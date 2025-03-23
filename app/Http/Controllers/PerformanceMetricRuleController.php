<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\PerformanceMetricRule;
use App\Models\Tenant;
use Illuminate\Support\Facades\Auth;
use App\Models\PerformanceMetric;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class PerformanceMetricRuleController extends Controller
{
    public function edit()
    {
        // Get rule through tenant relation (no ID needed)
        $rule = Auth::user()->tenant->performanceMetricRule;
        if (is_null(Auth::user()->tenant_id)) {
            $tenantSlug = null;
        } else {
            $tenantSlug = Auth::user()->tenant->slug;
        }
        return Inertia::render('PerformanceRules/Edit', [
            'rule' => $rule,
            'tenantSlug' => $tenantSlug,
            
        ]);
    }

    public function update(Request $request)
{
    $tenant = Auth::user()->tenant;
    $rule = $tenant->performanceMetricRule;

    $validated = $request->validateData($request);

    if ($rule) {
        $rule->update($validated);
    } else {
        $validated['tenant_id'] = $tenant->id;
        PerformanceMetricRule::create($validated);
    }

    return redirect()->back()->with('success', 'Metrics saved successfully.');
}

public function index()
{
    if (is_null(Auth::user()->tenant_id)) {
        $tenantSlug = null;
    } else {
        $tenantSlug = Auth::user()->tenant->slug;
    }
    $tenants = Tenant::all(['id', 'name']); // Pass to frontend for dropdown in form
    $metrics = PerformanceMetricRule::orderBy('id', 'desc')->paginate(10)->withQueryString();    return Inertia::render('PerformanceRules/Admin', [
        'metrics' => $metrics,
        'tenantSlug' => $tenantSlug,
        'tenants' => $tenants,
    ]);
}

public function store(Request $request)
{
    $validated = $this->validateData($request);
    PerformanceMetricRule::create($validated);
    return redirect()->back()->with('success', 'Entry created successfully!');
}

public function updateAdmin(Request $request, $id)
{
    $metric = PerformanceMetricRule::findOrFail($id);
    $validated = $this->validateData($request);
    $metric->update($validated);
    return redirect()->back()->with('success', 'Entry updated successfully!');
}


public function export()
{
    $data = PerformanceMetricRule::with('tenant')->get();

    if ($data->isEmpty()) {
        return back()->with('error', 'No data to export.');
    }

    $model = new PerformanceMetricRule();
    $fillable = $model->getFillable();

    // Replace tenant_id with tenant_name in export
    $headers = ['tenant_name', ...array_filter($fillable, fn($f) => $f !== 'tenant_id')];

    $fileName = 'performance_metrics_' . Str::random(8) . '.csv';
    $filePath = public_path($fileName);

    $file = fopen($filePath, 'w');
    fputcsv($file, $headers);

    foreach ($data as $row) {
        $values = $row->only($fillable);
        unset($values['tenant_id']); // remove tenant_id

        $values = [
            'tenant_name' => $row->tenant->name ?? '—',
            ...$values
        ];

        $flatRow = array_map(function ($value) {
            return is_array($value) ? json_encode($value) : $value;
        }, $values);

        fputcsv($file, $flatRow);
    }

    fclose($file);

    return Response::download($filePath)->deleteFileAfterSend(true);
}



public function import(Request $request)
{
    $request->validate([
        'csv_file' => 'required|file|mimes:csv,txt',
    ]);

    $file = $request->file('csv_file');
    $handle = fopen($file->getRealPath(), 'r');

    if (!$handle) {
        return back()->with('error', 'Could not open the file.');
    }

    $headers = fgetcsv($handle, 0, ',');
    $rowsImported = 0;
    $rowsSkipped = 0;

    while (($row = fgetcsv($handle, 0, ',')) !== false) {
        if (count($row) !== count($headers)) {
            $rowsSkipped++;
            continue; // skip malformed rows
        }

        $data = array_combine($headers, $row);

        // Resolve tenant_name → tenant_id
        $tenantName = trim($data['tenant_name'] ?? '');
        $tenant = Tenant::where('name', $tenantName)->first();

        if (!$tenant) {
            $rowsSkipped++;
            continue;
        }

        $data['tenant_id'] = $tenant->id;
        unset($data['tenant_name']);

        // Convert safety_bonus_eligible_levels to array if it's a stringified array
        if (!empty($data['safety_bonus_eligible_levels']) && is_string($data['safety_bonus_eligible_levels'])) {
            $decoded = json_decode($data['safety_bonus_eligible_levels'], true);
            $data['safety_bonus_eligible_levels'] = json_last_error() === JSON_ERROR_NONE ? $decoded : [];
        }

        // Fix empty strings
        $data = collect($data)->map(fn($val) => $val === '' ? null : $val)->toArray();

        // Validate the row using Laravel Validator (not Request)
        $validator = Validator::make($data, $this->validationRules());

        if ($validator->fails()) {
            $rowsSkipped++;
            continue;
        }

        // Save or update entry for that tenant
        PerformanceMetricRule::updateOrCreate(
            ['tenant_id' => $data['tenant_id']],
            $data
        );

        $rowsImported++;
    }

    fclose($handle);

    return back()->with('success', "$rowsImported rows imported. $rowsSkipped skipped.");
}

protected function validateData(Request $request): array
{
    return $request->validate($this->validationRules());
}

protected function validationRules(): array
{
    return [
        'acceptance_fantastic_plus' => ['required', 'numeric'],
        'acceptance_fantastic_plus_operator' => ['required', 'in:less,less_or_equal,equal,more_or_equal,more'],
        'acceptance_fantastic' => ['required', 'numeric'],
        'acceptance_fantastic_operator' => ['required', 'in:less,less_or_equal,equal,more_or_equal,more'],
        'acceptance_good' => ['required', 'numeric'],
        'acceptance_good_operator' => ['required', 'in:less,less_or_equal,equal,more_or_equal,more'],
        'acceptance_fair' => ['required', 'numeric'],
        'acceptance_fair_operator' => ['required', 'in:less,less_or_equal,equal,more_or_equal,more'],
        'acceptance_poor' => ['required', 'numeric'],
        'acceptance_poor_operator' => ['required', 'in:less,less_or_equal,equal,more_or_equal,more'],

        // On-Time
        'on_time_fantastic_plus' => ['required', 'numeric'],
        'on_time_fantastic_plus_operator' => ['required', 'in:less,less_or_equal,equal,more_or_equal,more'],
        'on_time_fantastic' => ['required', 'numeric'],
        'on_time_fantastic_operator' => ['required', 'in:less,less_or_equal,equal,more_or_equal,more'],
        'on_time_good' => ['required', 'numeric'],
        'on_time_good_operator' => ['required', 'in:less,less_or_equal,equal,more_or_equal,more'],
        'on_time_fair' => ['required', 'numeric'],
        'on_time_fair_operator' => ['required', 'in:less,less_or_equal,equal,more_or_equal,more'],
        'on_time_poor' => ['required', 'numeric'],
        'on_time_poor_operator' => ['required', 'in:less,less_or_equal,equal,more_or_equal,more'],

        // Maintenance Variance
        'maintenance_variance_fantastic_plus' => ['required', 'numeric'],
        'maintenance_variance_fantastic_plus_operator' => ['required', 'in:less,less_or_equal,equal,more_or_equal,more'],
        'maintenance_variance_fantastic' => ['required', 'numeric'],
        'maintenance_variance_fantastic_operator' => ['required', 'in:less,less_or_equal,equal,more_or_equal,more'],
        'maintenance_variance_good' => ['required', 'numeric'],
        'maintenance_variance_good_operator' => ['required', 'in:less,less_or_equal,equal,more_or_equal,more'],
        'maintenance_variance_fair' => ['required', 'numeric'],
        'maintenance_variance_fair_operator' => ['required', 'in:less,less_or_equal,equal,more_or_equal,more'],
        'maintenance_variance_poor' => ['required', 'numeric'],
        'maintenance_variance_poor_operator' => ['required', 'in:less,less_or_equal,equal,more_or_equal,more'],

        // Open BOC
        'open_boc_fantastic_plus' => ['required', 'integer'],
        'open_boc_fantastic_plus_operator' => ['required', 'in:less,less_or_equal,equal,more_or_equal,more'],
        'open_boc_fantastic' => ['required', 'integer'],
        'open_boc_fantastic_operator' => ['required', 'in:less,less_or_equal,equal,more_or_equal,more'],
        'open_boc_good' => ['required', 'integer'],
        'open_boc_good_operator' => ['required', 'in:less,less_or_equal,equal,more_or_equal,more'],
        'open_boc_fair' => ['required', 'integer'],
        'open_boc_fair_operator' => ['required', 'in:less,less_or_equal,equal,more_or_equal,more'],
        'open_boc_poor' => ['required', 'integer'],
        'open_boc_poor_operator' => ['required', 'in:less,less_or_equal,equal,more_or_equal,more'],

        // Safety Bonus Criteria
        'safety_bonus_eligible_levels' => ['nullable', 'array'],
        'safety_bonus_eligible_levels.*' => ['in:fantastic_plus,fantastic,good,fair,poor'],

        // VCR Preventable
        'vcr_preventable_fantastic_plus' => ['required', 'integer'],
        'vcr_preventable_fantastic_plus_operator' => ['required', 'in:less,less_or_equal,equal,more_or_equal,more'],
        'vcr_preventable_fantastic' => ['required', 'integer'],
        'vcr_preventable_fantastic_operator' => ['required', 'in:less,less_or_equal,equal,more_or_equal,more'],
        'vcr_preventable_good' => ['required', 'integer'],
        'vcr_preventable_good_operator' => ['required', 'in:less,less_or_equal,equal,more_or_equal,more'],
        'vcr_preventable_fair' => ['required', 'integer'],
        'vcr_preventable_fair_operator' => ['required', 'in:less,less_or_equal,equal,more_or_equal,more'],
        'vcr_preventable_poor' => ['required', 'integer'],
        'vcr_preventable_poor_operator' => ['required', 'in:less,less_or_equal,equal,more_or_equal,more'],

        'tenant_id' => 'nullable|exists:tenants,id',
    ];
}

}

