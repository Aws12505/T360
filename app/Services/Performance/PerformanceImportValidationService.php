<?php

namespace App\Services\Performance;

use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class PerformanceImportValidationService
{
    protected array $results = [
        'valid' => [],
        'invalid' => [],
        'summary' => [
            'total' => 0,
            'valid' => 0,
            'invalid' => 0,
        ],
        // Detected headers from file
        'headers' => [],
        // Expected template headers
        'expected_headers' => [],
    ];

    public function validatePerformancesCsv($file): array
    {
        $handle = fopen($file->getRealPath(), 'r');
        if (!$handle) {
            throw new \Exception('Unable to open CSV file.');
        }

        $user = Auth::user();
        $isSuperAdmin = $user && $user->tenant_id === null;

        $expectedHeaders = $isSuperAdmin
            ? [
                'tenant_name',
                'date',
                'acceptance',
                'on_time_to_origin',
                'on_time_to_destination',
                'maintenance_variance_to_spend',
                'open_boc',
                'meets_safety_bonus_criteria',
                'vcr_preventable',
                'vmcr_p',
            ]
            : [
                'date',
                'acceptance',
                'on_time_to_origin',
                'on_time_to_destination',
                'maintenance_variance_to_spend',
                'open_boc',
                'meets_safety_bonus_criteria',
                'vcr_preventable',
                'vmcr_p',
            ];

        $this->results['expected_headers'] = $expectedHeaders;

        $headerRow = fgetcsv($handle, 0, ',');
        if ($headerRow === false) {
            fclose($handle);

            return [
                ...$this->results,
                'header_error' => 'CSV appears to be empty or unreadable.',
            ];
        }

        $headerRow = array_map(fn ($h) => trim((string) $h), $headerRow);
        $this->results['headers'] = $headerRow;

        // strict count check
        if (count($headerRow) !== count($expectedHeaders)) {
            fclose($handle);

            return [
                ...$this->results,
                'header_error' =>
                    'CSV headers do not match expected format. Expected ' .
                    count($expectedHeaders) . ' columns, got ' . count($headerRow) . ' columns.',
            ];
        }

        // normalize + strict order match
        $normalizedIncoming = array_map(fn ($h) => strtolower(trim((string) $h)), $headerRow);
        $normalizedExpected = array_map(fn ($h) => strtolower(trim((string) $h)), $expectedHeaders);

        if ($normalizedIncoming !== $normalizedExpected) {
            fclose($handle);

            return [
                ...$this->results,
                'header_error' => 'CSV header names/order do not match expected template.',
            ];
        }

        $rowNumber = 1; // header is row 1
        while (($row = fgetcsv($handle, 0, ',')) !== false) {
            $rowNumber++;
            $this->results['summary']['total']++;

            $validationResult = $this->validateRow($row, $expectedHeaders, $rowNumber, $isSuperAdmin);

            if ($validationResult['isValid']) {
                $this->results['valid'][] = $validationResult;
                $this->results['summary']['valid']++;
            } else {
                $this->results['invalid'][] = $validationResult;
                $this->results['summary']['invalid']++;
            }
        }

        fclose($handle);
        return $this->results;
    }

    protected function validateRow(array $row, array $expectedHeaders, int $rowNumber, bool $isSuperAdmin): array
    {
        $errors = [];
        $warnings = []; // included for UI parity (and future use)

        // Column count check
        if (count($row) !== count($expectedHeaders)) {
            return [
                'rowNumber' => $rowNumber,
                'isValid' => false,
                'errors' => [
                    'Column count mismatch. Expected ' . count($expectedHeaders) . ' columns, got ' . count($row),
                ],
                'warnings' => [],
                'data' => $row,
                // IMPORTANT: preview must be an array of {key,label,value} like repair orders
                'preview' => $this->getRawPreviewWithHeaders($row, $expectedHeaders),
            ];
        }

        $data = array_combine($expectedHeaders, $row);

        // Trim all values
        $data = collect($data)->map(fn ($v) => is_string($v) ? trim($v) : $v)->toArray();

        // SuperAdmin tenant check
        if ($isSuperAdmin) {
            if (empty($data['tenant_name'])) {
                $errors[] = 'Tenant name is required';
            } else {
                $tenant = Tenant::where('name', $data['tenant_name'])->first();
                if (!$tenant) {
                    $errors[] = "Tenant not found: {$data['tenant_name']}";
                }
            }
        }

        // Date validation (m/d/Y)
        if (empty($data['date'])) {
            $errors[] = 'Date is required';
        } else {
            try {
                Carbon::createFromFormat('m/d/Y', $data['date']);
            } catch (\Exception $e) {
                $errors[] = 'Date format invalid (expected m/d/Y): ' . $data['date'];
            }
        }

        // Required numeric fields
        $numericFields = [
            'acceptance' => 'Acceptance',
            'on_time_to_origin' => 'On Time to Origin',
            'on_time_to_destination' => 'On Time to Destination',
            'maintenance_variance_to_spend' => 'Maintenance Variance to Spend',
        ];

        foreach ($numericFields as $field => $label) {
            if (!array_key_exists($field, $data) || $data[$field] === '' || $data[$field] === null) {
                $errors[] = "{$label} is required";
            } elseif (!is_numeric($data[$field])) {
                $errors[] = "{$label} must be numeric: {$data[$field]}";
            }
        }

        // Required integer fields
        $intFields = [
            'open_boc' => 'Open BOC',
            'vcr_preventable' => 'VCR Preventable',
            'vmcr_p' => 'VMCR P',
        ];

        foreach ($intFields as $field => $label) {
            if (!array_key_exists($field, $data) || $data[$field] === '' || $data[$field] === null) {
                $errors[] = "{$label} is required";
            } elseif (!ctype_digit((string) $data[$field])) {
                $errors[] = "{$label} must be an integer: {$data[$field]}";
            }
        }

        // Boolean yes/no field
        $boolRaw = strtolower(trim((string) ($data['meets_safety_bonus_criteria'] ?? '')));
        if ($boolRaw === '') {
            $errors[] = "Meets Safety Bonus Criteria is required (Yes/No)";
        } elseif (!in_array($boolRaw, ['yes', 'no'], true)) {
            $errors[] = "Meets Safety Bonus Criteria must be 'Yes' or 'No', got: {$data['meets_safety_bonus_criteria']}";
        }

        return [
            'rowNumber' => $rowNumber,
            'isValid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
            'data' => $data,
            // IMPORTANT: preview must be structured array for the UI
            'preview' => $this->getRowPreviewWithHeaders($data, $isSuperAdmin),
        ];
    }

    /**
     * Structured preview like Repair Orders:
     * [
     *   { key, label, value },
     *   ...
     * ]
     */
    protected function getRowPreviewWithHeaders(array $data, bool $isSuperAdmin): array
    {
        $previewFields = $isSuperAdmin
            ? ['tenant_name', 'date', 'acceptance', 'on_time_to_origin']
            : ['date', 'acceptance', 'on_time_to_origin', 'on_time_to_destination'];

        $labels = [
            'tenant_name' => 'Tenant',
            'date' => 'Date',
            'acceptance' => 'Acceptance',
            'on_time_to_origin' => 'OTO',
            'on_time_to_destination' => 'OTD',
        ];

        $preview = [];
        foreach ($previewFields as $key) {
            if (!isset($data[$key]) || $data[$key] === '') continue;

            $value = (string) $data[$key];
            if (strlen($value) > 30) $value = substr($value, 0, 30) . '...';

            $preview[] = [
                'key' => $key,
                'label' => $labels[$key] ?? $key,
                'value' => $value,
            ];
        }

        if (empty($preview)) {
            $preview[] = ['key' => 'row', 'label' => 'Row', 'value' => '(empty)'];
        }

        return $preview;
    }

    /**
     * For mismatch rows (column count, etc.) show first 3 columns as preview.
     */
    protected function getRawPreviewWithHeaders(array $row, array $expectedHeaders): array
    {
        $out = [];
        foreach (array_slice($expectedHeaders, 0, 3) as $i => $h) {
            $val = isset($row[$i]) ? (string) $row[$i] : '';
            if (strlen($val) > 30) $val = substr($val, 0, 30) . '...';

            $out[] = [
                'key' => $h,
                'label' => $h,
                'value' => $val,
            ];
        }

        if (empty($out)) {
            $out[] = ['key' => 'row', 'label' => 'Row', 'value' => '(empty)'];
        }

        return $out;
    }

    public function generateErrorReport(array $invalidRows): string
    {
        $fileName = 'performance_import_errors_' . date('Y-m-d_His') . '.csv';
        $filePath = storage_path('app/temp/' . $fileName);

        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        $file = fopen($filePath, 'w');

        // headers
        fputcsv($file, ['Row Number', 'Preview', 'Errors', 'Warnings']);

        foreach ($invalidRows as $row) {
            // Convert structured preview array to readable string for CSV report
            $previewString = '—';
            if (is_array($row['preview'] ?? null)) {
                $parts = [];
                foreach ($row['preview'] as $p) {
                    $label = $p['label'] ?? '';
                    $val = $p['value'] ?? '';
                    if ($label !== '' && $val !== '') $parts[] = "{$label}: {$val}";
                }
                $previewString = !empty($parts) ? implode(' | ', $parts) : '—';
            } elseif (isset($row['preview'])) {
                $previewString = (string) $row['preview'];
            }

            fputcsv($file, [
                $row['rowNumber'] ?? '—',
                $previewString,
                isset($row['errors']) ? implode('; ', $row['errors']) : '—',
                !empty($row['warnings']) ? implode('; ', $row['warnings']) : '—',
            ]);
        }

        fclose($file);
        return $filePath;
    }
}
