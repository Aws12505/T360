<?php

namespace App\Services;

use App\Models\Performance;
use App\Models\PerformanceMetricRule;
use App\Models\Tenant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

/**
 * Class PerformanceService
 *
 * Contains business logic for performance records including rating calculations,
 * CRUD operations, and summary compilation.
 *
 * Created manually: touch app/Services/PerformanceService.php
 */
class PerformanceService
{
    /**
     * Retrieve performance records for the index view.
     *
     * @return array
     */
    public function getPerformanceIndex(): array
    {
        $performances = Performance::with('tenant')->get();
        $isSuperAdmin = is_null(Auth::user()->tenant_id);
        $tenantSlug = $isSuperAdmin ? null : Auth::user()->tenant->slug;
        $tenants = $isSuperAdmin ? Tenant::all() : [];
        return [
            'performances' => $performances,
            'tenantSlug'   => $tenantSlug,
            'SuperAdmin'   => $isSuperAdmin,
            'tenants'      => $tenants,
        ];
    }

    /**
     * Store a new performance record.
     *
     * @param array $data
     * @return void
     */
    public function storePerformance(array $data)
    {
        // Calculate the composite on_time value.
        $data['on_time'] = $data['on_time_to_origin'] == 0
            ? 0.5
            : ($data['on_time_to_origin'] * 0.375 + $data['on_time_to_destination'] * 0.625);

        $rule = PerformanceMetricRule::first();

        // Calculate ratings.
        $data['acceptance_rating'] = $this->getRating($data['acceptance'], $rule, 'acceptance');
        $data['on_time_rating'] = $this->getRating($data['on_time'], $rule, 'on_time');
        $data['maintenance_variance_to_spend_rating'] = $this->getRating($data['maintenance_variance_to_spend'], $rule, 'maintenance_variance');
        $data['open_boc_rating'] = $this->getRating($data['open_boc'], $rule, 'open_boc');
        $data['meets_safety_bonus_criteria_rating'] = $this->getSafetyBonusRating($data['meets_safety_bonus_criteria'], $rule);
        $data['vcr_preventable_rating'] = $this->getRating($data['vcr_preventable'], $rule, 'vcr_preventable');

        Performance::updateOrCreate(
            ['tenant_id' => $data['tenant_id'], 'date' => $data['date']],
            $data
        );
    }

    /**
     * Update an existing performance record.
     *
     * @param int $id
     * @param array $data
     * @return void
     */
    public function updatePerformance($id, array $data)
    {
        $data['on_time'] = $data['on_time_to_origin'] == 0
            ? 0.5
            : ($data['on_time_to_origin'] * 0.375 + $data['on_time_to_destination'] * 0.625);

        $rule = PerformanceMetricRule::first();
        $data['acceptance_rating'] = $this->getRating($data['acceptance'], $rule, 'acceptance');
        $data['on_time_rating'] = $this->getRating($data['on_time'], $rule, 'on_time');
        $data['maintenance_variance_to_spend_rating'] = $this->getRating($data['maintenance_variance_to_spend'], $rule, 'maintenance_variance');
        $data['open_boc_rating'] = $this->getRating($data['open_boc'], $rule, 'open_boc');
        $data['meets_safety_bonus_criteria_rating'] = $this->getSafetyBonusRating($data['meets_safety_bonus_criteria'], $rule);
        $data['vcr_preventable_rating'] = $this->getRating($data['vcr_preventable'], $rule, 'vcr_preventable');

        $performance = Performance::findOrFail($id);
        $performance->update($data);
    }

