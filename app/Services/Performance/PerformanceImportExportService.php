<?php

namespace App\Services\Performance;

use Illuminate\Support\Facades\Auth;
use App\Models\PerformanceMetricRule;
use Carbon\Carbon;
use App\Models\Tenant;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use App\Models\Performance;
use App\Services\Performance\PerformanceCalculationsService;

class PerformanceImportExportService{
 
    protected PerformanceCalculationsService $performanceCalculationsService;

    /**
     * Constructor.
     *
     * @param PerformanceCalculationsService $performanceCalculationsService Service for performance operations.
     */
    public function __construct(PerformanceCalculationsService $performanceCalculationsService)
    {
        $this->performanceCalculationsService = $performanceCalculationsService;
    }

    /**
 * Import performance records.
 *
 * @param \Illuminate\Http\Request $request
 * @return \Illuminate\Http\RedirectResponse
 * @throws \Exception If the CSV file cannot be opened.
 */
public function importPerformances($request)
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
            'tenant_name', 'date', 'acceptance', 'on_time_to_origin', 'on_time_to_destination',
            'maintenance_variance_to_spend', 'open_boc', 'meets_safety_bonus_criteria', 'vcr_preventable'
        ]
        : [
            'date', 'acceptance', 'on_time_to_origin', 'on_time_to_destination',
            'maintenance_variance_to_spend', 'open_boc', 'meets_safety_bonus_criteria', 'vcr_preventable'
        ];

    // Read header row from the CSV.
    $headers = fgetcsv($handle, 0, ',');

    $rowsImported = 0;
    $rowsSkipped = 0;
    $rule = PerformanceMetricRule::first();

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

        // Convert the safety bonus eligibility from text to boolean.
        $data['meets_safety_bonus_criteria'] = match (strtolower(trim($data['meets_safety_bonus_criteria']))) {
            'yes' => true,
            'no' => false,
            default => false,
        };

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

        // Validate the row data.
        $validator = Validator::make($data, [
            'tenant_id' => 'required|exists:tenants,id',
            'date' => 'required|date',
            'acceptance' => 'required|numeric',
            'on_time_to_origin' => 'required|numeric',
            'on_time_to_destination' => 'required|numeric',
            'maintenance_variance_to_spend' => 'required|numeric',
            'open_boc' => 'required|integer',
            'meets_safety_bonus_criteria' => 'required|boolean',
            'vcr_preventable' => 'required|integer',
        ]);

        if ($validator->fails()) {
            $rowsSkipped++;
            continue;
        }

        // Calculate the composite on_time value.
        $data['on_time'] = $data['on_time_to_origin'] == 0
            ? 0.5
            : ($data['on_time_to_origin'] * 0.375 + $data['on_time_to_destination'] * 0.625);

        // Calculate ratings for each metric.
        $data['acceptance_rating'] = $this->performanceCalculationsService->getRating($data['acceptance'], $rule, 'acceptance');
        $data['on_time_rating'] = $this->performanceCalculationsService->getRating($data['on_time'], $rule, 'on_time');
        $data['maintenance_variance_to_spend_rating'] = $this->performanceCalculationsService->getRating($data['maintenance_variance_to_spend'], $rule, 'maintenance_variance');
        $data['open_boc_rating'] = $this->performanceCalculationsService->getRating($data['open_boc'], $rule, 'open_boc');
        $data['meets_safety_bonus_criteria_rating'] = $this->performanceCalculationsService->getSafetyBonusRating($data['meets_safety_bonus_criteria'], $rule);
        $data['vcr_preventable_rating'] = $this->performanceCalculationsService->getRating($data['vcr_preventable'], $rule, 'vcr_preventable');

        // Update or create the performance record based on tenant_id and date.
        Performance::updateOrCreate(
            ['tenant_id' => $data['tenant_id'], 'date' => $data['date']],
            $data
        );

        $rowsImported++;
    }

    fclose($handle);

    // Return a redirect response with a success message.
    return redirect()->back()->with('success', "$rowsImported rows imported or updated. $rowsSkipped skipped.");
}

    /**
 * Export performance records.
 *
 * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
 * @throws \Exception If there are no performance records to export.
 */
public function exportPerformances()
{
    // Retrieve all performance records along with their related tenant.
    $performances = Performance::with('tenant')->get();

    // If there are no records, throw an exception or handle accordingly.
    if ($performances->isEmpty()) {
        throw new \Exception('No performance data to export.');
    }

    // Generate a random filename for the CSV export.
    $fileName = 'performances_' . Str::random(8) . '.csv';
    $filePath = public_path($fileName);
    
    // Open a file pointer for writing CSV data.
    $file = fopen($filePath, 'w');

    // Define the header row for the CSV.
    $headers = [
        'tenant_name',
        'date',
        'acceptance',
        'acceptance_rating',
        'on_time_to_origin',
        'on_time_to_destination',
        'on_time',
        'on_time_rating',
        'maintenance_variance_to_spend',
        'maintenance_variance_to_spend_rating',
        'open_boc',
        'open_boc_rating',
        'meets_safety_bonus_criteria',
        'meets_safety_bonus_criteria_rating',
        'vcr_preventable',
        'vcr_preventable_rating',
    ];

    // Write the header row to the CSV file.
    fputcsv($file, $headers);

    // Loop through each performance record and write its data to the CSV.
    foreach ($performances as $p) {
        fputcsv($file, [
            // Use tenant name if available, or a dash if not.
            $p->tenant->name ?? '—',
            // Format the date as m/d/Y.
            Carbon::parse($p->date)->format('m/d/Y'),
            $p->acceptance,
            $p->acceptance_rating,
            $p->on_time_to_origin,
            $p->on_time_to_destination,
            $p->on_time,
            $p->on_time_rating,
            $p->maintenance_variance_to_spend,
            $p->maintenance_variance_to_spend_rating,
            $p->open_boc,
            $p->open_boc_rating,
            // Convert boolean to human-readable format.
            $p->meets_safety_bonus_criteria ? 'Yes' : 'No',
            $p->meets_safety_bonus_criteria_rating,
            $p->vcr_preventable,
            $p->vcr_preventable_rating,
        ]);
    }

    // Close the CSV file pointer.
    fclose($file);

    // Return a response that downloads the file and deletes it after sending.
    return Response::download($filePath)->deleteFileAfterSend(true);
}

}