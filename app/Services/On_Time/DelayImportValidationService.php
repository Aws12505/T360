<?php

namespace App\Services\On_Time;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DelayImportValidationService
{
    protected array $results = [
        'valid'            => [],
        'invalid'          => [],
        'summary'          => ['total' => 0, 'valid' => 0, 'invalid' => 0],
        'headers'          => [],
        'expected_headers' => [],
    ];

    // ─────────────────────────────────────────────────────────────
    //  Expected headers per import type
    // ─────────────────────────────────────────────────────────────

    private function getExpectedHeaders(string $importType): array
    {
        if ($importType === 'origin') {
            return [
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

        // destination
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
    //  Main entry
    // ─────────────────────────────────────────────────────────────

    public function validateDelaysCsv($file, string $importType): array
    {
        $handle = fopen($file->getRealPath(), 'r');
        if (!$handle) {
            throw new \Exception('Unable to open CSV file.');
        }

        // Strip BOM
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $isSuperAdmin    = Auth::user() && Auth::user()->tenant_id === null;
        $expectedHeaders = $this->getExpectedHeaders($importType);

        $this->results['expected_headers'] = $expectedHeaders;

        // Read header row (tab-separated)
        $headerRow = fgetcsv($handle, 0, "\t");
        if ($headerRow === false) {
            fclose($handle);
            return [
                ...$this->results,
                'header_error' => 'CSV appears to be empty or unreadable.',
            ];
        }

        $headerRow = array_map(fn($h) => trim((string) $h), $headerRow);
        $this->results['headers'] = $headerRow;

        // Normalize and compare headers
        $normalizedIncoming = array_map(fn($h) => strtolower(trim($h)), $headerRow);
        $normalizedExpected = array_map(fn($h) => strtolower(trim($h)), $expectedHeaders);

        if ($normalizedIncoming !== $normalizedExpected) {
            fclose($handle);
            return [
                ...$this->results,
                'header_error' => 'CSV headers do not match the expected ' . $importType . ' template. ' .
                    'Expected ' . count($expectedHeaders) . ' columns, got ' . count($headerRow) . '.',
            ];
        }

        $rowNumber = 1;
        while (($row = fgetcsv($handle, 0, "\t")) !== false) {
            $rowNumber++;
            $this->results['summary']['total']++;

            $validation = $importType === 'origin'
                ? $this->validateOriginRow($row, $expectedHeaders, $rowNumber, $isSuperAdmin)
                : $this->validateDestinationRow($row, $expectedHeaders, $rowNumber, $isSuperAdmin);

            if ($validation['isValid']) {
                $this->results['valid'][]  = $validation;
                $this->results['summary']['valid']++;
            } else {
                $this->results['invalid'][] = $validation;
                $this->results['summary']['invalid']++;
            }
        }

        fclose($handle);
        return $this->results;
    }

    // ─────────────────────────────────────────────────────────────
    //  Origin row validator
    // ─────────────────────────────────────────────────────────────

    private function validateOriginRow(array $row, array $headers, int $rowNumber, bool $isSuperAdmin): array
    {
        $errors   = [];
        $warnings = [];

        if (count($row) !== count($headers)) {
            return $this->columnMismatchResult($rowNumber, $row, $headers);
        }

        $data = array_combine($headers, $row);
        $data = array_map(fn($v) => trim((string) $v), $data);

        // Date
        $rawDate = $data['Origin Yard Arrival Time'] ?? '';
        if ($rawDate === '') {
            $errors[] = 'Origin Yard Arrival Time is required';
        } else {
            if (!$this->parseDatetime($rawDate)) {
                $errors[] = "Origin Yard Arrival Time format invalid: {$rawDate}";
            }
        }

        // Duration (nullable — if empty, no delay, that's fine)
        $rawDuration = $data['Origin Delay Duration'] ?? '';
        if ($rawDuration !== '' && $this->parseDurationToMinutes($rawDuration) === null) {
            $errors[] = "Origin Delay Duration format invalid: {$rawDuration} (expected e.g. '24m', '4h38m', '3650D')";
        }

        // Penalty column format — just a number or empty
        $rawPenalty = $data['Origin Arrival Penalty'] ?? '';
        if ($rawPenalty !== '' && !is_numeric($rawPenalty)) {
            $warnings[] = "Origin Arrival Penalty is not numeric: {$rawPenalty}";
        }

        return [
            'rowNumber' => $rowNumber,
            'isValid'   => empty($errors),
            'errors'    => $errors,
            'warnings'  => $warnings,
            'data'      => $data,
            'preview'   => $this->buildPreview($data, [
                'Origin Yard Arrival Time',
                'Origin Arrival Late Reason',
                'Origin Delay Duration',
            ]),
        ];
    }

    // ─────────────────────────────────────────────────────────────
    //  Destination row validator
    // ─────────────────────────────────────────────────────────────

    private function validateDestinationRow(array $row, array $headers, int $rowNumber, bool $isSuperAdmin): array
    {
        $errors   = [];
        $warnings = [];

        if (count($row) !== count($headers)) {
            return $this->columnMismatchResult($rowNumber, $row, $headers);
        }

        $data = array_combine($headers, $row);
        $data = array_map(fn($v) => trim((string) $v), $data);

        // Load ID — required for destination (used for upsert)
        if (empty($data['Load ID'])) {
            $errors[] = 'Load ID is required';
        }

        // Drivers
        if (empty($data['Drivers'])) {
            $warnings[] = 'Drivers column is empty';
        }

        // Date
        $rawDate = $data['Destination Yard Arrival Time'] ?? '';
        if ($rawDate === '') {
            $errors[] = 'Destination Yard Arrival Time is required';
        } else {
            if (!$this->parseDatetime($rawDate)) {
                $errors[] = "Destination Yard Arrival Time format invalid: {$rawDate}";
            }
        }

        // Duration
        $rawDuration = $data['Destination Delay Duration'] ?? '';
        if ($rawDuration !== '' && $this->parseDurationToMinutes($rawDuration) === null) {
            $errors[] = "Destination Delay Duration format invalid: {$rawDuration} (expected e.g. '24m', '4h38m', '3650D')";
        }

        // Penalty
        $rawPenalty = $data['Destination Arrival Penalty'] ?? '';
        if ($rawPenalty !== '' && !is_numeric($rawPenalty)) {
            $warnings[] = "Destination Arrival Penalty is not numeric: {$rawPenalty}";
        }

        return [
            'rowNumber' => $rowNumber,
            'isValid'   => empty($errors),
            'errors'    => $errors,
            'warnings'  => $warnings,
            'data'      => $data,
            'preview'   => $this->buildPreview($data, [
                'Load ID',
                'Drivers',
                'Destination Yard Arrival Time',
                'Destination Arrival Late Reason',
            ]),
        ];
    }

    // ─────────────────────────────────────────────────────────────
    //  Error report CSV
    // ─────────────────────────────────────────────────────────────

    public function generateErrorReport(array $invalidRows): string
    {
        $fileName = 'delays_import_errors_' . date('Y-m-d_His') . '.csv';
        $dir      = 'temp-imports';
        Storage::makeDirectory($dir);

        $path = $dir . '/' . $fileName;
        $full = Storage::path($path);
        $file = fopen($full, 'w');

        fputcsv($file, ['Row Number', 'Preview', 'Errors', 'Warnings']);

        foreach ($invalidRows as $row) {
            $previewString = '—';

            if (is_array($row['preview'] ?? null)) {
                $parts = [];
                foreach ($row['preview'] as $p) {
                    if (($p['label'] ?? '') !== '' && ($p['value'] ?? '') !== '') {
                        $parts[] = "{$p['label']}: {$p['value']}";
                    }
                }
                $previewString = !empty($parts) ? implode(' | ', $parts) : '—';
            }

            fputcsv($file, [
                $row['rowNumber'] ?? '—',
                $previewString,
                !empty($row['errors'])   ? implode('; ', $row['errors'])   : '—',
                !empty($row['warnings']) ? implode('; ', $row['warnings']) : '—',
            ]);
        }

        fclose($file);
        return $full;
    }

    // ─────────────────────────────────────────────────────────────
    //  Shared helpers
    // ─────────────────────────────────────────────────────────────

    private function parseDatetime(string $raw): ?Carbon
    {
        $raw = trim($raw);
        if ($raw === '') return null;

        if (preg_match('/^(\d{1,2}\/\d{1,2}\/\d{4}\s+\d{1,2}:\d{2})\s*(CST|EST)?$/i', $raw, $m)) {
            try {
                return Carbon::createFromFormat('m/d/Y H:i', trim($m[1]));
            } catch (\Exception $e) {
                return null;
            }
        }

        return null;
    }

    private function parseDurationToMinutes(string $raw): ?int
    {
        $raw = trim($raw);
        if ($raw === '') return null;

        if (preg_match('/^(\d+)\s*[Dd]$/i', $raw, $m)) {
            return (int) $m[1] * 1440;
        }

        if (preg_match('/^(?:(\d+)\s*h)?\s*(?:(\d+)\s*m)?$/i', $raw, $m)) {
            $hours   = isset($m[1]) && $m[1] !== '' ? (int) $m[1] : 0;
            $minutes = isset($m[2]) && $m[2] !== '' ? (int) $m[2] : 0;
            $total   = ($hours * 60) + $minutes;
            return $total > 0 ? $total : null;
        }

        return null;
    }

    private function buildPreview(array $data, array $fields): array
    {
        $preview = [];
        foreach ($fields as $field) {
            $val = $data[$field] ?? '';
            if ($val === '') continue;
            if (strlen($val) > 30) $val = substr($val, 0, 30) . '...';
            $preview[] = [
                'key'   => $field,
                'label' => $field,
                'value' => $val,
            ];
        }

        return !empty($preview) ? $preview : [['key' => 'row', 'label' => 'Row', 'value' => '(empty)']];
    }

    private function columnMismatchResult(int $rowNumber, array $row, array $headers): array
    {
        return [
            'rowNumber' => $rowNumber,
            'isValid'   => false,
            'errors'    => ['Column count mismatch. Expected ' . count($headers) . ', got ' . count($row)],
            'warnings'  => [],
            'data'      => $row,
            'preview'   => $this->buildRawPreview($row, $headers),
        ];
    }

    private function buildRawPreview(array $row, array $headers): array
    {
        $out = [];
        foreach (array_slice($headers, 0, 3) as $i => $h) {
            $val = isset($row[$i]) ? (string) $row[$i] : '';
            if (strlen($val) > 30) $val = substr($val, 0, 30) . '...';
            $out[] = ['key' => $h, 'label' => $h, 'value' => $val];
        }
        return !empty($out) ? $out : [['key' => 'row', 'label' => 'Row', 'value' => '(empty)']];
    }
}
