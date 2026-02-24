<?php

namespace App\Services\On_Time;

use App\Models\Delay;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;

class DelayImportExportService
{
    // ─────────────────────────────────────────────────────────────
    //  Duration parser
    //  Handles: "24m", "4h38m", "1h29m", "3650D" (days), "4m", ""
    // ─────────────────────────────────────────────────────────────

    private function parseDurationToMinutes(string $raw): ?int
    {
        $raw = trim($raw);
        if ($raw === '' || $raw === null) return null;

        // Days format e.g. "3650D"
        if (preg_match('/^(\d+)\s*[Dd]$/i', $raw, $m)) {
            return (int) $m[1] * 1440;
        }

        // Hours + minutes e.g. "4h38m", "1h29m"
        if (preg_match('/^(?:(\d+)\s*h)?\s*(?:(\d+)\s*m)?$/i', $raw, $m)) {
            $hours   = isset($m[1]) && $m[1] !== '' ? (int) $m[1] : 0;
            $minutes = isset($m[2]) && $m[2] !== '' ? (int) $m[2] : 0;
            $total   = ($hours * 60) + $minutes;
            return $total > 0 ? $total : null;
        }

        return null;
    }

    // ─────────────────────────────────────────────────────────────
    //  Category + penalty from total minutes  (same logic as service)
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

