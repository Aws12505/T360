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
            ? ['tenant_name', 'truck_id', 'type', 'make', 'fuel', 'license', 'vin', 'active']
            : ['truck_id', 'type', 'make', 'fuel', 'license', 'vin', 'active'];

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

        $rowsImported = 0;
        $rowsSkipped = 0;

        // Process each CSV row.
        while (($row = fgetcsv($handle, 0, ',')) !== false) {
            if (count($row) !== count($expectedHeaders)) {
                $rowsSkipped++;
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
                    continue;
                }
                $data['tenant_id'] = $tenant->id;
                unset($data['tenant_name']);
            }

            // Convert the active value to boolean.
            $data['active'] = filter_var($data['active'], FILTER_VALIDATE_BOOLEAN);

            // Validate the row data.
            $validator = Validator::make($data, [
                'tenant_id' => 'required|exists:tenants,id',
                'truck_id'  => 'required|integer',
                'type'      => 'required|in:daycab,sleepercab',
                'make'      => 'required|in:International,Kenworth,Peterbilt,Volvo,Freightliner',
                'fuel'      => 'required|in:cng,diesel',
                'license'   => 'required|integer',
                'vin'       => 'required|string|unique:trucks,vin',
                'active'    => 'required|boolean',
            ]);

            if ($validator->fails()) {
                $rowsSkipped++;
                continue;
            }

            // Create a new truck record.
            Truck::create($data);
            $rowsImported++;
        }

        fclose($handle);

        return redirect()->back()->with('success', "$rowsImported rows imported. $rowsSkipped rows skipped.");
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
            'truck_id',
            'type',
            'make',
            'fuel',
            'license',
            'vin',
            'active',
            'date',
        ];
        fputcsv($file, $headers);

        foreach ($trucks as $truck) {
            fputcsv($file, [
                $truck->tenant->name ?? '—',
                $truck->truck_id,
                $truck->type,
                $truck->make,
                $truck->fuel,
                $truck->license,
                $truck->vin,
                $truck->active ? 'Active' : 'Inactive',
                Carbon::parse($truck->created_at)->format('Y-m-d'),
            ]);
        }
        fclose($file);
        return Response::download($filePath)->deleteFileAfterSend(true);
    }
}