    /**
     * Delete a performance record.
     *
     * @param int $id
     * @param int|null $tenantId
     * @return void
     */
    public function deletePerformance($id, $tenantId = null)
    {
        $performance = Performance::findOrFail($id);
        if ($tenantId && $performance->tenant_id !== $tenantId) {
            abort(403, 'Unauthorized');
        }
        $performance->delete();
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
        $data['acceptance_rating'] = $this->getRating($data['acceptance'], $rule, 'acceptance');
        $data['on_time_rating'] = $this->getRating($data['on_time'], $rule, 'on_time');
        $data['maintenance_variance_to_spend_rating'] = $this->getRating($data['maintenance_variance_to_spend'], $rule, 'maintenance_variance');
        $data['open_boc_rating'] = $this->getRating($data['open_boc'], $rule, 'open_boc');
        $data['meets_safety_bonus_criteria_rating'] = $this->getSafetyBonusRating($data['meets_safety_bonus_criteria'], $rule);
        $data['vcr_preventable_rating'] = $this->getRating($data['vcr_preventable'], $rule, 'vcr_preventable');

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

    /**
     * Get the global performance metric rules.
     *
     * @return PerformanceMetricRule|null
     */
    public function getGlobalMetrics()
    {
        return PerformanceMetricRule::first();
    }

    /**
     * Update global performance metric rules.
     *
     * @param array $data
     * @return void
     */
    public function updateGlobalMetrics(array $data)
    {
        PerformanceMetricRule::updateOrCreate(['id' => 1], $data);
    }

    /**
     * Retrieve the validation rules for performance metrics.
     *
     * @return array
     */
    public function getMetricValidationRules(): array
    {
        $levels = ['fantastic_plus', 'fantastic', 'good', 'fair', 'poor'];
        $metrics = ['acceptance', 'on_time', 'maintenance_variance', 'open_boc', 'vcr_preventable'];
        $rules = [];
        foreach ($metrics as $metric) {
            foreach ($levels as $level) {
                $rules["{$metric}_{$level}"] = ['required', 'numeric'];
                $rules["{$metric}_{$level}_operator"] = ['required', 'in:less,less_or_equal,equal,more_or_equal,more'];
            }
        }
        $rules['safety_bonus_eligible_levels'] = ['nullable', 'array'];
        $rules['safety_bonus_eligible_levels.*'] = ['in:fantastic_plus,fantastic,good,fair,poor'];
        return $rules;
    }

    /**
     * Compare a value to a threshold using a specified operator.
     *
     * @param mixed $value
     * @param mixed $threshold
     * @param string $operator
     * @return bool
     */
    protected function compare($value, $threshold, $operator)
    {
        return match ($operator) {
            'less'          => $value < $threshold,
            'less_or_equal' => $value <= $threshold,
            'equal'         => $value == $threshold,
            'more_or_equal' => $value >= $threshold,
            'more'          => $value > $threshold,
            default         => false,
        };
    }

    /**
     * Calculate a rating based on a given value and metric rule.
     *
     * @param mixed $value
     * @param PerformanceMetricRule $rule
     * @param string $prefix
     * @return string|null
     */
    protected function getRating($value, $rule, $prefix)
    {
        $levels = ['fantastic_plus', 'fantastic', 'good', 'fair', 'poor'];
        foreach ($levels as $level) {
            $threshold = $rule["{$prefix}_{$level}"];
            $operator = $rule["{$prefix}_{$level}_operator"];
            if ($this->compare($value, $threshold, $operator)) {
                return $level;
            }
        }
        return null;
    }

    /**
     * Determine the safety bonus rating.
     *
     * @param mixed $isEligible
     * @param PerformanceMetricRule $rule
     * @return string
     */
    private function getSafetyBonusRating($isEligible, $rule): string
    {
        $levels = ['fantastic_plus', 'fantastic', 'good', 'fair', 'poor'];
        $eligibleLevels = $rule->safety_bonus_eligible_levels ?? [];
        return $isEligible
            ? (collect($levels)->first(fn($level) => in_array($level, $eligibleLevels)) ?? 'poor')
            : 'poor';
    }

    /**
     * Compile performance summaries for various time ranges.
     *
     * @return array
     */
    public function compileSummaries(): array
    {
        $today = Carbon::now();
        $currentWeekStart = $today->copy()->startOfWeek(Carbon::SUNDAY);
        $currentWeekEnd = $today->copy()->endOfWeek(Carbon::SATURDAY);
        $rollingStart = $currentWeekStart->copy()->subWeeks(5);
        $rollingEnd = $currentWeekEnd;

        $summaries = [
            'yesterday'      => $this->fetchMetrics(Carbon::yesterday()->startOfDay(), Carbon::yesterday()->endOfDay(), 'yesterday'),
            'current_week'   => $this->fetchMetrics($currentWeekStart->copy()->startOfDay(), $currentWeekEnd->copy()->endOfDay(), 'current_week'),
            'rolling_6_weeks'=> $this->fetchMetrics($rollingStart->copy()->startOfDay(), $rollingEnd->copy()->endOfDay(), 'rolling_6_weeks'),
            'quarterly'      => $this->fetchMetrics($today->copy()->subMonths(3)->startOfDay(), $today->copy()->endOfDay(), 'quarterly'),
        ];

        $isSuperAdmin = is_null(Auth::user()->tenant_id);
        $tenantSlug = $isSuperAdmin ? null : Auth::user()->tenant->slug;
        $tenants = $isSuperAdmin ? Tenant::all() : [];

        return [
            'summaries'  => $summaries,
            'tenantSlug' => $tenantSlug,
            'SuperAdmin' => $isSuperAdmin,
            'tenants'    => $tenants,
        ];
    }

    /**
     * Fetch metrics for a specific date range.
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param string $label
     * @return array
     */
    protected function fetchMetrics($startDate, $endDate, $label = '')
    {
        $rule = PerformanceMetricRule::first();
        $rollingStart = Carbon::now()->subWeeks(6)->startOfWeek();
        $rollingEnd = Carbon::now();

        $mainData = DB::table('performances')
            ->selectRaw("AVG(acceptance) AS average_acceptance, AVG(on_time) AS average_on_time, AVG(maintenance_variance_to_spend) AS average_maintenance_variance_to_spend, CASE WHEN SUM(meets_safety_bonus_criteria) >= COUNT(meets_safety_bonus_criteria) / 2 THEN 1 ELSE 0 END AS meets_safety_bonus_criteria")
            ->whereBetween('date', [$startDate, $endDate])
            ->first();

        $rollingData = DB::table('performances')
            ->selectRaw("SUM(open_boc) AS sum_open_boc, SUM(vcr_preventable) AS sum_vcr_preventable")
            ->whereBetween('date', [$rollingStart, $rollingEnd])
            ->first();

        return [
            'label'      => $label,
            'start_date' => $startDate->toDateString(),
            'end_date'   => $endDate->toDateString(),
            'data'       => [
                'average_acceptance' => $mainData->average_acceptance ?? 0,
                'average_on_time'    => $mainData->average_on_time ?? 0,
                'average_maintenance_variance_to_spend' => $mainData->average_maintenance_variance_to_spend ?? 0,
                'open_boc'           => $rollingData->sum_open_boc ?? 0,
                'vcr_preventable'    => $rollingData->sum_vcr_preventable ?? 0,
                'meets_safety_bonus_criteria' => $mainData->meets_safety_bonus_criteria ?? 0,
            ],
            'ratings'    => [
                'average_acceptance' => $this->getRating($mainData->average_acceptance, $rule, 'acceptance'),
                'average_on_time'    => $this->getRating($mainData->average_on_time, $rule, 'on_time'),
                'average_maintenance_variance_to_spend' => $this->getRating($mainData->average_maintenance_variance_to_spend, $rule, 'maintenance_variance'),
                'open_boc'           => $this->getRating($rollingData->sum_open_boc, $rule, 'open_boc'),
                'vcr_preventable'    => $this->getRating($rollingData->sum_vcr_preventable, $rule, 'vcr_preventable'),
                'meets_safety_bonus_criteria' => $this->getSafetyBonusRating($mainData->meets_safety_bonus_criteria, $rule),
            ],
        ];
    }
}
