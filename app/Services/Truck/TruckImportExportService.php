<?php

namespace App\Services\Truck;

use App\Models\Truck;
use App\Models\Tenant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TruckImportExportService
{
    /**
     * Import truck data from a CSV file.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception If the CSV file cannot be opened.
     */
    public function importData($request)
    {
        // Validate the incoming request.
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
        ]);

        // Get the CSV file.
        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');

        if (!$handle) {
            throw new \Exception('Could not open the file.');
        }

        // Determine if the current user is a SuperAdmin.
        $isSuperAdmin = Auth::user()->tenant_id === null;

        // Define expected CSV headers.
        // SuperAdmin users must include tenant_name in the CSV.
        $expectedHeaders = $isSuperAdmin
            ? ['tenant_name', 'truckid', 'type', 'make', 'fuel', 'license', 'vin', 'status', 'inspection_status', 'inspection_expiry_date']
            : ['truckid', 'type', 'make', 'fuel', 'license', 'vin', 'status', 'inspection_status', 'inspection_expiry_date'];

        // Read the header row.
        $headers = fgetcsv($handle, 0, ',');
        if ($headers === false) {
            fclose($handle);
            throw new \Exception('CSV file is empty.');
        }

        // Check if the CSV header count matches expected.
        if (count($headers) !== count($expectedHeaders)) {
            fclose($handle);
            return redirect()->back()->with('error', 'CSV headers do not match the expected format.');
        }

        $rowsCreated = 0;
        $rowsUpdated = 0;
        $rowsSkipped = 0;

        // Start row counter at 2 (assuming row 1 is the header).
        $currentRow = 2;

        // Process each CSV row.
        while (($row = fgetcsv($handle, 0, ',')) !== false) {
            if (count($row) !== count($expectedHeaders)) {
                $rowsSkipped++;
                $currentRow++;
                continue;
            }

            // Combine headers with row data.
            $data = array_combine($expectedHeaders, $row);

            // Clean the data: replace empty strings with null.
            $data = array_map(function ($val) {
                return $val === '' ? null : $val;
            }, $data);

            // For non-SuperAdmin, set tenant_id from the authenticated user.
            if (!$isSuperAdmin) {
                $data['tenant_id'] = Auth::user()->tenant_id;
            } else {
                // For SuperAdmin, lookup tenant by tenant_name.
                $tenant = Tenant::where('name', $data['tenant_name'])->first();
                if (!$tenant) {
                    $rowsSkipped++;
                    $currentRow++;
                    continue;
                }
                $data['tenant_id'] = $tenant->id;
                unset($data['tenant_name']);
            }

            // Convert the type, fuel, and make values to lowercase.
            $data['type'] = strtolower($data['type']);
            $data['fuel'] = strtolower($data['fuel']);
            $data['make'] = strtolower($data['make']);

            // Process status value - ensure it's one of the allowed values
            $statusVal = trim($data['status']);
            if (!in_array($statusVal, ['active', 'inactive', 'Returned to AMZ'])) {
                $data['status'] = 'active'; // Default to active if invalid
            }
            
            // Process inspection status - convert to lowercase and validate
            $data['inspection_status'] = strtolower(trim($data['inspection_status']));
            
            // Format inspection expiry date if provided
            if (!empty($data['inspection_expiry_date'])) {
                try {
                    $data['inspection_expiry_date'] = Carbon::parse($data['inspection_expiry_date'])->format('Y-m-d');
                } catch (\Exception $e) {
                    // If date parsing fails, set to null
                    $data['inspection_expiry_date'] = null;
                }
            }

            // Validate the row data.
            $validator = Validator::make($data, [
                'tenant_id' => 'required|exists:tenants,id',
                'truckid'   => 'required|integer',
                'type'      => 'required|in:daycab,sleepercab',
                'make'      => 'required|in:international,kenworth,peterbilt,volvo,freightliner',
                'fuel'      => 'required|in:cng,diesel',
                'license'   => 'required|integer',
                'vin'       => 'required|string',
                'status'    => 'required|in:active,inactive,Returned to AMZ',
                'inspection_status' => 'required|in:good,expired',
                'inspection_expiry_date' => 'required|date',
            ]);

            if ($validator->fails()) {
                $rowsSkipped++;
                $currentRow++;
                continue;
            }

            // Check if a truck with this VIN already exists.
            $truck = Truck::where('vin', $data['vin'])->first();
            if ($truck) {
                // Update the existing truck.
                $truck->update($data);
                $rowsUpdated++;
            } else {
                // Create a new truck record.
                Truck::create($data);
                $rowsCreated++;
            }
            $currentRow++;
        }

        fclose($handle);

        $totalProcessed = $rowsCreated + $rowsUpdated;
        return redirect()->back()->with('success', "{$totalProcessed} rows processed: {$rowsCreated} created, {$rowsUpdated} updated, {$rowsSkipped} skipped.");
    }

    /**
     * Export truck data to a CSV file.
     */
    public function exportData()
    {
        $trucks = Truck::with('tenant')->get();
        if ($trucks->isEmpty()) {
            return redirect()->back()->with('error', 'No truck data to export.');
        }
        $fileName = 'trucks_' . Str::random(8) . '.csv';
        $filePath = public_path($fileName);
        $file = fopen($filePath, 'w');

        // Define headers.
        $headers = [
            'tenant_name',
            'truckid',
            'type',
            'make',
            'fuel',
            'license',
            'vin',
            'status',
            'inspection_status',
            'inspection_expiry_date',
        ];
        fputcsv($file, $headers);

        foreach ($trucks as $truck) {
            fputcsv($file, [
                $truck->tenant->name ?? '—',
                $truck->truckid,
                $truck->type,
                $truck->make,
                $truck->fuel,
                $truck->license,
                $truck->vin,
                $truck->status,
                $truck->inspection_status ?? 'good',
                $truck->inspection_expiry_date ?? '',
            ]);
        }
        fclose($file);
        return Response::download($filePath)->deleteFileAfterSend(true);
    }
}
