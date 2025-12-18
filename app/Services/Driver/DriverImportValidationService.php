<?php

namespace App\Services\Driver;

use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DriverImportValidationService
{
    protected array $results = [
        'valid' => [],
        'invalid' => [],
        'summary' => [
            'total' => 0,
            'valid' => 0,
            'invalid' => 0,
        ],
        'headers' => [],
        'expected_headers' => [],
    ];

    public function validateDriversCsv($file): array
    {
        $handle = fopen($file->getRealPath(), 'r');
        if (!$handle) {
            throw new \Exception('Unable to open CSV file.');
        }

        $user = Auth::user();
        $isSuperAdmin = $user && $user->tenant_id === null;

        $expectedHeaders = $isSuperAdmin
            ? ['tenant_name','first_name','last_name','email','password','netradyne_user_name','mobile_phone','hiring_date']
            : ['first_name','last_name','email','password','netradyne_user_name','mobile_phone','hiring_date'];

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

        $rowNumber = 1; // header row
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
        $warnings = [];

        if (count($row) !== count($expectedHeaders)) {
            return [
                'rowNumber' => $rowNumber,
                'isValid' => false,
                'errors' => [
                    'Column count mismatch. Expected ' . count($expectedHeaders) . ' columns, got ' . count($row),
                ],
                'warnings' => [],
                'data' => $row,
                'preview' => $this->getRawPreviewWithHeaders($row, $expectedHeaders),
            ];
        }

        $data = array_combine($expectedHeaders, $row);
        $data = collect($data)->map(fn ($v) => is_string($v) ? trim($v) : $v)->toArray();

        // SuperAdmin: tenant_name required + must exist
        if ($isSuperAdmin) {
            if (empty($data['tenant_name'])) {
                $errors[] = 'Tenant name is required';
            } else {
                $tenant = Tenant::where('name', $data['tenant_name'])->first();
                if (!$tenant) $errors[] = "Tenant not found: {$data['tenant_name']}";
            }
        }

        // Required fields
        foreach (['first_name','last_name','email','netradyne_user_name','mobile_phone','hiring_date'] as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                $errors[] = ucfirst(str_replace('_',' ', $field)) . ' is required';
            }
        }

        // Email format
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Email is invalid: {$data['email']}";
        }

        // hiring_date format m/d/Y
        if (!empty($data['hiring_date'])) {
            try {
                Carbon::createFromFormat('m/d/Y', $data['hiring_date']);
            } catch (\Exception $e) {
                $errors[] = 'Hiring date format invalid (expected m/d/Y): ' . $data['hiring_date'];
            }
        }

        // password is allowed to be empty (service fills default) but if provided, enforce min len
        if (!empty($data['password']) && strlen((string)$data['password']) < 8) {
            $errors[] = "Password must be at least 8 characters if provided";
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
            ? ['tenant_name','email','first_name','last_name']
            : ['email','first_name','last_name','hiring_date'];

        $labels = [
            'tenant_name' => 'Tenant',
            'email' => 'Email',
            'first_name' => 'First',
            'last_name' => 'Last',
            'hiring_date' => 'Hire Date',
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
        $fileName = 'driver_import_errors_' . date('Y-m-d_His') . '.csv';
        $dir = storage_path('app/temp');
        $filePath = $dir . '/' . $fileName;

        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }

        $file = fopen($filePath, 'w');
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
