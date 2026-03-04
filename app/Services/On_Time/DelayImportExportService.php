<?php

namespace App\Services\On_Time;

use App\Models\Delay;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;

class DelayImportExportService
{
    // ─────────────────────────────────────────────────────────────
    //  Shared CSV helpers
    // ─────────────────────────────────────────────────────────────

    private function detectDelimiter(string $filePath): string
    {
        $handle = fopen($filePath, 'r');
        if (!$handle) return "\t";
        $firstLine = fgets($handle);
        fclose($handle);
        if ($firstLine === false) return "\t";

        $delimiters = [
            ','  => substr_count($firstLine, ','),
            "\t" => substr_count($firstLine, "\t"),
            ';'  => substr_count($firstLine, ';'),
            '|'  => substr_count($firstLine, '|'),
        ];
        arsort($delimiters);
        return array_key_first($delimiters);
    }

    /**
     * Strip BOM and trim all header elements.
     * Trailing empty columns are intentionally kept — dropped
     * only after via array_filter when comparing against expected.
     */
    private function sanitizeHeaders(array $headers): array
    {
        if (!empty($headers[0])) {
            $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headers[0]);
        }
        return array_map(fn($h) => trim((string) $h), $headers);
    }

    /**
     * Trim all values and drop trailing empty cells beyond expected count.
     */
    private function sanitizeRow(array $row, int $expectedCount): array
    {
        $row = array_map(fn($v) => is_string($v) ? trim($v) : $v, $row);
        while (count($row) > $expectedCount && end($row) === '') {
            array_pop($row);
        }
        return $row;
    }

    /**
     * Returns true if every cell in the row is empty (blank line guard).
     */
    private function isBlankRow(array $row): bool
    {
        return empty(array_filter($row, fn($v) => trim((string) $v) !== ''));
    }

    // ─────────────────────────────────────────────────────────────
    //  Expected headers — both origin and destination are identical
    // ─────────────────────────────────────────────────────────────

    private function getExpectedHeaders(): array
    {
        return [
            'Trip ID',
            'Load ID',
            'Leg #',
            'Total Legs',
            'Drivers',
            'Driver Type',
            'Segment Status',
            'Run',
            'Distance',
            'Origin',
            'Destination',
            'Origin Yard Arrival Time',
            'Origin Arrival Late Reason',
            'Origin arrival previous late reason',
            'Origin Delay Duration',
            'Origin Delay Duration Bucket',
            'Origin Arrival Penalty',
            'Origin Yard Departure Time',
            'Origin depart Late Reason',
            'Origin depart previous late reason',
            'Destination Yard Arrival Time',
            'Destination Arrival Late Reason',
            'Destination arrival previous late reason',
            'Destination Delay Duration',
            'Destination Delay Duration Bucket',
            'Destination Arrival Penalty',
            'App Usage: Check-in at origin',
            'App Usage: Trailer Attach at Origin',
            'App Usage: Check-out at origin',
            'App Usage: Check-in at destination',
            'App Usage compliance',
            'Location Available',
            'Location Unavailability Reason',
        ];
    }

    // ─────────────────────────────────────────────────────────────
    //  Duration parser
    // ─────────────────────────────────────────────────────────────

    private function parseDurationToMinutes(string $raw): ?int
    {
        $raw = trim($raw);
        if ($raw === '') return null;

        $totalMinutes = 0;
        $matched      = 0;
        $remaining    = $raw;

        // Match tokens like "3D", "4h", "59m" — greedy left-to-right
        while (preg_match('/^(\d+)\s*([DdHhMm])(.*)$/s', $remaining, $m)) {
            $value     = (int) $m[1];
            $unit      = strtolower($m[2]);
            $remaining = $m[3];
            $matched++;

            $totalMinutes += match ($unit) {
                'd' => $value * 1440,
                'h' => $value * 60,
                'm' => $value,
            };
        }

        // If there's leftover non-whitespace content we couldn't parse → invalid
        if (trim($remaining) !== '') return null;

        // Must have matched at least one token and produced > 0 minutes
        return ($matched > 0 && $totalMinutes > 0) ? $totalMinutes : null;
    }

    // ─────────────────────────────────────────────────────────────
    //  Category + penalty from total minutes
    // ─────────────────────────────────────────────────────────────

    private function categoryFromMinutes(int $minutes): string
    {
        return match (true) {
            $minutes <= 60  => '1_60',
            $minutes <= 240 => '61_240',
            $minutes <= 600 => '241_600',
            default         => '601_plus',
        };
    }

    private function penaltyFromCategory(string $category): int
    {
        return match ($category) {
            '1_60'     => 1,
            '61_240'   => 2,
            '241_600'  => 4,
            '601_plus' => 8,
            default    => 0,
        };
    }

    // ─────────────────────────────────────────────────────────────
    //  Date parser — converts CST → EST (+1 hour) when needed
    // ─────────────────────────────────────────────────────────────

    private function parseDatetime(string $raw): ?Carbon
    {
        $raw = trim($raw);
        if ($raw === '') return null;

        if (preg_match('/^(\d{1,2}\/\d{1,2}\/\d{4}\s+\d{1,2}:\d{2})\s*(CST|EST)?$/i', $raw, $m)) {
            $dt       = Carbon::createFromFormat('m/d/Y H:i', trim($m[1]));
            $timezone = strtoupper($m[2] ?? 'EST');

            if ($timezone === 'CST') {
                $dt->addHour();
            }

            return $dt;
        }

        return null;
    }

    // ─────────────────────────────────────────────────────────────
    //  Controllable flags from penalty value
    // ─────────────────────────────────────────────────────────────

    private function resolveControllable(?string $penaltyRaw): bool
    {
        $val = trim((string) $penaltyRaw);
        return $val !== '' && $val !== '0';
    }

    // ─────────────────────────────────────────────────────────────
    //  Import
    // ─────────────────────────────────────────────────────────────

    public function importDelays($request): void
    {
        $request->validate([
            'csv_file'    => 'required|file|mimes:csv,txt',
            'import_type' => 'required|in:origin,destination',
        ]);

        $importType   = $request->input('import_type');
        $corrected    = $request->input('corrected_rows', []); // ✅ NEW
        $isSuperAdmin = Auth::user()->tenant_id === null;

        $tenantId = $isSuperAdmin
            ? $request->input('tenant_id')
            : Auth::user()->tenant_id;

        $file            = $request->file('csv_file');
        $filePath        = $file->getRealPath();
        $delimiter       = $this->detectDelimiter($filePath);
        $expectedHeaders = $this->getExpectedHeaders();
        $expectedCount   = count($expectedHeaders);

        // ✅ Build corrected row map
        $correctedMap = [];
        foreach ($corrected as $row) {
            if (isset($row['rowNumber'], $row['manual_datetime'])) {
                $correctedMap[$row['rowNumber']] = $row['manual_datetime'];
            }
        }

        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new \Exception('Could not open the CSV file.');
        }

        fgetcsv($handle, 0, $delimiter); // skip header

        $rowNumber = 1;

        while (($rawRow = fgetcsv($handle, 0, $delimiter)) !== false) {
            $rowNumber++;

            if ($this->isBlankRow($rawRow)) continue;

            $row = $this->sanitizeRow($rawRow, $expectedCount);

            try {
                $manualDate = $correctedMap[$rowNumber] ?? null;

                if ($importType === 'origin') {
                    $this->processOriginRow($row, $tenantId, $manualDate);
                } else {
                    $this->processDestinationRow($row, $tenantId, $manualDate);
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        fclose($handle);
    }

    // ─────────────────────────────────────────────────────────────
    //  Origin row processor
    //
    //  Both CSVs share the same 33-column layout.
    //  For origin imports we read from the origin columns:
    //
    //  11 Origin Yard Arrival Time         → date
    //  12 Origin Arrival Late Reason       → delay_reason
    //  14 Origin Delay Duration            → delay_duration
    //  16 Origin Arrival Penalty           → controllable flag
    //   1 Load ID                          → load_id
    //   4 Drivers                          → driver_name
    // ─────────────────────────────────────────────────────────────

    private function processOriginRow(array $row, ?int $tenantId, ?string $manualDate = null): void
    {
        if ($tenantId === null) return;

        $loadId      = trim($row[1]  ?? '');
        $driverName  = trim($row[4]  ?? '');
        $rawDatetime = $row[11] ?? '';
        $rawReason   = trim($row[12] ?? '');
        $rawDuration = trim($row[14] ?? '');
        $rawPenalty  = trim($row[16] ?? '');

        $date = $this->parseDatetime($rawDatetime);

        // ✅ If missing date and manual provided
        if (!$date && $manualDate) {
            $date = Carbon::parse($manualDate);

            $this->upsertDelay([
                'tenant_id'            => $tenantId,
                'delay_type'           => 'origin',
                'date'                 => $date,
                'load_id'              => $loadId ?: null,
                'driver_name'          => $driverName ?: null,
                'delay_reason'         => null,
                'delay_duration'       => null,
                'delay_category'       => null,
                'penalty'              => 0,
                'disputed'             => 'none',
                'driver_controllable'  => false,
                'carrier_controllable' => false,
            ]);

            return;
        }

        if (!$date) return;

        $totalMinutes   = $this->parseDurationToMinutes($rawDuration);
        $category       = $totalMinutes ? $this->categoryFromMinutes($totalMinutes) : null;
        $penalty        = $category ? $this->penaltyFromCategory($category) : 0;
        $isControllable = $this->resolveControllable($rawPenalty);
        $delayReason    = $rawReason !== '' ? $rawReason : null;

        $this->upsertDelay([
            'tenant_id'            => $tenantId,
            'delay_type'           => 'origin',
            'date'                 => $date,
            'load_id'              => $loadId ?: null,
            'driver_name'          => $driverName ?: null,
            'delay_reason'         => $delayReason,
            'delay_duration'       => $totalMinutes,
            'delay_category'       => $category,
            'penalty'              => $penalty,
            'disputed'             => 'none',
            'driver_controllable'  => $isControllable,
            'carrier_controllable' => $isControllable,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    //  Destination row processor
    //
    //  For destination imports we read from the destination columns:
    //
    //   1 Load ID                           → load_id
    //   4 Drivers                           → driver_name
    //  20 Destination Yard Arrival Time     → date
    //  21 Destination Arrival Late Reason   → delay_reason
    //  23 Destination Delay Duration        → delay_duration
    //  25 Destination Arrival Penalty       → controllable flag
    // ─────────────────────────────────────────────────────────────

    private function processDestinationRow(array $row, ?int $tenantId, ?string $manualDate = null): void
    {
        if ($tenantId === null) return;

        $loadId      = trim($row[1]  ?? '');
        $driverName  = trim($row[4]  ?? '');
        $rawDatetime = $row[20] ?? '';
        $rawReason   = trim($row[21] ?? '');
        $rawDuration = trim($row[23] ?? '');
        $rawPenalty  = trim($row[25] ?? '');

        $date = $this->parseDatetime($rawDatetime);

        // ✅ Manual override
        if (!$date && $manualDate) {
            $date = Carbon::parse($manualDate);

            $this->upsertDelay([
                'tenant_id'            => $tenantId,
                'delay_type'           => 'destination',
                'date'                 => $date,
                'load_id'              => $loadId ?: null,
                'driver_name'          => $driverName ?: null,
                'delay_reason'         => null,
                'delay_duration'       => null,
                'delay_category'       => null,
                'penalty'              => 0,
                'disputed'             => 'none',
                'driver_controllable'  => false,
                'carrier_controllable' => false,
            ]);

            return;
        }

        if (!$date) return;

        $totalMinutes   = $this->parseDurationToMinutes($rawDuration);
        $category       = $totalMinutes ? $this->categoryFromMinutes($totalMinutes) : null;
        $penalty        = $category ? $this->penaltyFromCategory($category) : 0;
        $isControllable = $this->resolveControllable($rawPenalty);
        $delayReason    = $rawReason !== '' ? $rawReason : null;

        $this->upsertDelay([
            'tenant_id'            => $tenantId,
            'delay_type'           => 'destination',
            'date'                 => $date,
            'load_id'              => $loadId ?: null,
            'driver_name'          => $driverName ?: null,
            'delay_reason'         => $delayReason,
            'delay_duration'       => $totalMinutes,
            'delay_category'       => $category,
            'penalty'              => $penalty,
            'disputed'             => 'none',
            'driver_controllable'  => $isControllable,
            'carrier_controllable' => $isControllable,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    //  Upsert logic
    // ─────────────────────────────────────────────────────────────

    private function upsertDelay(array $data): void
    {
        $loadId   = $data['load_id']   ?? null;
        $tenantId = $data['tenant_id'] ?? null;
        $type     = $data['delay_type'];

        $existing = null;

        if ($loadId && $tenantId) {
            $existing = Delay::where('tenant_id', $tenantId)
                ->where('load_id', $loadId)
                ->where('delay_type', $type)
                ->first();
        }

        if ($existing) {
            $hadReason    = !empty($existing->delay_reason);
            $hasNewReason = !empty($data['delay_reason']);

            if ($hadReason && !$hasNewReason) {
                // Had a reason before, now cleared → auto-win
                $existing->update([
                    'carrier_controllable' => false,
                    'disputed'             => 'won',
                ]);
            } else {
                $existing->update([
                    'date'                 => $data['date'],
                    'driver_name'          => $data['driver_name'],
                    'delay_reason'         => $data['delay_reason'],
                    'delay_duration'       => $data['delay_duration'],
                    'delay_category'       => $data['delay_category'],
                    'penalty'              => $data['penalty'],
                    'driver_controllable'  => $data['driver_controllable'],
                    'carrier_controllable' => $data['carrier_controllable'],
                    // disputed stays as-is on normal updates
                ]);
            }
        } else {
            Delay::create($data);
        }
    }

    // ─────────────────────────────────────────────────────────────
    //  Export
    // ─────────────────────────────────────────────────────────────

    public function exportDelays()
    {
        $isSuperAdmin = Auth::user()->tenant_id === null;

        $query = Delay::with('tenant');

        if (!$isSuperAdmin) {
            $query->where('tenant_id', Auth::user()->tenant_id);
        }

        $delays = $query->get();

        if ($delays->isEmpty()) {
            return redirect()->back()->with('error', 'No delay data found to export.');
        }

        $fileName = 'delays_' . Str::random(8) . '.csv';
        $filePath = public_path($fileName);
        $file     = fopen($filePath, 'w');

        // UTF-8 BOM for Excel
        fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

        $headers = [];
        if ($isSuperAdmin) $headers[] = 'Company Name';

        $headers = array_merge($headers, [
            'Date',
            'Type',
            'Load ID',
            'Driver Name',
            'Delay Duration (minutes)',
            'Delay Category',
            'Penalty',
            'Delay Reason',
            'Disputed',
            'Driver Controllable',
            'Carrier Controllable',
        ]);

        fputcsv($file, $headers);

        foreach ($delays as $delay) {
            $row = [];
            if ($isSuperAdmin) $row[] = $delay->tenant->name ?? '';

            $row = array_merge($row, [
                $delay->date ? Carbon::parse($delay->date)->format('m/d/Y H:i') : '',
                $this->formatDelayType($delay),
                $delay->load_id      ?? '',
                $delay->driver_name  ?? '',
                $delay->delay_duration ?? '',
                $this->formatDelayCategory($delay->delay_category),
                $delay->penalty ?? '',
                $delay->delay_reason ?? '',
                $this->formatDisputeStatus($delay->disputed),
                $delay->driver_controllable  === null ? 'N/A' : ($delay->driver_controllable  ? 'Yes' : 'No'),
                $delay->carrier_controllable === null ? 'N/A' : ($delay->carrier_controllable ? 'Yes' : 'No'),
            ]);

            fputcsv($file, $row);
        }

        fclose($file);

        return Response::download($filePath)->deleteFileAfterSend(true);
    }

    // ─────────────────────────────────────────────────────────────
    //  Export formatters
    // ─────────────────────────────────────────────────────────────

    private function formatDelayType(Delay $delay): string
    {
        $hasReason = !empty($delay->delay_reason);

        return match ($delay->delay_type) {
            'origin'      => $hasReason ? 'Origin'      : 'Origin',
            'destination' => $hasReason ? 'Destination' : 'Destination',
            default       => ucfirst($delay->delay_type),
        };
    }

    private function formatDelayCategory(?string $category): string
    {
        return match ($category) {
            '1_60'     => '1–60 minutes late',
            '61_240'   => '61–240 minutes late',
            '241_600'  => '241–600 minutes late',
            '601_plus' => '601+ minutes late',
            default    => '',
        };
    }

    private function formatDisputeStatus(?string $disputed): string
    {
        return match ($disputed) {
            'none'    => 'None',
            'pending' => 'Pending',
            'won'     => 'Won',
            'lost'    => 'Lost',
            default   => 'None',
        };
    }
}
