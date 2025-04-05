<?php

namespace App\Services\Maintenance;

use App\Models\RepairOrder;
use App\Models\Vendor;
use App\Models\AreaOfConcern;
use App\Models\Truck;
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
     * Expected CSV columns:
     * - SuperAdmin: tenant_name, ro_number, ro_open_date, ro_close_date, truckid, repairs_made, vendor,
     *   wo_number, wo_status, invoice, invoice_amount, invoice_received, on_qs, qs_invoice_date,
     *   disputed, dispute_outcome, area_of_concerns
     * - Non‑SuperAdmin: ro_number, ro_open_date, ro_close_date, truckid, repairs_made, vendor, wo_number,
     *   wo_status, invoice, invoice_amount, invoice_received, on_qs, qs_invoice_date, disputed,
     *   dispute_outcome, area_of_concerns
     *
     * Note: vendor and area_of_concerns are provided as names (or comma‑separated names),
     * and truckid is the unique truck identifier.
     *
     * dd() calls have been added at each major step to help debug issues in the CSV.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception If the CSV file cannot be opened.
     */
    public function importRepairOrders(Request $request)
    {
        // Validate the incoming request.
        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
        ]);

        $file = $request->file('file');
        $handle = fopen($file->getRealPath(), 'r');
        if (!$handle) {
            dd('Could not open the file.');
        }

        // Determine if the current user is a SuperAdmin.
        $isSuperAdmin = Auth::user()->tenant_id === null;

        // Define expected CSV headers.
        // Note: 'truckid' is used instead of 'truck_id', and vendor is passed as its name.
        // The repairs_made column is included after truckid.
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

        // Read (and ignore) the CSV header row.
        $headerRow = fgetcsv($handle, 0, ',');
        // dd('Header row from CSV:', $headerRow);
        if (count($headerRow) !== count($expectedHeaders)) {
            dd('Header row count mismatch', $headerRow, $expectedHeaders);
        }

        $rowsImported = 0;
        $rowsSkipped = 0;

        while (($row = fgetcsv($handle, 0, ',')) !== false) {
            if (count($row) !== count($expectedHeaders)) {
                dd('Row column count mismatch', $row, $expectedHeaders);
                $rowsSkipped++;
                continue;
            }

            // Combine expected headers with row values.
            $data = array_combine($expectedHeaders, $row);
            // dd('Combined row data:', $data);

            // Convert date fields from m/d/Y to Y-m-d.
            try {
                $data['ro_open_date'] = Carbon::createFromFormat('m/d/Y', $data['ro_open_date'])->format('Y-m-d');
            } catch (\Exception $e) {
                dd('Error converting ro_open_date', $data['ro_open_date'], $e);
                $rowsSkipped++;
                continue;
            }
            if (!empty($data['ro_close_date'])) {
                try {
                    $data['ro_close_date'] = Carbon::createFromFormat('m/d/Y', $data['ro_close_date'])->format('Y-m-d');
                } catch (\Exception $e) {
                    dd('Error converting ro_close_date', $data['ro_close_date'], $e);
                    $data['ro_close_date'] = null;
                }
            }
            if (!empty($data['qs_invoice_date'])) {
                try {
                    $data['qs_invoice_date'] = Carbon::createFromFormat('m/d/Y', $data['qs_invoice_date'])->format('Y-m-d');
                } catch (\Exception $e) {
                    dd('Error converting qs_invoice_date', $data['qs_invoice_date'], $e);
                    $data['qs_invoice_date'] = null;
                }
            }
            // dd('After date conversion:', $data);

            // Convert textual booleans to actual booleans.
            $data['invoice_received'] = strtolower(trim($data['invoice_received'])) === 'yes';
            $data['on_qs'] = strtolower(trim($data['on_qs'])) === 'yes';
            $data['disputed'] = strtolower(trim($data['disputed'])) === 'yes';
            // dd('After processing booleans:', $data);

            // Process truck: look up Truck by its unique 'truckid' field.
            $truck = Truck::where('truckid', $data['truckid'])->first();
            if (!$truck) {
                dd('Truck not found for truckid:', $data['truckid'], $row);
                $rowsSkipped++;
                continue;
            }
            $data['truck_id'] = $truck->id;
            unset($data['truckid']);
            // dd('After processing truck:', $data);

            // Process vendor: look up by vendor_name.
            $vendor = Vendor::where('vendor_name', $data['vendor'])->first();
            if (!$vendor) {
                dd('Vendor not found for vendor:', $data['vendor'], $row);
                $rowsSkipped++;
                continue;
            }
            $data['vendor_id'] = $vendor->id;
            unset($data['vendor']);
            // dd('After processing vendor:', $data);

            // Process area_of_concerns: convert concern names to their IDs.
            if (isset($data['area_of_concerns'])) {
                $concernNames = array_map('trim', explode(',', $data['area_of_concerns']));
                $concernIds = [];
                foreach ($concernNames as $concernName) {
                    $area = AreaOfConcern::where('concern', $concernName)->first();
                    if (!$area) {
                        dd('Area of Concern not found for name:', $concernName, $row);
                        // You could also choose to skip or continue without this concern.
                    }
                    $concernIds[] = $area->id;
                }
                $data['area_of_concerns'] = $concernIds;
            } else {
                $data['area_of_concerns'] = [];
            }
            // dd('After processing areas of concern:', $data);

            // Handle tenant assignment.
            if ($isSuperAdmin) {
                $tenant = Tenant::where('name', $data['tenant_name'])->first();
                if (!$tenant) {
                    dd('Tenant not found for tenant_name:', $data['tenant_name'], $row);
                    $rowsSkipped++;
                    continue;
                }
                $data['tenant_id'] = $tenant->id;
                unset($data['tenant_name']);
            } else {
                $data['tenant_id'] = Auth::user()->tenant_id;
            }
            // dd('After processing tenant:', $data);

            // Validate the row data.
            $validator = Validator::make($data, [
                'ro_number'        => 'required|string',
                'ro_open_date'     => 'required|date',
                'ro_close_date'    => 'nullable|date',
                'truck_id'         => 'required|exists:trucks,id',
                'vendor_id'        => 'required|exists:vendors,id',
                'wo_number'        => 'required|string',
                'wo_status'        => 'required|in:Completed,Canceled,Closed',
                'invoice'          => 'required|string',
                'invoice_amount'   => 'required|numeric',
                'invoice_received' => 'required|boolean',
                'on_qs'            => 'required|boolean',
                'qs_invoice_date'  => 'nullable|date',
                'disputed'         => 'required|boolean',
                'dispute_outcome'  => 'nullable|string',
                'tenant_id'        => 'required|exists:tenants,id',
                // Added repairs_made validation rule:
                'repairs_made'     => 'required|string',
            ]);
            if ($validator->fails()) {
                dd('Validation failed:', $validator->errors(), $data);
                $rowsSkipped++;
                continue;
            }
            // dd('After validation:', $data);

            // Update or create the repair order based on unique ro_number and tenant_id.
            $repairOrder = RepairOrder::updateOrCreate(
                ['ro_number' => $data['ro_number'], 'tenant_id' => $data['tenant_id']],
                $data
            );
            // dd('After updateOrCreate:', $repairOrder);

            // Sync areas of concern.
            if (!empty($data['area_of_concerns'])) {
                $repairOrder->areasOfConcern()->sync($data['area_of_concerns']);
            }
            // dd('After syncing areas of concern:', $data);

            $rowsImported++;
        }

        fclose($handle);
        return redirect()->back()->with('success', "$rowsImported rows imported or updated. $rowsSkipped skipped.");
    }

    /**
     * Export repair orders to a CSV file.
     *
     * The CSV will include:
     * - tenant_name, ro_number, ro_open_date, ro_close_date, truckid, repairs_made, vendor,
     *   wo_number, wo_status, invoice, invoice_amount, invoice_received, on_qs, qs_invoice_date,
     *   disputed, dispute_outcome, area_of_concerns
     *
     * Note: truckid and vendor are output as their human‑readable values, and area_of_concerns
     * is output as a comma‑separated list of concern names.
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     * @throws \Exception If there are no repair orders to export.
     */
    public function exportRepairOrders()
    {
        // Retrieve all repair orders with related data.
        $repairOrders = RepairOrder::with(['truck', 'vendor', 'areasOfConcern', 'tenant'])->get();

        if ($repairOrders->isEmpty()) {
            throw new \Exception('No repair order data to export.');
        }

        $fileName = 'repair_orders_' . Str::random(8) . '.csv';
        $filePath = public_path($fileName);
        $file = fopen($filePath, 'w');

        // Define the CSV header.
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
                // Output the Truck's unique truckid value.
                $ro->truck->truckid,
                // Output the repairs_made text.
                $ro->repairs_made,
                // Output the vendor's name.
                $ro->vendor->vendor_name,
                $ro->wo_number,
                $ro->wo_status,
                $ro->invoice,
                $ro->invoice_amount,
                $ro->invoice_received ? 'Yes' : 'No',
                $ro->on_qs ? 'Yes' : 'No',
                !empty($ro->qs_invoice_date) ? Carbon::parse($ro->qs_invoice_date)->format('m/d/Y') : '',
                $ro->disputed ? 'Yes' : 'No',
                $ro->dispute_outcome,
                // Output area_of_concerns as a comma-separated list of concern names.
                implode(',', $ro->areasOfConcern->pluck('concern')->toArray()),
            ]);
        }

        fclose($file);
        return Response::download($filePath)->deleteFileAfterSend(true);
    }
}
