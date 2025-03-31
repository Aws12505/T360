<?php

namespace App\Services\Driver;

use App\Models\Driver;
use App\Models\Tenant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DriverImportExportService
{
    /**
     * Import driver data from a CSV file.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception If the CSV file cannot be opened.
     */
    public function importData($request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');

        if (!$handle) {
            throw new \Exception('Could not open the file.');
        }

        $isSuperAdmin = Auth::user()->tenant_id === null;

        $expectedHeaders = $isSuperAdmin
            ? ['tenant_name', 'first_name', 'last_name', 'email', 'mobile_phone', 'hiring_date']
            : ['first_name', 'last_name', 'email', 'mobile_phone', 'hiring_date'];

        $headers = fgetcsv($handle, 0, ',');
        if ($headers === false) {
            fclose($handle);
            throw new \Exception('CSV file is empty.');
        }

        if (count($headers) !== count($expectedHeaders)) {
            fclose($handle);
            return redirect()->back()->with('error', 'CSV headers do not match the expected format.');
        }

        $rowsCreated = 0;
        $rowsUpdated = 0;
        $rowsSkipped = 0;
        $currentRow = 2;

        while (($row = fgetcsv($handle, 0, ',')) !== false) {
            if (count($row) !== count($expectedHeaders)) {
                $rowsSkipped++;
                $currentRow++;
                continue;
            }

            $data = array_combine($expectedHeaders, $row);
            $data = array_map(function ($val) {
                return $val === '' ? null : $val;
            }, $data);

            // Convert hiring_date from m/d/Y to Y-m-d (set time to start of day to avoid timezone issues)
            try {
                $data['hiring_date'] = Carbon::createFromFormat('m/d/Y', $data['hiring_date'])
                    ->startOfDay()
                    ->format('Y-m-d');
            } catch (\Exception $e) {
                $rowsSkipped++;
                $currentRow++;
                continue;
            }

            if (!$isSuperAdmin) {
                $data['tenant_id'] = Auth::user()->tenant_id;
            } else {
                $tenant = Tenant::where('name', $data['tenant_name'])->first();
                if (!$tenant) {
                    $rowsSkipped++;
                    $currentRow++;
                    continue;
                }
                $data['tenant_id'] = $tenant->id;
                unset($data['tenant_name']);
            }

            $validator = Validator::make($data, [
                'tenant_id'    => 'required|exists:tenants,id',
                'first_name'   => 'required|string',
                'last_name'    => 'required|string',
                'email'        => 'required|email',
                'mobile_phone' => 'required|string',
                'hiring_date'  => 'required|date',
            ]);

            if ($validator->fails()) {
                $rowsSkipped++;
                $currentRow++;
                continue;
            }

            $driver = Driver::where('email', $data['email'])->first();
            if ($driver) {
                $driver->update($data);
                $rowsUpdated++;
            } else {
                Driver::create($data);
                $rowsCreated++;
            }
            $currentRow++;
        }

        fclose($handle);

        $totalProcessed = $rowsCreated + $rowsUpdated;
        return redirect()->back()->with('success', "{$totalProcessed} rows processed: {$rowsCreated} created, {$rowsUpdated} updated, {$rowsSkipped} skipped.");
    }

    /**
     * Export driver data to a CSV file.
     */
    public function exportData()
    {
        $drivers = Driver::with('tenant')->get();
        if ($drivers->isEmpty()) {
            return redirect()->back()->with('error', 'No driver data to export.');
        }
        $fileName = 'drivers_' . Str::random(8) . '.csv';
        $filePath = public_path($fileName);
        $file = fopen($filePath, 'w');

        $headers = [
            'tenant_name',
            'first_name',
            'last_name',
            'email',
            'mobile_phone',
            'hiring_date',
        ];
        fputcsv($file, $headers);

        foreach ($drivers as $driver) {
            fputcsv($file, [
                $driver->tenant->name ?? '—',
                $driver->first_name,
                $driver->last_name,
                $driver->email,
                $driver->mobile_phone,
                $driver->hiring_date,
            ]);
        }
        fclose($file);
        return Response::download($filePath)->deleteFileAfterSend(true);
    }
}