        // e.g. "1/19/2026 2:14 CST" or "1/4/2026 20:43 EST"
        if (preg_match('/^(\d{1,2}\/\d{1,2}\/\d{4}\s+\d{1,2}:\d{2})\s*(CST|EST)?$/i', $raw, $m)) {
            $dt       = Carbon::createFromFormat('m/d/Y H:i', trim($m[1]));
            $timezone = strtoupper($m[2] ?? 'EST');

            if ($timezone === 'CST') {
                $dt->addHour(); // CST is UTC-6, EST is UTC-5 → +1h
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
        // Empty or "0" → not controllable
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
        $isSuperAdmin = Auth::user()->tenant_id === null;
        $tenantId     = $isSuperAdmin ? null : Auth::user()->tenant_id;

        $file   = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');

        if (!$handle) {
            throw new \Exception('Could not open the CSV file.');
        }

        // Strip BOM if present
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        // Read and discard header row
        fgetcsv($handle, 0, "\t"); // TSV (tab-separated based on sample data)

        while (($row = fgetcsv($handle, 0, "\t")) !== false) {
            try {
                if ($importType === 'origin') {
                    $this->processOriginRow($row, $isSuperAdmin, $tenantId);
                } else {
                    $this->processDestinationRow($row, $isSuperAdmin, $tenantId);
                }
            } catch (\Exception $e) {
                // Skip malformed rows silently — validation service caught these already
                continue;
            }
        }

        fclose($handle);
    }

    // ─────────────────────────────────────────────────────────────
    //  Origin row processor
    //
    //  Columns (0-indexed, tab-separated):
    //  0  Segment Status
    //  1  Run
    //  2  Distance
    //  3  Origin
    //  4  Destination
    //  5  Origin Yard Arrival Time        → date
    //  6  Origin Arrival Late Reason      → delay_reason
    //  7  Origin arrival previous late reason
    //  8  Origin Delay Duration           → delay_duration
    //  9  Origin Delay Duration Bucket
    //  10 Origin Arrival Penalty          → controllable flag
    //  11 Origin Yard Departure Time
    //  12 Origin depart Late Reason
    //  ... (rest ignored for origin import)
    // ─────────────────────────────────────────────────────────────

    private function processOriginRow(array $row, bool $isSuperAdmin, ?int $tenantId): void
    {
        // Resolve tenant for super admin (no tenant_name column in origin CSV,
        // so super admin must pass tenantId another way — skip if null)
        if ($isSuperAdmin && $tenantId === null) return;

        $rawDatetime    = $row[5]  ?? '';
        $rawReason      = trim($row[6]  ?? '');
        $rawDuration    = trim($row[8]  ?? '');
        $rawPenalty     = trim($row[10] ?? '');

        // Date is required
        $date = $this->parseDatetime($rawDatetime);
        if (!$date) return;

        // Duration → total minutes → category → penalty
        $totalMinutes = $this->parseDurationToMinutes($rawDuration);
        $category     = $totalMinutes ? $this->categoryFromMinutes($totalMinutes) : null;
        $penalty      = $category    ? $this->penaltyFromCategory($category)      : 0;

        // Controllable flags
        $isControllable      = $this->resolveControllable($rawPenalty);
        $driverControllable  = $isControllable;
        $carrierControllable = $isControllable;

        // Delay reason
        $delayReason = $rawReason !== '' ? $rawReason : null;

        // Load ID — origin CSV has no load_id column, so null
        $loadId = null;

        $this->upsertDelay([
            'tenant_id'           => $tenantId,
            'delay_type'          => 'origin',
            'date'                => $date,
            'driver_name'         => null, // no Drivers column in origin CSV
            'delay_reason'        => $delayReason,
            'delay_duration'      => $totalMinutes,
            'delay_category'      => $category,
            'penalty'             => $penalty,
            'disputed'            => 'none',
            'driver_controllable' => $driverControllable,
            'carrier_controllable' => $carrierControllable,
            'load_id'             => $loadId,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    //  Destination row processor
    //
    //  Columns (0-indexed, tab-separated):
    //  0  Trip ID
    //  1  Load ID                          → load_id
    //  2  Leg #
    //  3  Total Legs
    //  4  Drivers                          → driver_name
    //  5  Driver Type
    //  6  Segment Status
    //  7  Run
    //  8  Distance
    //  9  Origin
    //  10 Destination
    //  11 Origin Yard Arrival Time
    //  12 Origin Arrival Late Reason
    //  13 Origin arrival previous late reason
    //  14 Origin Delay Duration
    //  15 Origin Delay Duration Bucket
    //  16 Origin Arrival Penalty
    //  17 Origin Yard Departure Time
    //  18 Origin depart Late Reason
    //  19 Origin depart previous late reason
    //  20 Destination Yard Arrival Time    → date
    //  21 Destination Arrival Late Reason  → delay_reason
    //  22 Destination arrival previous late reason
    //  23 Destination Delay Duration       → delay_duration
    //  24 Destination Delay Duration Bucket
    //  25 Destination Arrival Penalty      → controllable flag
    // ─────────────────────────────────────────────────────────────

    private function processDestinationRow(array $row, bool $isSuperAdmin, ?int $tenantId): void
    {
        if ($isSuperAdmin && $tenantId === null) return;

        $loadId      = trim($row[1]  ?? '');
        $driverName  = trim($row[4]  ?? '');
        $rawDatetime = $row[20] ?? '';
        $rawReason   = trim($row[21] ?? '');
        $rawDuration = trim($row[23] ?? '');
        $rawPenalty  = trim($row[25] ?? '');

        $date = $this->parseDatetime($rawDatetime);
        if (!$date) return;

        $totalMinutes        = $this->parseDurationToMinutes($rawDuration);
        $category            = $totalMinutes ? $this->categoryFromMinutes($totalMinutes) : null;
        $penalty             = $category    ? $this->penaltyFromCategory($category)      : 0;
        $isControllable      = $this->resolveControllable($rawPenalty);
        $driverControllable  = $isControllable;
        $carrierControllable = $isControllable;
        $delayReason         = $rawReason !== '' ? $rawReason : null;

        $this->upsertDelay([
            'tenant_id'            => $tenantId,
            'delay_type'           => 'destination',
            'date'                 => $date,
            'driver_name'          => $driverName !== '' ? $driverName : null,
            'delay_reason'         => $delayReason,
            'delay_duration'       => $totalMinutes,
            'delay_category'       => $category,
            'penalty'              => $penalty,
            'disputed'             => 'none',
            'driver_controllable'  => $driverControllable,
            'carrier_controllable' => $carrierControllable,
            'load_id'              => $loadId !== '' ? $loadId : null,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    //  Upsert logic
    //
    //  If load_id exists for this tenant → check update scenario
    //  If no load_id OR no match → create new record
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
                // Special case: had reason before, now no reason →
                // only update carrier_controllable → false and disputed → 'won'
                $existing->update([
                    'carrier_controllable' => false,
                    'disputed'             => 'won',
                ]);
            } else {
                // Normal update — refresh all fields
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
            // New record
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
        fwrite($file, "\xEF\xBB\xBF");

        $headers = [
            ...($isSuperAdmin ? ['tenant_name'] : []),
            'date',
            'type',
            'load_id',
            'driver_name',
            'delay_duration_minutes',
            'delay_category',
            'penalty',
            'delay_reason',
            'disputed',
            'driver_controllable',
            'carrier_controllable',
        ];

        fputcsv($file, $headers);

        foreach ($delays as $delay) {
            $row = [
                ...($isSuperAdmin ? [$delay->tenant->name ?? ''] : []),
                $delay->date ? Carbon::parse($delay->date)->format('m/d/Y H:i') : '',
                $this->formatDelayType($delay),
                $delay->load_id ?? '',
                $delay->driver_name ?? '',
                $delay->delay_duration ?? '',
                $this->formatDelayCategory($delay->delay_category),
                $delay->penalty ?? '',
                $delay->delay_reason ?? '',
                $this->formatDisputeStatus($delay->disputed),
                $delay->driver_controllable === null ? 'N/A' : ($delay->driver_controllable ? 'Yes' : 'No'),
                $delay->carrier_controllable === null ? 'N/A' : ($delay->carrier_controllable ? 'Yes' : 'No'),
            ];

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
            'origin'      => $hasReason ? 'Delay to Origin'      : 'Origin Leg',
            'destination' => $hasReason ? 'Delay to Destination' : 'Destination Leg',
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
