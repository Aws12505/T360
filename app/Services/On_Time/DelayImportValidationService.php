<?php

namespace App\Services\On_Time;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DelayImportValidationService
{
    protected array $results = [
        'valid' => [],
        'invalid' => [],
        'summary' => ['total' => 0, 'valid' => 0, 'invalid' => 0],
        'headers' => [],
        'expected_headers' => [],
    ];

    // ─────────────────────────────────────────────────────────────
    //  Shared CSV helpers
    // ─────────────────────────────────────────────────────────────

    private function detectDelimiter(string $filePath): string
    {
        $handle = fopen($filePath, 'r');
        if (!$handle)
            return "\t";
        $firstLine = fgets($handle);
        fclose($handle);
        if ($firstLine === false)
            return "\t";

        $delimiters = [
            ',' => substr_count($firstLine, ','),
            "\t" => substr_count($firstLine, "\t"),
            ';' => substr_count($firstLine, ';'),
            '|' => substr_count($firstLine, '|'),
        ];
        arsort($delimiters);
        return array_key_first($delimiters);
    }

    /**
     * Strip BOM and trim all header elements.
     * NOTE: trailing empty columns are intentionally kept —
     * the source CSV has a trailing tab after the last column.
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
    //  Expected headers per import type
    //  Both origin and destination share the same 33-column format.
    // ─────────────────────────────────────────────────────────────

    private function getExpectedHeaders(string $importType): array
    {
        // Both origin and destination CSVs have identical headers.
        // The distinction is which columns are populated per row.
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
        $filePath = $file->getRealPath();
        $delimiter = $this->detectDelimiter($filePath);

        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new \Exception('Unable to open CSV file.');
        }

        $isSuperAdmin = Auth::user() && Auth::user()->tenant_id === null;
        $expectedHeaders = $this->getExpectedHeaders($importType);

        $this->results['expected_headers'] = $expectedHeaders;
        $this->results['needs_input'] = []; // ✅ NEW

        // Read and sanitize header row
        $rawHeader = fgetcsv($handle, 0, $delimiter);
        if ($rawHeader === false) {
            fclose($handle);
            return [
                ...$this->results,
                'header_error' => 'CSV appears to be empty or unreadable.',
            ];
        }

        $headerRow = $this->sanitizeHeaders($rawHeader);
        $headerRow = array_values(array_filter($headerRow, fn($h) => $h !== ''));

        $this->results['headers'] = $headerRow;

        $normalizedIncoming = array_map(fn($h) => strtolower(trim($h)), $headerRow);
        $normalizedExpected = array_map(fn($h) => strtolower(trim($h)), $expectedHeaders);

        if ($normalizedIncoming !== $normalizedExpected) {
            fclose($handle);
            return [
                ...$this->results,
                'header_error' => 'CSV headers do not match the expected template. '
                    . 'Expected ' . count($expectedHeaders) . ' columns, got ' . count($headerRow) . '.',
            ];
        }

        $rowNumber = 1;

        while (($rawRow = fgetcsv($handle, 0, $delimiter)) !== false) {
            $rowNumber++;

            if ($this->isBlankRow($rawRow))
                continue;

            $this->results['summary']['total']++;

            $row = $this->sanitizeRow($rawRow, count($expectedHeaders));

            $validation = $importType === 'origin'
                ? $this->validateOriginRow($row, $expectedHeaders, $rowNumber, $isSuperAdmin)
                : $this->validateDestinationRow($row, $expectedHeaders, $rowNumber, $isSuperAdmin);

            // ✅ NEW LOGIC: separate "needs_input"
            if (!empty($validation['needsDateInput'])) {
                $this->results['needs_input'][] = $validation;
            } elseif ($validation['isValid']) {
                $this->results['valid'][] = $validation;
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
    //  Reads from the shared 33-column format but only validates
    //  origin-specific fields (cols 5, 6, 8, 10).
    // ─────────────────────────────────────────────────────────────

    private function validateOriginRow(array $row, array $headers, int $rowNumber, bool $isSuperAdmin): array
    {
        $errors = [];
        $warnings = [];

        $row = $this->sanitizeRow($row, count($headers));

        if (count($row) !== count($headers)) {
            return $this->columnMismatchResult($rowNumber, $row, $headers);
        }

        $data = array_combine($headers, $row);
        $data = array_map(fn($v) => trim((string) $v), $data);

        $rawDate = $data['Origin Yard Arrival Time'] ?? '';

        // ✅ NEW: Missing date handled as "needs input"
        if ($rawDate === '') {
            return [
                'rowNumber' => $rowNumber,
                'isValid' => false,
                'needsDateInput' => true,
                'missing_field' => 'Origin Yard Arrival Time',
                'errors' => [],
                'warnings' => [],
                'data' => $data,
                'preview' => $this->buildPreview($data, [
                    'Load ID',
                    'Drivers',
                ]),
            ];
        }

        if (!$this->parseDatetime($rawDate)) {
            $errors[] = "Origin Yard Arrival Time format invalid: {$rawDate}";
        }

        $rawDuration = $data['Origin Delay Duration'] ?? '';
        if ($rawDuration !== '' && $this->parseDurationToMinutes($rawDuration) === null) {
            $errors[] = "Origin Delay Duration format invalid: {$rawDuration} (expected e.g. '24m', '4h38m', '3650D')";
        }

        $rawPenalty = $data['Origin Arrival Penalty'] ?? '';
        if ($rawPenalty !== '' && !is_numeric($rawPenalty)) {
            $warnings[] = "Origin Arrival Penalty is not numeric: {$rawPenalty}";
        }

        return [
            'rowNumber' => $rowNumber,
            'isValid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
            'data' => $data,
            'preview' => $this->buildPreview($data, [
                'Load ID',
                'Drivers',
                'Origin Yard Arrival Time',
                'Origin Arrival Late Reason',
                'Origin Delay Duration',
            ]),
        ];
    }

    // ─────────────────────────────────────────────────────────────
    //  Destination row validator
    //  Reads from the same 33-column format but validates
    //  destination-specific fields (cols 20, 21, 23, 25).
    // ─────────────────────────────────────────────────────────────

    private function validateDestinationRow(array $row, array $headers, int $rowNumber, bool $isSuperAdmin): array
    {
        $errors = [];
        $warnings = [];

        $row = $this->sanitizeRow($row, count($headers));

        if (count($row) !== count($headers)) {
            return $this->columnMismatchResult($rowNumber, $row, $headers);
        }

        $data = array_combine($headers, $row);
        $data = array_map(fn($v) => trim((string) $v), $data);

        if (empty($data['Load ID'])) {
            $errors[] = 'Load ID is required';
        }

        if (empty($data['Drivers'])) {
            $warnings[] = 'Drivers column is empty';
        }

        $rawDate = $data['Destination Yard Arrival Time'] ?? '';

        // ✅ NEW: Missing date handled as "needs input"
        if ($rawDate === '') {
            return [
                'rowNumber' => $rowNumber,
                'isValid' => false,
                'needsDateInput' => true,
                'missing_field' => 'Destination Yard Arrival Time',
                'errors' => [],
                'warnings' => [],
                'data' => $data,
                'preview' => $this->buildPreview($data, [
                    'Load ID',
                    'Drivers',
                ]),
            ];
        }
        if (!$this->parseDatetime($rawDate)) {
            $errors[] = "Destination Yard Arrival Time format invalid: {$rawDate}";
        }

        $rawDuration = $data['Destination Delay Duration'] ?? '';
        if ($rawDuration !== '' && $this->parseDurationToMinutes($rawDuration) === null) {
            $errors[] = "Destination Delay Duration format invalid: {$rawDuration} (expected e.g. '24m', '4h38m', '3650D')";
        }

        $rawPenalty = $data['Destination Arrival Penalty'] ?? '';
        if ($rawPenalty !== '' && !is_numeric($rawPenalty)) {
            $warnings[] = "Destination Arrival Penalty is not numeric: {$rawPenalty}";
        }

        return [
            'rowNumber' => $rowNumber,
            'isValid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
            'data' => $data,
            'preview' => $this->buildPreview($data, [
                'Load ID',
                'Drivers',
                'Destination Yard Arrival Time',
                'Destination Arrival Late Reason',
                'Destination Delay Duration',
            ]),
        ];
    }

    // ─────────────────────────────────────────────────────────────
    //  Error report CSV
    // ─────────────────────────────────────────────────────────────

    public function generateErrorReport(array $invalidRows): string
    {
        $fileName = 'delays_import_errors_' . date('Y-m-d_His') . '.csv';
        $dir = 'temp-imports';
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
                !empty($row['errors']) ? implode('; ', $row['errors']) : '—',
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
        if ($raw === '')
            return null;

        if (preg_match('/^(\d{1,2}\/\d{1,2}\/\d{4}\s+\d{1,2}:\d{2})\s*(CST|EST|CDT|EDT|MST|MDT|PST|PDT|AKST|AKDT|HST|AST)?$/i', $raw, $m)) {
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
        if ($raw === '')
            return null;

        $totalMinutes = 0;
        $matched = 0;
        $remaining = $raw;

        while (preg_match('/^(\d+)\s*([DdHhMm])(.*)$/s', $remaining, $m)) {
            $value = (int) $m[1];
            $unit = strtolower($m[2]);
            $remaining = $m[3];
            $matched++;

            $totalMinutes += match ($unit) {
                'd' => $value * 1440,
                'h' => $value * 60,
                'm' => $value,
            };
        }

        // Leftover non-whitespace → unrecognised format → invalid
        if (trim($remaining) !== '')
            return null;

        return ($matched > 0 && $totalMinutes > 0) ? $totalMinutes : null;
    }

    private function buildPreview(array $data, array $fields): array
    {
        $preview = [];
        foreach ($fields as $field) {
            $val = $data[$field] ?? '';
            if ($val === '')
                continue;
            if (strlen($val) > 30)
                $val = substr($val, 0, 30) . '...';
            $preview[] = ['key' => $field, 'label' => $field, 'value' => $val];
        }

        return !empty($preview) ? $preview : [['key' => 'row', 'label' => 'Row', 'value' => '(empty)']];
    }

    private function columnMismatchResult(int $rowNumber, array $row, array $headers): array
    {
        return [
            'rowNumber' => $rowNumber,
            'isValid' => false,
            'errors' => ['Column count mismatch. Expected ' . count($headers) . ', got ' . count($row)],
            'warnings' => [],
            'data' => $row,
            'preview' => $this->buildRawPreview($row, $headers),
        ];
    }

    private function buildRawPreview(array $row, array $headers): array
    {
        $out = [];
        foreach (array_slice($headers, 0, 3) as $i => $h) {
            $val = isset($row[$i]) ? (string) $row[$i] : '';
            if (strlen($val) > 30)
                $val = substr($val, 0, 30) . '...';
            $out[] = ['key' => $h, 'label' => $h, 'value' => $val];
        }
        return !empty($out) ? $out : [['key' => 'row', 'label' => 'Row', 'value' => '(empty)']];
    }
}
