<?php

namespace App\Services\On_Time;

use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Tenant;
use App\Models\Delay;
use App\Models\DelayCode;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;

class DelayImportExportService
{
    /**
     * Import delay records.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception If the CSV file cannot be opened.
     */
    public function importDelays($request)
    {
        // Validate the incoming request.
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
        ]);

        // Get the CSV file from the request.
        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');

        if (!$handle) {
            throw new \Exception('Could not open the file.');
        }

        // Determine if the current user is a SuperAdmin.
        $isSuperAdmin = Auth::user()->tenant_id === null;

        // Define expected CSV headers.
        $expectedHeaders = $isSuperAdmin
            ? [
                'tenant_name', 'date', 'delay_type', 'driver_name', 'delay_category', 
                'delay_code', 'disputed', 'driver_controllable'
            ]
            : [
                'date', 'delay_type', 'driver_name', 'delay_category', 
                'delay_code', 'disputed', 'driver_controllable'
            ];

        // Read header row from the CSV.
        $headers = fgetcsv($handle, 0, ',');
$validatortt=0;
        $rowsImported = 0;
        $rowsSkipped = 0;

        // Process each row in the CSV.
        while (($row = fgetcsv($handle, 0, ',')) !== false) {
            // Skip rows that don't have the expected number of columns.
            if (count($row) !== count($expectedHeaders)) {
                $rowsSkipped++;
                continue;
            }

            // Combine the expected headers with the CSV row data.
            $data = array_combine($expectedHeaders, $row);

            // Attempt to parse the date from m/d/Y to Y-m-d.
            try {
                $data['date'] = Carbon::createFromFormat('m/d/Y', $data['date'])->format('Y-m-d');
            } catch (\Exception $e) {
                
                $rowsSkipped++;
                continue;
            }

            // Convert the disputed and driver_controllable from text to boolean.
            $data['disputed'] = match (strtolower(trim($data['disputed']))) {
                'yes' => true,
                'no' => false,
                default => false,
            };

            if (strtolower(trim($data['driver_controllable'])) === 'n/a') {
                $data['driver_controllable'] = null;
            } else {
                $data['driver_controllable'] = match (strtolower(trim($data['driver_controllable']))) {
                    'yes' => true,
                    'no' => false,
                    default => null,
                };
            }

            // Clean the data: replace empty strings with null.
            $data = collect($data)->map(fn($val) => $val === '' ? null : $val)->toArray();

            // Set tenant_id: for non-SuperAdmin, use the authenticated user's tenant_id.
            if (!$isSuperAdmin) {
                $data['tenant_id'] = Auth::user()->tenant_id;
            } else {
                // For SuperAdmin, look up the tenant by name.
                $tenant = Tenant::where('name', $data['tenant_name'])->first();
                if (!$tenant) {
                    $rowsSkipped++;
                    continue;
                }
                $data['tenant_id'] = $tenant->id;
                unset($data['tenant_name']);
            }

            // Look up the delay code by code
            $delayCode = DelayCode::where('code', $data['delay_code'])->first();
            if (!$delayCode) {
                $rowsSkipped++;
                continue;
            }
            $data['delay_code_id'] = $delayCode->id;
            unset($data['delay_code']);

            // Calculate penalty based on delay_category
            $data['penalty'] = match ($data['delay_category']) {
                '1_120' => 1,
                '121_600' => 2,
                '601_plus' => 3,
                default => 0,
            };

            // Validate the row data.
            $validator = Validator::make($data, [
                'tenant_id' => 'required|exists:tenants,id',
                'date' => 'required|date',
                'delay_type' => 'required|in:origin,destination',
                'driver_name' => 'required|string',
                'delay_category' => 'required|in:1_120,121_600,601_plus',
                'delay_code_id' => 'required|exists:delay_codes,id',
                'disputed' => 'required|boolean',
                'driver_controllable' => 'nullable|boolean',
                'penalty' => 'required|integer',
            ]);

            if ($validator->fails()) {
                $rowsSkipped++;
                continue;
            }
            // Create a new delay record instead of updating existing ones
            Delay::create($data);

            $rowsImported++;
        }
        fclose($handle);
        // After processing all rows, you can return a response or redirect as neede
        // Return a redirect response with a success message.
        return redirect()->back()->with('success', "$rowsImported rows imported or updated. $rowsSkipped skipped.");
    }

    /**
     * Export delay records.
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\RedirectResponse
     */
    public function exportDelays()
    {
        // Retrieve all delay records along with their related tenant and delay code.
        $delays = Delay::with(['tenant', 'delayCode'])->get();

        // If there are no records, return a redirect with a message
        if ($delays->isEmpty()) {
            return redirect()->back()->with('error', 'No delay data found to export.');
        }

        // Generate a random filename for the CSV export.
        $fileName = 'delays_' . Str::random(8) . '.csv';
        $filePath = public_path($fileName);
        
        // Open a file pointer for writing CSV data.
        $file = fopen($filePath, 'w');

        // Define the header row for the CSV.
        $headers = [
            'tenant_name',
            'date',
            'delay_type',
            'driver_name',
            'delay_category',
            'penalty',
            'delay_code',
            'disputed',
            'driver_controllable'
        ];

        // Write the header row to the CSV file.
        fputcsv($file, $headers);

        // Loop through each delay record and write its data to the CSV.
        foreach ($delays as $delay) {
            // Format driver_controllable for CSV
            $driverControllable = $delay->driver_controllable === null 
                ? 'N/A' 
                : ($delay->driver_controllable ? 'Yes' : 'No');
                
            fputcsv($file, [
                // Use tenant name if available, or a dash if not.
                $delay->tenant->name ?? '—',
                // Format the date as m/d/Y.
                Carbon::parse($delay->date)->format('m/d/Y'),
                $delay->delay_type,
                $delay->driver_name,
                $delay->delay_category,
                $delay->penalty,
                $delay->delayCode->code ?? '—',
                // Convert boolean to human-readable format.
                $delay->disputed ? 'Yes' : 'No',
                $driverControllable
            ]);
        }

        // Close the CSV file pointer.
        fclose($file);

        // Return a response that downloads the file and deletes it after sending.
        return Response::download($filePath)->deleteFileAfterSend(true);
    }
}