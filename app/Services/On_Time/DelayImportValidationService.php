<?php

namespace App\Services\On_Time;

use App\Models\Tenant;
use App\Models\DelayCode;
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

    public function validateDelaysCsv($file): array
    {
        $handle = fopen($file->getRealPath(), 'r');
        if (!$handle) {
            throw new \Exception('Unable to open CSV file.');
        }

        $user = Auth::user();
        $isSuperAdmin = $user && $user->tenant_id === null;

        $expectedHeaders = $isSuperAdmin
            ? ['tenant_name', 'date', 'delay_type', 'driver_name', 'delay_category', 'delay_code', 'disputed', 'driver_controllable']
            : ['date', 'delay_type', 'driver_name', 'delay_category', 'delay_code', 'disputed', 'driver_controllable'];

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

        if (count($headerRow) !== count($expectedHeaders)) {
            fclose($handle);
            return [
                ...$this->results,
                'header_error' =>
                    'CSV headers do not match expected format. Expected ' .
                    count($expectedHeaders) . ' columns, got ' . count($headerRow) . ' columns.',
            ];
        }

        $normalizedIncoming = array_map(fn ($h) => strtolower(trim((string) $h)), $headerRow);
        $normalizedExpected = array_map(fn ($h) => strtolower(trim((string) $h)), $expectedHeaders);

        if ($normalizedIncoming !== $normalizedExpected) {
            fclose($handle);
            return [
                ...$this->results,
                'header_error' => 'CSV header names/order do not match expected template.',
            ];
        }

        $rowNumber = 1;
        while (($row = fgetcsv($handle, 0, ',')) !== false) {
            $rowNumber++;
            $this->results['summary']['total']++;

            $validation = $this->validateRow($row, $expectedHeaders, $rowNumber, $isSuperAdmin);

            if ($validation['isValid']) {
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

    protected function validateRow(array $row, array $expectedHeaders, int $rowNumber, bool $isSuperAdmin): array
    {
        $errors = [];
        $warnings = [];

        // mismatch: preview ONLY first 3 columns
        if (count($row) !== count($expectedHeaders)) {
            return [
                'rowNumber' => $rowNumber,
                'isValid' => false,
                'errors' => ['Column count mismatch. Expected ' . count($expectedHeaders) . ' columns, got ' . count($row)],
                'warnings' => [],
                'data' => $row,
                'preview' => $this->getRawPreviewWithHeaders($row, $expectedHeaders),
            ];
        }

        $data = array_combine($expectedHeaders, $row);
        $data = collect($data)->map(fn ($v) => is_string($v) ? trim($v) : $v)->toArray();

        // tenant check (superadmin only)
        if ($isSuperAdmin) {
            if (empty($data['tenant_name'])) {
                $errors[] = 'Tenant name is required';
            } else {
                $tenant = Tenant::where('name', $data['tenant_name'])->first();
                if (!$tenant) $errors[] = "Tenant not found: {$data['tenant_name']}";
            }
        }

        // date m/d/Y
        if (empty($data['date'])) {
            $errors[] = 'Date is required';
        } else {
            try {
                Carbon::createFromFormat('m/d/Y', $data['date']);
            } catch (\Exception $e) {
                $errors[] = 'Date format invalid (expected m/d/Y): ' . $data['date'];
            }
        }

        // delay_type origin/destination
        $type = strtolower((string)($data['delay_type'] ?? ''));
        if (!in_array($type, ['origin', 'destination'], true)) {
            $errors[] = "Delay type must be 'origin' or 'destination'";
        }

        // driver_name
        if (empty($data['driver_name'])) {
            $errors[] = 'Driver name is required';
        }

        // delay_category allowed set
        $category = (string)($data['delay_category'] ?? '');
        $allowedCategories = ['1_120', '121_600', '601_plus', '1_60', '61_240', '241_600'];
        if (!in_array($category, $allowedCategories, true)) {
            $errors[] = "Delay category invalid: {$category}";
        }

        // delay_code must exist by code
        if (empty($data['delay_code'])) {
            $errors[] = 'Delay code is required';
        } else {
            $exists = DelayCode::where('code', $data['delay_code'])->exists();
            if (!$exists) $errors[] = "Delay code not found: {$data['delay_code']}";
        }

        // disputed Yes/No
        $disputedRaw = strtolower(trim((string)($data['disputed'] ?? '')));
        if ($disputedRaw === '') {
            $errors[] = "Disputed is required (Yes/No)";
        } elseif (!in_array($disputedRaw, ['yes', 'no'], true)) {
            $errors[] = "Disputed must be 'Yes' or 'No', got: {$data['disputed']}";
        }

        // driver_controllable Yes/No/N/A (nullable)
        $dcRaw = strtolower(trim((string)($data['driver_controllable'] ?? '')));
        if ($dcRaw !== '' && !in_array($dcRaw, ['yes', 'no', 'n/a'], true)) {
            $errors[] = "Driver controllable must be 'Yes', 'No', or 'N/A', got: {$data['driver_controllable']}";
        }

        return [
            'rowNumber' => $rowNumber,
            'isValid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
            'data' => $data,
            'preview' => $this->getRowPreviewWithHeaders($data, $isSuperAdmin),
        ];
    }

    protected function getRowPreviewWithHeaders(array $data, bool $isSuperAdmin): array
    {
        $previewFields = $isSuperAdmin
            ? ['tenant_name', 'date', 'delay_type', 'delay_code']
            : ['date', 'delay_type', 'driver_name', 'delay_code'];

        $labels = [
            'tenant_name' => 'Tenant',
            'date' => 'Date',
            'delay_type' => 'Type',
            'driver_name' => 'Driver',
            'delay_category' => 'Category',
            'delay_code' => 'Delay Code',
        ];

        $preview = [];

        foreach ($previewFields as $key) {
            if (!array_key_exists($key, $data) || $data[$key] === '' || $data[$key] === null) continue;

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

    private function getRawPreviewWithHeaders(array $row, array $expectedHeaders): array
    {
        $out = [];

        foreach (array_slice($expectedHeaders, 0, 3) as $i => $h) {
            $val = isset($row[$i]) ? (string) $row[$i] : '';
            if (strlen($val) > 30) $val = substr($val, 0, 30) . '...';

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
                !empty($row['errors']) ? implode('; ', $row['errors']) : '—',
                !empty($row['warnings']) ? implode('; ', $row['warnings']) : '—',
            ]);
        }

        fclose($file);

        return $full;
    }
}
