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
public function validateRepairOrdersCsv($file, string $importType = 'template', ?int $tenantId = null): array
{
    $handle = fopen($file->getRealPath(), 'r');
    if (!$handle) throw new \Exception('Unable to open CSV file.');

    $user = Auth::user();
    $isSuperAdmin = $user && $user->tenant_id === null;
    // -------------------------
    // 1) Expected headers
    // -------------------------
    if ($importType === 'template') {
        $expectedHeaders = $isSuperAdmin
            ? [
                'tenant_name','ro_number','ro_open_date','ro_close_date',
                'truckid','repairs_made','vendor','wo_number','wo_status',
                'invoice','invoice_amount','invoice_received','on_qs',
                'qs_invoice_date','disputed','dispute_outcome','area_of_concerns'
              ]
            : [
                'ro_number','ro_open_date','ro_close_date',
                'truckid','repairs_made','vendor','wo_number','wo_status',
                'invoice','invoice_amount','invoice_received','on_qs',
                'qs_invoice_date','disputed','dispute_outcome','area_of_concerns'
              ];
    } else {
        // QuickSight export headers (exact match, normalized)
        $expectedHeaders = [
            'Work Order #',
            'Vendor',
            'WO start date',
            'WO end date',
            'Asset ID',
            'Asset age (days)',
            'Fuel type',
            'Invoice #',
            'Invoice date',
            'Processing Timestamp',
            'Invoice report week',
            'Dispute/Review status',
            'Dispute/Review determination',
            'Dispute outcome',
            'Invoice amount',
            'Invoice revised amount',
            'Exemption Reason',
            'Invoice revised amount post exemptions',
            'T6W indicator',
            'Allowance time period',
        ];
    }

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

$normalizedHeaderRow = array_map(function ($h) {
    $h = (string) $h;

    // Remove UTF-8 BOM if present (usually only on first header)
    $h = preg_replace('/^\xEF\xBB\xBF/', '', $h);

    // Trim whitespace
    $h = trim($h);

    // Remove wrapping quotes if present
    $h = trim($h, "\"'");

    // Lowercase for comparison
    return strtolower($h);
}, $headerRow);

$normalizedExpected = array_map(function ($h) {
    $h = (string) $h;
    $h = trim($h);
    $h = trim($h, "\"'");
    return strtolower($h);
}, $expectedHeaders);    if (count($normalizedHeaderRow) !== count($normalizedExpected)) {
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
            'header_error' => 'CSV headers do not match expected format.',
        ];
    }

    $this->results = [
        'headers' => $expectedHeaders,
        'valid' => [],
        'invalid' => [],
        'summary' => ['total' => 0, 'valid' => 0, 'invalid' => 0],
    ];

    $rowNumber = 1;

    while (($row = fgetcsv($handle, 0, ',')) !== false) {
        $rowNumber++;
        $this->results['summary']['total']++;

        $validationResult = $importType === 'template'
            ? $this->validateRowTemplate($row, $expectedHeaders, $rowNumber, $isSuperAdmin)
            : $this->validateRowQuickSight($row, $expectedHeaders, $rowNumber, $isSuperAdmin, $tenantId);

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
    protected function validateRowTemplate(array $row, array $expectedHeaders, int $rowNumber, bool $isSuperAdmin): array
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
        if (!empty($data['wo_status'])) {
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
protected function validateRowQuickSight(
    array $row,
    array $expectedHeaders,
    int $rowNumber,
    bool $isSuperAdmin,
    ?int $tenantId
): array {
    $errors = [];
    $warnings = [];

    if (count($row) !== count($expectedHeaders)) {
        return [
            'rowNumber' => $rowNumber,
            'isValid' => false,
            'errors' => ['Column count mismatch.'],
            'warnings' => [],
            'data' => $row,
            'preview' => [
                ['key' => 'col1', 'label' => 'Column 1', 'value' => (string)($row[0] ?? '')],
            ],
        ];
    }

    $qs = array_combine($expectedHeaders, $row);

    // Map QS -> internal template-like structure
    // Requirements you said:
    // 1st col RO# => ro_number
    // 2nd vendor => vendor
    // 3rd start => ro_open_date
    // 4th end => ro_close_date
    // 5th asset id => truckid
    // 9th invoice date => qs_invoice_date
    // 15th invoice amount => invoice_amount

    $data = [
        'ro_number'        => trim((string)($qs['Work Order #'] ?? '')),
        'vendor'           => trim((string)($qs['Vendor'] ?? '')),
        'ro_open_date'     => trim((string)($qs['WO start date'] ?? '')),
        'ro_close_date'    => trim((string)($qs['WO end date'] ?? '')),
        'truckid'          => trim((string)($qs['Asset ID'] ?? '')),
        'invoice'          => trim((string)($qs['Invoice #'] ?? '')),
        'qs_invoice_date'  => trim((string)($qs['Invoice date'] ?? '')),
        'invoice_amount'   => trim((string)($qs['Invoice amount'] ?? '')),

        // fill missing required fields
        'on_qs'            => 'yes',
        'disputed'         => 'no',
        'invoice_received' => !empty($qs['Invoice #']) ? 'yes' : 'no',

        // optional fields we don't have
        'repairs_made'     => '',
        'wo_number'        => '',
        'wo_status'        => '',
        'dispute_outcome'  => '',
        'area_of_concerns' => '',
    ];

    // If SuperAdmin QuickSight import: use provided tenant_id (required)
    if ($isSuperAdmin) {
        if (!$tenantId) $errors[] = 'Tenant is required for QuickSight import.';
    }

    // Date formats: QuickSight might be m/d/Y, Y-m-d, or timestamps.
    // We'll support both m/d/Y and Y-m-d quickly.
    $data['ro_open_date'] = $this->parseDateFlex($data['ro_open_date'], 'RO Open Date', $errors);
    $data['ro_close_date'] = $this->parseDateFlexNullable($data['ro_close_date'], 'RO Close Date', $errors);
    $data['qs_invoice_date'] = $this->parseDateFlexNullable($data['qs_invoice_date'], 'Invoice Date', $errors);

    // Required: ro_number, truckid, vendor, ro_open_date
    if ($data['ro_number'] === '') $errors[] = 'RO Number is required';
    if ($data['truckid'] === '') $errors[] = 'Truck ID is required';
    if ($data['vendor'] === '') $errors[] = 'Vendor is required';
    if (empty($data['ro_open_date'])) $errors[] = 'RO Open Date is required';

    // Validate truck exists
    if ($data['truckid'] !== '') {
        $truck = \App\Models\Truck::where('truckid', $data['truckid'])->first();
        if (!$truck) $errors[] = "Truck not found: {$data['truckid']}";
    }

    // Validate vendor exists
    if ($data['vendor'] !== '') {
        $vendor = \App\Models\Vendor::where('vendor_name', $data['vendor'])->first();
        if (!$vendor) $errors[] = "Vendor not found: {$data['vendor']}";
        elseif (method_exists($vendor, 'trashed') && $vendor->trashed()) $errors[] = "Vendor is deleted: {$data['vendor']}";
    }

    // invoice_amount numeric if present
    if ($data['invoice_amount'] !== '' && !is_numeric($data['invoice_amount'])) {
        $errors[] = "Invoice Amount must be numeric: {$data['invoice_amount']}";
    }

    // Preview (show key fields)
    $preview = [
        ['key' => 'ro_number', 'label' => 'RO#', 'value' => $data['ro_number']],
        ['key' => 'truckid', 'label' => 'Truck', 'value' => $data['truckid']],
        ['key' => 'vendor', 'label' => 'Vendor', 'value' => $data['vendor']],
        ['key' => 'ro_open_date', 'label' => 'Open Date', 'value' => (string)($qs['WO start date'] ?? '')],
    ];

    return [
        'rowNumber' => $rowNumber,
        'isValid' => empty($errors),
        'errors' => $errors,
        'warnings' => $warnings,
        'data' => $data,     // IMPORTANT: store mapped data so import step can reuse if you want
        'preview' => $preview,
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

        protected function parseDateFlex(string $val, string $label, array &$errors): ?string
{
    $val = trim($val);
    if ($val === '') return null;

    try {
        // try m/d/Y
        return Carbon::createFromFormat('m/d/Y', $val)->format('Y-m-d');
    } catch (\Exception $e) {}

    try {
        // try Y-m-d
        return Carbon::createFromFormat('Y-m-d', $val)->format('Y-m-d');
    } catch (\Exception $e) {}

    // try Carbon parse (timestamps)
    try {
        return Carbon::parse($val)->format('Y-m-d');
    } catch (\Exception $e) {
        $errors[] = "{$label} format invalid: {$val}";
        return null;
    }
}

protected function parseDateFlexNullable(string $val, string $label, array &$errors): ?string
{
    $val = trim($val);
    if ($val === '') return null;
    return $this->parseDateFlex($val, $label, $errors);
}
}
    