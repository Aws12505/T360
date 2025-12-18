<?php

namespace App\Services\Acceptance;

use App\Models\Tenant;
use App\Models\RejectionReasonCode;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class RejectionImportValidationService
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

    public function validateRejectionsCsv($file): array
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
                'rejection_type',
                'driver_name',
                'rejection_category',
                'reason_code',
                'disputed',
                'driver_controllable',
            ]
            : [
                'date',
                'rejection_type',
                'driver_name',
                'rejection_category',
                'reason_code',
                'disputed',
                'driver_controllable',
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

        // Trim all headers
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

        // Column count check (use RAW preview: first 3 columns only)
        if (count($row) !== count($expectedHeaders)) {
            return [
                'rowNumber' => $rowNumber,
                'isValid' => false,
                'errors' => [
                    'Column count mismatch. Expected ' . count($expectedHeaders) . ' columns, got ' . count($row),
                ],
                'warnings' => [],
                'data' => $row,
                // IMPORTANT: preview must be an array of {key,label,value}
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

        // rejection_type
        $type = strtolower(trim((string) ($data['rejection_type'] ?? '')));
        if (!in_array($type, ['block', 'load'], true)) {
            $errors[] = "Rejection type must be 'block' or 'load'";
        }

        // driver_name
        if (empty($data['driver_name'])) {
            $errors[] = 'Driver name is required';
        }

        // rejection_category depends on type
        $category = (string) ($data['rejection_category'] ?? '');
        if (!$this->validateRejectionCategory($type, $category)) {
            $errors[] = "Rejection category invalid for type '{$type}': {$category}";
        }

        // reason_code must exist
        if (empty($data['reason_code'])) {
            $errors[] = 'Reason code is required';
        } else {
            $exists = RejectionReasonCode::where('reason_code', $data['reason_code'])->exists();
            if (!$exists) {
                $errors[] = "Reason code not found: {$data['reason_code']}";
            }
        }

        // disputed Yes/No
        $disputedRaw = strtolower(trim((string) ($data['disputed'] ?? '')));
        if ($disputedRaw === '') {
            $errors[] = "Disputed is required (Yes/No)";
        } elseif (!in_array($disputedRaw, ['yes', 'no'], true)) {
            $errors[] = "Disputed must be 'Yes' or 'No', got: {$data['disputed']}";
        }

        // driver_controllable Yes/No/N/A (nullable)
        $dcRaw = strtolower(trim((string) ($data['driver_controllable'] ?? '')));
        if ($dcRaw !== '' && !in_array($dcRaw, ['yes', 'no', 'n/a'], true)) {
            $errors[] = "Driver controllable must be 'Yes', 'No', or 'N/A', got: {$data['driver_controllable']}";
        }

        return [
            'rowNumber' => $rowNumber,
            'isValid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
            'data' => $data,
            // IMPORTANT: structured preview for UI (like Performance)
            'preview' => $this->getRowPreviewWithHeaders($data, $isSuperAdmin),
        ];
    }

    private function validateRejectionCategory(string $type, string $category): bool
    {
        $block = ['after_start', 'within_24', 'more_than_24', 'advanced_rejection'];
        $load = ['after_start', 'within_6', 'more_than_6'];

        if ($type === 'block') {
            return in_array($category, $block, true);
        }

        if ($type === 'load') {
            return in_array($category, $load, true);
        }

        return false;
    }

    /**
     * Structured preview like Performance/Repair Orders:
     * [
     *   { key, label, value },
     *   ...
     * ]
     */
    protected function getRowPreviewWithHeaders(array $data, bool $isSuperAdmin): array
    {
        $previewFields = $isSuperAdmin
            ? ['tenant_name', 'date', 'rejection_type', 'reason_code']
            : ['date', 'rejection_type', 'driver_name', 'reason_code'];

        $labels = [
            'tenant_name' => 'Tenant',
            'date' => 'Date',
            'rejection_type' => 'Type',
            'driver_name' => 'Driver',
            'rejection_category' => 'Category',
            'reason_code' => 'Reason Code',
            'disputed' => 'Disputed',
            'driver_controllable' => 'Driver Controllable',
        ];

        $preview = [];

        foreach ($previewFields as $key) {
            if (!array_key_exists($key, $data) || $data[$key] === '' || $data[$key] === null) {
                continue;
            }

            $value = (string) $data[$key];
            if (strlen($value) > 30) {
                $value = substr($value, 0, 30) . '...';
            }

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
    private function getRawPreviewWithHeaders(array $row, array $expectedHeaders): array
    {
        $out = [];

        foreach (array_slice($expectedHeaders, 0, 3) as $i => $h) {
            $val = isset($row[$i]) ? (string) $row[$i] : '';
            if (strlen($val) > 30) {
                $val = substr($val, 0, 30) . '...';
            }

            $out[] = [
                'key' => $h,
                'label' => ucwords(str_replace('_', ' ', (string) $h)),
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
        $fileName = 'rejections_import_errors_' . date('Y-m-d_His') . '.csv';

        // Keep your existing temp-imports folder approach
        $dir = 'temp-imports';
        Storage::makeDirectory($dir);

        $path = $dir . '/' . $fileName;
        $full = Storage::path($path);

        $file = fopen($full, 'w');

        // headers (Performance style)
        fputcsv($file, ['Row Number', 'Preview', 'Errors', 'Warnings']);

        foreach ($invalidRows as $row) {
            // Convert structured preview array to readable string for CSV report
            $previewString = '—';
            if (is_array($row['preview'] ?? null)) {
                $parts = [];
                foreach ($row['preview'] as $p) {
                    $label = $p['label'] ?? '';
                    $val = $p['value'] ?? '';
                    if ($label !== '' && $val !== '') {
                        $parts[] = "{$label}: {$val}";
                    }
                }
                $previewString = !empty($parts) ? implode(' | ', $parts) : '—';
            } elseif (isset($row['preview'])) {
                $previewString = (string) $row['preview'];
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
}
