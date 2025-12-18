<?php

namespace App\Services\Truck;

use App\Models\Tenant;
use App\Models\Truck;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TruckImportValidationService
{
    protected array $results = [
        'valid' => [],
        'invalid' => [],
        'summary' => ['total' => 0, 'valid' => 0, 'invalid' => 0],
        'headers' => [],
        'expected_headers' => [],
    ];

    public function validateTrucksCsv($file): array
    {
        $handle = fopen($file->getRealPath(), 'r');
        if (!$handle) {
            throw new \Exception('Unable to open CSV file.');
        }

        $user = Auth::user();
        $isSuperAdmin = $user && $user->tenant_id === null;

        $expectedHeaders = $isSuperAdmin
            ? ['tenant_name', 'truckid', 'type', 'make', 'fuel', 'license', 'vin', 'status', 'inspection_status', 'inspection_expiry_date']
            : ['truckid', 'type', 'make', 'fuel', 'license', 'vin', 'status', 'inspection_status', 'inspection_expiry_date'];

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
                    'CSV header column count mismatch. Expected ' .
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

        // mismatch: preview only first 3 columns
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

        // superadmin tenant validation
        if ($isSuperAdmin) {
            if (empty($data['tenant_name'])) {
                $errors[] = 'Tenant name is required';
            } else {
                $tenant = Tenant::where('name', $data['tenant_name'])->first();
                if (!$tenant) $errors[] = "Tenant not found: {$data['tenant_name']}";
            }
        }

        // truckid int
        if (($data['truckid'] ?? '') === '') $errors[] = 'Truck ID is required';
        elseif (!ctype_digit((string)$data['truckid'])) $errors[] = "Truck ID must be an integer: {$data['truckid']}";

        // license int
        if (($data['license'] ?? '') === '') $errors[] = 'License is required';
        elseif (!ctype_digit((string)$data['license'])) $errors[] = "License must be an integer: {$data['license']}";

        // vin required + warn if duplicate exists (not blocking)
        if (empty($data['vin'])) {
            $errors[] = 'VIN is required';
        } else {
            $existing = Truck::where('vin', $data['vin'])->exists();
            if ($existing) $warnings[] = "VIN already exists and will be updated: {$data['vin']}";
        }

        // type enum
        $type = strtolower((string)($data['type'] ?? ''));
        if (!in_array($type, ['daycab', 'sleepercab'], true)) {
            $errors[] = "Type must be 'daycab' or 'sleepercab'";
        }

        // make enum
        $make = strtolower((string)($data['make'] ?? ''));
        $allowedMakes = ['international', 'kenworth', 'peterbilt', 'volvo', 'freightliner'];
        if (!in_array($make, $allowedMakes, true)) {
            $errors[] = "Make invalid: {$data['make']}";
        }

        // fuel enum
        $fuel = strtolower((string)($data['fuel'] ?? ''));
        if (!in_array($fuel, ['cng', 'diesel'], true)) {
            $errors[] = "Fuel must be 'cng' or 'diesel'";
        }

        // status enum (keep your exact set)
        $status = (string)($data['status'] ?? '');
        if (!in_array($status, ['active', 'inactive', 'Returned to AMZ'], true)) {
            $errors[] = "Status must be 'active', 'inactive', or 'Returned to AMZ'";
        }

        // inspection_status enum
        $insp = strtolower((string)($data['inspection_status'] ?? ''));
        if (!in_array($insp, ['good', 'expired'], true)) {
            $errors[] = "Inspection status must be 'good' or 'expired'";
        }

        // inspection_expiry_date required date
        if (empty($data['inspection_expiry_date'])) {
            $errors[] = 'Inspection expiry date is required';
        } else {
            try {
                // accept either Y-m-d or m/d/Y but normalize validation
                if (str_contains($data['inspection_expiry_date'], '/')) {
                    Carbon::createFromFormat('m/d/Y', $data['inspection_expiry_date']);
                } else {
                    Carbon::parse($data['inspection_expiry_date']);
                }
            } catch (\Exception $e) {
                $errors[] = 'Inspection expiry date invalid: ' . $data['inspection_expiry_date'];
            }
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
            ? ['tenant_name', 'truckid', 'vin', 'status']
            : ['truckid', 'vin', 'status', 'inspection_status'];

        $labels = [
            'tenant_name' => 'Tenant',
            'truckid' => 'Truck ID',
            'vin' => 'VIN',
            'status' => 'Status',
            'inspection_status' => 'Inspection',
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
        $fileName = 'trucks_import_errors_' . date('Y-m-d_His') . '.csv';

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
