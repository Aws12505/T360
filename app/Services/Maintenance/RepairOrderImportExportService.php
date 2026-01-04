<?php

namespace App\Services\Maintenance;

use App\Models\RepairOrder;
use App\Models\Vendor;
use App\Models\AreaOfConcern;
use App\Models\Truck;
use App\Models\WoStatus;
use Illuminate\Support\Facades\Auth;
use App\Models\Tenant;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Carbon\Carbon;

class RepairOrderImportExportService
{
    /**
     * Import repair orders from a CSV file.
     *
     * Supports two formats:
     * 1) template
     *  - SuperAdmin: tenant_name, ro_number, ro_open_date, ro_close_date, truckid, repairs_made, vendor,
     *    wo_number, wo_status, invoice, invoice_amount, invoice_received, on_qs, qs_invoice_date,
     *    disputed, dispute_outcome, area_of_concerns
     *  - Non-SuperAdmin: same minus tenant_name
     *
     * 2) quicksight (exported CSV)
     *  Work Order #, Vendor, WO start date, WO end date, Asset ID, Asset age (days), Fuel type, Invoice #,
     *  Invoice date, Processing Timestamp, Invoice report week, Dispute/Review status,
     *  Dispute/Review determination, Dispute outcome, Invoice amount, Invoice revised amount,
     *  Exemption Reason, Invoice revised amount post exemptions, T6W indicator, Allowance time period
     *
     * Mapping for quicksight:
     *  - ro_number        = Work Order #
     *  - vendor           = Vendor
     *  - ro_open_date     = WO start date
     *  - ro_close_date    = WO end date
     *  - truckid          = Asset ID
     *  - qs_invoice_date  = Invoice date
     *  - invoice_amount   = Invoice amount
     *  - invoice          = Invoice #
     *
     * Defaults for quicksight:
     *  - on_qs            = 'yes'
     *  - disputed         = false
     *  - invoice_received = (Invoice # present) ? true : false
     *  - wo_number        = null
     *  - wo_status_id     = null
     *  - repairs_made     = null
     *  - dispute_outcome  = null
     *  - area_of_concerns = []
     *
     * NOTE: wo_number and wo_status_id are nullable.
     *
     * IMPORTANT:
     *  - area_of_concerns is a MANY-TO-MANY pivot relationship and MUST NOT be included in $raw passed
     *    into create/updateOrCreate. We extract IDs and sync AFTER the RepairOrder is persisted.
     */
    public function importRepairOrders(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
            'importType' => 'nullable|in:template,quicksight',
            // Required only for SuperAdmin + quicksight (since QS doesn't include tenant_name)
            'tenant_id' => 'nullable|integer|exists:tenants,id',
        ]);

        $file = $request->file('file');
        $handle = fopen($file->getRealPath(), 'r');
        if (!$handle) {
            return redirect()->back()->with('error', 'Unable to open CSV file.');
        }

        $importType = $request->input('importType', 'template');
        $tenantIdFromRequest = $request->input('tenant_id');
        $isSuperAdmin = Auth::user()->tenant_id === null;

        // Define expected headers.
        if ($importType === 'template') {
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
        } else {
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

        // Read CSV header row.
        $headerRow = fgetcsv($handle, 0, ',');
        if ($headerRow === false) {
            fclose($handle);
            return redirect()->back()->with('error', 'CSV file appears to be empty or unreadable.');
        }

        // Normalize and validate headers (case/spacing/quotes/BOM tolerant).
        $normalizeHeader = function ($h): string {
            $h = (string) $h;
            $h = preg_replace('/^\xEF\xBB\xBF/', '', $h); // remove BOM
            $h = trim($h);
            $h = trim($h, "\"'"); // remove wrapping quotes
            return strtolower($h);
        };

        $normalizedHeaderRow = array_map($normalizeHeader, $headerRow);
        $normalizedExpected  = array_map($normalizeHeader, $expectedHeaders);

        if (count($normalizedHeaderRow) !== count($normalizedExpected) || $normalizedHeaderRow !== $normalizedExpected) {
            fclose($handle);
            return redirect()->back()->with('error', 'CSV header row does not match expected headers.');
        }

        // For SuperAdmin + quicksight we need a tenant_id (QS has no tenant_name).
        if ($isSuperAdmin && $importType === 'quicksight' && !$tenantIdFromRequest) {
            fclose($handle);
            return redirect()->back()->with('error', 'Tenant is required for QuickSight import.');
        }

        // Flexible date parser (supports m/d/Y, Y-m-d, timestamps).
        $parseFlexDate = function ($val): ?string {
            $val = trim((string) $val);
            if ($val === '') return null;

            try {
                return Carbon::createFromFormat('m/d/Y', $val)->format('Y-m-d');
            } catch (\Exception $e) {
                // continue
            }

            try {
                return Carbon::createFromFormat('Y-m-d', $val)->format('Y-m-d');
            } catch (\Exception $e) {
                // continue
            }

            try {
                return Carbon::parse($val)->format('Y-m-d');
            } catch (\Exception $e) {
                return null;
            }
        };

        $rowsImported = 0;
        $rowsSkipped = 0;

        while (($row = fgetcsv($handle, 0, ',')) !== false) {
            if (count($row) !== count($expectedHeaders)) {
                $rowsSkipped++;
                continue;
            }

            // Combine headers with row values
            $raw = array_combine($expectedHeaders, $row);

            // If quicksight, map to template-like structure used below
            if ($importType === 'quicksight') {
                $raw = [
                    'ro_number'        => trim((string)($raw['Work Order #'] ?? '')),
                    'vendor'           => trim((string)($raw['Vendor'] ?? '')),
                    'ro_open_date'     => trim((string)($raw['WO start date'] ?? '')),
                    'ro_close_date'    => trim((string)($raw['WO end date'] ?? '')),
                    'truckid'          => trim((string)($raw['Asset ID'] ?? '')),
                    'repairs_made'     => null,

                    'wo_number'        => null,
                    'wo_status'        => '',

                    'invoice'          => trim((string)($raw['Invoice #'] ?? '')),
                    'qs_invoice_date'  => trim((string)($raw['Invoice date'] ?? '')),
                    'invoice_amount'   => trim((string)($raw['Invoice amount'] ?? '')),

                    // defaults
                    'invoice_received' => (!empty($raw['Invoice #']) ? 'yes' : 'no'),
                    'on_qs'            => 'yes',
                    'disputed'         => 'no',
                    'dispute_outcome'  => null,

                    // QS doesn't have areas of concern
                    'area_of_concerns' => '',
                ];

                // SuperAdmin: tenant_id from request, Non-superadmin: from user tenant
                if ($isSuperAdmin) {
                    $raw['tenant_id'] = (int) $tenantIdFromRequest;
                } else {
                    $raw['tenant_id'] = Auth::user()->tenant_id;
                }
            }

            // Convert dates
            $raw['ro_open_date'] = $parseFlexDate($raw['ro_open_date'] ?? null);
            if (!$raw['ro_open_date']) {
                $rowsSkipped++;
                continue;
            }
            $raw['ro_close_date']   = $parseFlexDate($raw['ro_close_date'] ?? null);
            $raw['qs_invoice_date'] = $parseFlexDate($raw['qs_invoice_date'] ?? null);

            // Convert textual booleans to actual booleans / enums
            $raw['invoice_received'] = strtolower(trim((string)($raw['invoice_received'] ?? 'no'))) === 'yes';

            $onQsValue = strtolower(trim((string)($raw['on_qs'] ?? 'no')));
            $raw['on_qs'] = $onQsValue === 'yes'
                ? 'yes'
                : ($onQsValue === 'not expected' ? 'not expected' : 'no');

            $raw['disputed'] = strtolower(trim((string)($raw['disputed'] ?? 'no'))) === 'yes';

            // Process truck (truckid -> truck_id)
            $truckid = trim((string)($raw['truckid'] ?? ''));
            if ($truckid === '') {
                $rowsSkipped++;
                continue;
            }
            $truck = Truck::where('truckid', $truckid)->first();
            if (!$truck) {
                $rowsSkipped++;
                continue;
            }
            $raw['truck_id'] = $truck->id;
            unset($raw['truckid']);

            // Process vendor (vendor name -> vendor_id)
            $vendorName = trim((string)($raw['vendor'] ?? ''));
            if ($vendorName === '') {
                $rowsSkipped++;
                continue;
            }
            $vendor = Vendor::where('vendor_name', $vendorName)->first();
            if (!$vendor) {
                $rowsSkipped++;
                continue;
            }
            if (method_exists($vendor, 'trashed') && $vendor->trashed()) {
                $rowsSkipped++;
                continue;
            }
            $raw['vendor_id'] = $vendor->id;
            unset($raw['vendor']);

            // Process WO Status (nullable)
            $raw['wo_status_id'] = null;
            $woStatusName = trim((string)($raw['wo_status'] ?? ''));
            if ($woStatusName !== '') {
                $woStatus = WoStatus::where('name', $woStatusName)->first();
                if (!$woStatus) {
                    $rowsSkipped++;
                    continue;
                }
                if (method_exists($woStatus, 'trashed') && $woStatus->trashed()) {
                    $rowsSkipped++;
                    continue;
                }
                $raw['wo_status_id'] = $woStatus->id;
            }
            unset($raw['wo_status']);

            // Sanitize repairs_made (only if string)
            if (isset($raw['repairs_made']) && is_string($raw['repairs_made'])) {
                $raw['repairs_made'] = iconv('UTF-8', 'UTF-8//IGNORE', $raw['repairs_made']);
                $raw['repairs_made'] = preg_replace('/[^\x20-\x7E]/', '', $raw['repairs_made']);
                $raw['repairs_made'] = str_replace(
                    ["\xe2\x80\x98", "\xe2\x80\x99", "\xe2\x80\x9c", "\xe2\x80\x9d"],
                    ["'", "'", '"', '"'],
                    $raw['repairs_made']
                );
            }

            /**
             * Process areas of concern (PIVOT)
             * - Build $areaIds
             * - REMOVE area_of_concerns from $raw BEFORE save
             * - sync AFTER save
             */
            $areaIds = [];
            if (isset($raw['area_of_concerns']) && !empty($raw['area_of_concerns'])) {
                $concernNames = array_map('trim', explode(',', (string) $raw['area_of_concerns']));
                foreach ($concernNames as $concernName) {
                    if ($concernName === '') continue;

                    $area = AreaOfConcern::withTrashed()->where('concern', $concernName)->first();
                    if ($area && !$area->trashed()) {
                        $areaIds[] = $area->id;
                    }
                }
            }
            unset($raw['area_of_concerns']); // ✅ critical

            // Tenant assignment
            if (!isset($raw['tenant_id'])) {
                if ($isSuperAdmin) {
                    // template import uses tenant_name
                    $tenantName = trim((string)($raw['tenant_name'] ?? ''));
                    if ($tenantName === '') {
                        $rowsSkipped++;
                        continue;
                    }
                    $tenant = Tenant::where('name', $tenantName)->first();
                    if (!$tenant) {
                        $rowsSkipped++;
                        continue;
                    }
                    $raw['tenant_id'] = $tenant->id;
                    unset($raw['tenant_name']);
                } else {
                    $raw['tenant_id'] = Auth::user()->tenant_id;
                }
            } else {
                // For quicksight we already set tenant_id; ensure tenant_name doesn't exist
                unset($raw['tenant_name']);
            }

            // Ensure empty strings -> null for nullable fields
            foreach (['ro_close_date', 'qs_invoice_date', 'wo_number', 'invoice', 'dispute_outcome', 'invoice_amount', 'repairs_made'] as $field) {
                if (array_key_exists($field, $raw) && $raw[$field] === '') {
                    $raw[$field] = null;
                }
            }

            // Validate row (note: no area_of_concerns here because it's pivot)
            $validator = Validator::make($raw, [
                'ro_number'        => 'nullable|string',
                'ro_open_date'     => 'required|date',
                'ro_close_date'    => 'nullable|date',
                'truck_id'         => 'required|exists:trucks,id',
                'vendor_id'        => 'required|exists:vendors,id',

                // nullable
                'wo_number'        => 'nullable|string',
                'wo_status_id'     => 'nullable|exists:wo_statuses,id',

                'invoice'          => 'nullable|string',
                'invoice_amount'   => 'nullable|numeric',
                'invoice_received' => 'required|boolean',
                'on_qs'            => 'required|in:yes,no,not expected',
                'qs_invoice_date'  => 'nullable|date',
                'disputed'         => 'required|boolean',
                'dispute_outcome'  => 'nullable|string',
                'tenant_id'        => 'required|exists:tenants,id',
                'repairs_made'     => 'nullable|string',
            ]);

            if ($validator->fails()) {
                $rowsSkipped++;
                continue;
            }

            // Persist
            if (($raw['ro_number'] ?? null) === '-') {
                $repairOrder = RepairOrder::create($raw);
            } else {
                $repairOrder = RepairOrder::updateOrCreate(
                    ['ro_number' => $raw['ro_number'], 'tenant_id' => $raw['tenant_id']],
                    $raw
                );
            }

            // Sync pivot AFTER save/update
            // If you only want to sync when the CSV provides areas, keep as-is.
            // If you want to clear existing areas when CSV has none, this already clears via sync([]).
            $repairOrder->areasOfConcern()->sync($areaIds);

            $rowsImported++;
        }

        fclose($handle);

        return redirect()->back()->with(
            'success',
            "{$rowsImported} rows imported or updated. {$rowsSkipped} skipped."
        );
    }

    /**
     * Export repair orders to a CSV file.
     */
    public function exportRepairOrders()
    {
        $repairOrders = RepairOrder::with([
            'truck',
            'vendor' => function ($query) {
                $query->withTrashed();
            },
            'areasOfConcern' => function ($query) {
                $query->withTrashed();
            },
            'woStatus' => function ($query) {
                $query->withTrashed();
            },
            'tenant'
        ])->get();

        if ($repairOrders->isEmpty()) {
            return redirect()->back()->with('error', 'No Data');
        }

        $fileName = 'repair_orders_' . Str::random(8) . '.csv';
        $filePath = public_path($fileName);
        $file = fopen($filePath, 'w');

        $headers = [
            'tenant_name',
            'ro_number',
            'ro_open_date',
            'ro_close_date',
            'truckid',
            'repairs_made',
            'vendor',
            'wo_number',
            'wo_status',
            'invoice',
            'invoice_amount',
            'invoice_received',
            'on_qs',
            'qs_invoice_date',
            'disputed',
            'dispute_outcome',
            'area_of_concerns',
        ];
        fputcsv($file, $headers);

        foreach ($repairOrders as $ro) {
            fputcsv($file, [
                $ro->tenant->name ?? '—',
                $ro->ro_number,
                !empty($ro->ro_open_date) ? Carbon::parse($ro->ro_open_date)->format('m/d/Y') : '',
                !empty($ro->ro_close_date) ? Carbon::parse($ro->ro_close_date)->format('m/d/Y') : '',
                $ro->truck?->truckid ?? '—',
                $ro->repairs_made,
                $ro->vendor
                    ? ($ro->vendor->trashed() ? $ro->vendor->vendor_name . ' (Deleted)' : $ro->vendor->vendor_name)
                    : '—',
                $ro->wo_number,
                $ro->woStatus
                    ? ($ro->woStatus->trashed() ? $ro->woStatus->name . ' (Deleted)' : $ro->woStatus->name)
                    : '—',
                $ro->invoice,
                $ro->invoice_amount,
                $ro->invoice_received ? 'Yes' : 'No',
                ucfirst($ro->on_qs),
                !empty($ro->qs_invoice_date) ? Carbon::parse($ro->qs_invoice_date)->format('m/d/Y') : '',
                $ro->disputed ? 'Yes' : 'No',
                $ro->dispute_outcome,
                implode(',', $ro->areasOfConcern->map(function ($area) {
                    return $area->trashed() ? $area->concern . ' (Deleted)' : $area->concern;
                })->toArray()),
            ]);
        }

        fclose($file);

        return Response::download($filePath)->deleteFileAfterSend(true);
    }
}
