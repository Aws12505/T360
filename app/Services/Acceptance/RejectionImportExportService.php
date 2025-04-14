<?php

namespace App\Services\Acceptance;

use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Tenant;
use App\Models\Rejection;
use App\Models\RejectionReasonCode;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;

class RejectionImportExportService
{
    /**
     * Import rejection records.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception If the CSV file cannot be opened.
     */
    public function importRejections($request)
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
                'tenant_name', 'date', 'rejection_type', 'driver_name', 'rejection_category', 
                'reason_code', 'disputed', 'driver_controllable'
            ]
            : [
                'date', 'rejection_type', 'driver_name', 'rejection_category', 
                'reason_code', 'disputed', 'driver_controllable'
            ];

        // Read header row from the CSV.
        $headers = fgetcsv($handle, 0, ',');

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

            // Look up the reason code by code
            $reasonCode = RejectionReasonCode::where('reason_code', $data['reason_code'])->first();
            if (!$reasonCode) {
                
                $rowsSkipped++;
                continue;
            }
            $data['reason_code_id'] = $reasonCode->id;
            unset($data['reason_code']);

            // Calculate penalty based on rejection_category
            $data['penalty'] = match ($data['rejection_category']) {
                'more_than_6' => 1,
                'within_6'    => 4,
                'after_start' => 8,
            };

            // Validate the row data.
            $validator = Validator::make($data, [
                'tenant_id' => 'required|exists:tenants,id',
                'date' => 'required|date',
                'rejection_type' => 'required|in:block,load',
                'driver_name' => 'required|string',
                'rejection_category' => 'required|in:more_than_6,within_6,after_start',
                'reason_code_id' => 'required|exists:rejection_reason_codes,id',
                'disputed' => 'required|boolean',
                'driver_controllable' => 'nullable|boolean',
                'penalty' => 'required|integer',
            ]);

            if ($validator->fails()) {
              
                $rowsSkipped++;
                continue;
            }

            // Create a new rejection record
            Rejection::create($data);

            $rowsImported++;
        }
        fclose($handle);
        
              
        // Return a redirect response with a success message.
        return redirect()->back()->with('success', "$rowsImported rows imported. $rowsSkipped skipped.");
    }

    /**
     * Export rejection records.
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     * @throws \Exception If there are no rejection records to export.
     */
    public function exportRejections()
    {
        // Retrieve all rejection records along with their related tenant and reason code.
        $rejections = Rejection::with(['tenant', 'reasonCode'])->get();

        // If there are no records, throw an exception or handle accordingly.
        if ($rejections->isEmpty()) {
            throw new \Exception('No rejection data to export.');
        }

        // Generate a random filename for the CSV export.
        $fileName = 'rejections_' . Str::random(8) . '.csv';
        $filePath = public_path($fileName);
        
        // Open a file pointer for writing CSV data.
        $file = fopen($filePath, 'w');

        // Define the header row for the CSV.
        $headers = [
            'tenant_name',
            'date',
            'rejection_type',
            'driver_name',
            'rejection_category',
            'penalty',
            'reason_code',
            'disputed',
            'driver_controllable'
        ];

        // Write the header row to the CSV file.
        fputcsv($file, $headers);

        // Loop through each rejection record and write its data to the CSV.
        foreach ($rejections as $rejection) {
            // Format driver_controllable for CSV
            $driverControllable = $rejection->driver_controllable === null 
                ? 'N/A' 
                : ($rejection->driver_controllable ? 'Yes' : 'No');
                
            fputcsv($file, [
                // Use tenant name if available, or a dash if not.
                $rejection->tenant->name ?? '—',
                // Format the date as m/d/Y.
                Carbon::parse($rejection->date)->format('m/d/Y'),
                $rejection->rejection_type,
                $rejection->driver_name,
                $rejection->rejection_category,
                $rejection->penalty,
                $rejection->reasonCode->reason_code ?? '—',
                // Convert boolean to human-readable format.
                $rejection->disputed ? 'Yes' : 'No',
                $driverControllable
            ]);
        }

        // Close the CSV file pointer.
        fclose($file);

        // Return a response that downloads the file and deletes it after sending.
        return Response::download($filePath)->deleteFileAfterSend(true);
    }
}