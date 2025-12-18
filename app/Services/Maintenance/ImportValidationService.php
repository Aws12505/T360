<?php

namespace App\Services\Maintenance;

use App\Models\{Vendor, AreaOfConcern, Truck, WoStatus, Tenant};
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ImportValidationService
{
    protected array $results = [
        'headers' => [],
        'valid' => [],
        'invalid' => [],
        'summary' => [
            'total' => 0,
            'valid' => 0,
            'invalid' => 0,
        ],
    ];

    /**
     * Validate CSV file without importing
     */
    public function validateRepairOrdersCsv($file): array
    {
        $handle = fopen($file->getRealPath(), 'r');
        if (!$handle) {
            throw new \Exception('Unable to open CSV file.');
        }

        $user = Auth::user();
        $isSuperAdmin = $user && $user->tenant_id === null;

        $expectedHeaders = $isSuperAdmin
            ? [
                'tenant_name', 'ro_number', 'ro_open_date', 'ro_close_date',
                'truckid', 'repairs_made', 'vendor', 'wo_number', 'wo_status',
                'invoice', 'invoice_amount', 'invoice_received', 'on_qs',
                'qs_invoice_date', 'disputed', 'dispute_outcome', 'area_of_concerns'
            ]
            : [
                'ro_number', 'ro_open_date', 'ro_close_date',
                'truckid', 'repairs_made', 'vendor', 'wo_number', 'wo_status',
                'invoice', 'invoice_amount', 'invoice_received', 'on_qs',
                'qs_invoice_date', 'disputed', 'dispute_outcome', 'area_of_concerns'
            ];

        $headerRow = fgetcsv($handle, 0, ',');

        if ($headerRow === false) {
            fclose($handle);
            return [
                'headers' => $expectedHeaders,
                'valid' => [],
                'invalid' => [],
                'summary' => ['total' => 0, 'valid' => 0, 'invalid' => 0],
                'header_error' => 'CSV file appears to be empty or unreadable.',
            ];
        }

        // Normalize headers (trim + lowercase) to avoid tiny format mistakes
        $normalizedHeaderRow = array_map(fn ($h) => strtolower(trim((string) $h)), $headerRow);
        $normalizedExpected = array_map(fn ($h) => strtolower(trim((string) $h)), $expectedHeaders);

        // Validate headers (count + exact match)
        if (count($normalizedHeaderRow) !== count($normalizedExpected)) {
            fclose($handle);
            return [
                'headers' => $expectedHeaders,
                'valid' => [],
                'invalid' => [],
                'summary' => ['total' => 0, 'valid' => 0, 'invalid' => 0],
                'header_error' => 'CSV headers do not match expected format. Expected ' .
                    count($expectedHeaders) . ' columns, got ' . count($headerRow) . ' columns.',
            ];
        }

        if ($normalizedHeaderRow !== $normalizedExpected) {
            fclose($handle);
            return [
                'headers' => $expectedHeaders,
                'valid' => [],
                'invalid' => [],
                'summary' => ['total' => 0, 'valid' => 0, 'invalid' => 0],
                'header_error' => 'CSV headers do not match expected format. Please download the template and try again.',
            ];
        }

        // Store headers in results for frontend display
        $this->results['headers'] = $expectedHeaders;

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

    /**
     * Validate a single row
     */
    protected function validateRow(array $row, array $expectedHeaders, int $rowNumber, bool $isSuperAdmin): array
    {
        $errors = [];
        $warnings = [];

        // Column count mismatch
        if (count($row) !== count($expectedHeaders)) {
            return [
                'rowNumber' => $rowNumber,
                'isValid' => false,
                'errors' => [
                    'Column count mismatch. Expected ' . count($expectedHeaders) . ' columns, got ' . count($row),
                ],
                'warnings' => [],
                'data' => $row,
                'preview' => [
                    ['key' => 'col1', 'label' => 'Column 1', 'value' => (string)($row[0] ?? '')],
                    ['key' => 'col2', 'label' => 'Column 2', 'value' => (string)($row[1] ?? '')],
                    ['key' => 'col3', 'label' => 'Column 3', 'value' => (string)($row[2] ?? '')],
                ],
            ];
        }

        $data = array_combine($expectedHeaders, $row);
        $rowData = $data; // keep original for UI preview

        // Validate RO Open Date
        if (empty($data['ro_open_date'])) {
            $errors[] = 'RO Open Date is required';
        } else {
            try {
                $data['ro_open_date'] = Carbon::createFromFormat('m/d/Y', $data['ro_open_date'])->format('Y-m-d');
            } catch (\Exception $e) {
                $errors[] = 'RO Open Date format invalid (expected m/d/Y): ' . $data['ro_open_date'];
            }
        }

        // Validate RO Close Date
        if (!empty($data['ro_close_date'])) {
            try {
                $data['ro_close_date'] = Carbon::createFromFormat('m/d/Y', $data['ro_close_date'])->format('Y-m-d');
            } catch (\Exception $e) {
                $errors[] = 'RO Close Date format invalid (expected m/d/Y): ' . $data['ro_close_date'];
            }
        }

        // Validate QS Invoice Date
        if (!empty($data['qs_invoice_date'])) {
            try {
                $data['qs_invoice_date'] = Carbon::createFromFormat('m/d/Y', $data['qs_invoice_date'])->format('Y-m-d');
            } catch (\Exception $e) {
                $errors[] = 'QS Invoice Date format invalid (expected m/d/Y): ' . $data['qs_invoice_date'];
            }
        }

        // Validate truck
        if (empty($data['truckid'])) {
            $errors[] = 'Truck ID is required';
        } else {
            $truck = Truck::where('truckid', $data['truckid'])->first();
            if (!$truck) {
                $errors[] = "Truck not found: {$data['truckid']}";
            }
        }

        // Validate vendor
        if (empty($data['vendor'])) {
            $errors[] = 'Vendor is required';
        } else {
            $vendor = Vendor::where('vendor_name', $data['vendor'])->first();
            if (!$vendor) {
                $errors[] = "Vendor not found: {$data['vendor']}";
            } elseif (method_exists($vendor, 'trashed') && $vendor->trashed()) {
                $errors[] = "Vendor is deleted: {$data['vendor']}";
            }
        }

        // Validate WO Status
        if (empty($data['wo_status'])) {
            $errors[] = 'WO Status is required';
        } else {
            $woStatus = WoStatus::where('name', $data['wo_status'])->first();
            if (!$woStatus) {
                $errors[] = "WO Status not found: {$data['wo_status']}";
            } elseif (method_exists($woStatus, 'trashed') && $woStatus->trashed()) {
                $errors[] = "WO Status is deleted: {$data['wo_status']}";
            }
        }

        // Validate boolean fields
        $invoiceReceived = strtolower(trim((string)$data['invoice_received']));
        if (!in_array($invoiceReceived, ['yes', 'no'], true)) {
            $errors[] = "Invoice Received must be 'Yes' or 'No', got: {$data['invoice_received']}";
        }

        $onQs = strtolower(trim((string)$data['on_qs']));
        if (!in_array($onQs, ['yes', 'no', 'not expected'], true)) {
            $errors[] = "On QS must be 'Yes', 'No', or 'Not Expected', got: {$data['on_qs']}";
        }

        $disputed = strtolower(trim((string)$data['disputed']));
        if (!in_array($disputed, ['yes', 'no'], true)) {
            $errors[] = "Disputed must be 'Yes' or 'No', got: {$data['disputed']}";
        }

        // Validate areas of concern
        if (!empty($data['area_of_concerns'])) {
            $concernNames = array_map('trim', explode(',', (string)$data['area_of_concerns']));
            foreach ($concernNames as $concernName) {
                if ($concernName === '') continue;

                $area = AreaOfConcern::withTrashed()->where('concern', $concernName)->first();
                if (!$area) {
                    $errors[] = "Area of Concern not found: {$concernName}";
                } elseif ($area->trashed()) {
                    // keep this as a warning (UI will hide column if none exist)
                    $warnings[] = "Area of Concern is deleted (will be skipped): {$concernName}";
                }
            }
        }

        // Validate tenant for SuperAdmin
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

        // Validate numeric invoice amount
        if (!empty($data['invoice_amount']) && !is_numeric($data['invoice_amount'])) {
            $errors[] = "Invoice Amount must be numeric: {$data['invoice_amount']}";
        }

        // Validate RO Number
        if (empty($data['ro_number'])) {
            $errors[] = 'RO Number is required';
        }

        return [
            'rowNumber' => $rowNumber,
            'isValid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
            'data' => $rowData,
            'preview' => $this->getRowPreview($rowData, $isSuperAdmin),
        ];
    }

    /**
     * Return structured preview fields with labels so the UI can show:
     * RO#: 123 | Truck: ABC | Vendor: XYZ
     */
    protected function getRowPreview(array $data, bool $isSuperAdmin): array
    {
        $fields = $isSuperAdmin
            ? ['tenant_name', 'ro_number', 'truckid', 'ro_open_date', 'vendor']
            : ['ro_number', 'truckid', 'ro_open_date', 'vendor'];

        $labels = [
            'tenant_name' => 'Tenant',
            'ro_number' => 'RO#',
            'truckid' => 'Truck',
            'ro_open_date' => 'Open Date',
            'vendor' => 'Vendor',
        ];

        $preview = [];
        foreach ($fields as $key) {
            if (!isset($data[$key]) || $data[$key] === '') continue;

            $value = (string)$data[$key];
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
     * Generate downloadable error report CSV
     */
    public function generateErrorReport(array $invalidRows): string
    {
        $fileName = 'import_errors_' . date('Y-m-d_His') . '.csv';
        $filePath = storage_path('app/temp/' . $fileName);

        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        $file = fopen($filePath, 'w');

        fputcsv($file, ['Row Number', 'Preview', 'Errors', 'Warnings']);

        foreach ($invalidRows as $row) {
            // Convert structured preview array to readable string for csv report
            $previewString = '';
            if (is_array($row['preview'] ?? null)) {
                $parts = [];
                foreach ($row['preview'] as $p) {
                    $label = $p['label'] ?? '';
                    $val = $p['value'] ?? '';
                    if ($label !== '' && $val !== '') $parts[] = "{$label}: {$val}";
                }
                $previewString = implode(' | ', $parts);
            } else {
                $previewString = (string)($row['preview'] ?? '');
            }

            fputcsv($file, [
                $row['rowNumber'] ?? '',
                $previewString,
                implode('; ', $row['errors'] ?? []),
                !empty($row['warnings']) ? implode('; ', $row['warnings']) : '—',
            ]);
        }

        fclose($file);
        return $filePath;
    }
}
    