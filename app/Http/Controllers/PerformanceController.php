<?php

namespace App\Http\Controllers;

use App\Models\Performance;
use App\Models\Tenant;

use App\Models\PerformanceMetricRule;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PerformanceController extends Controller
{
    public function index(Request $request)
    {
        $performances = Performance::with('tenant')->get();
        if(is_null(Auth::user()->tenant_id)){
$isSuperAdmin = true;}
else {$isSuperAdmin=false;}
$tenants = [];
    if ($isSuperAdmin) {
        $tenants = Tenant::all();
    }
    if ($isSuperAdmin) {
        $tenantSlug = null;
    } else {
        $tenantSlug = Auth::user()->tenant->slug;
    }
        return Inertia::render('Performance/Index', [
            'performances' => $performances,
            'tenantSlug' => $tenantSlug,
            'SuperAdmin' => $isSuperAdmin,
            'tenants' => $tenants,
        ]);
    }

    public function store(Request $request)
    {
        $tenantId = Auth::user()->tenant_id ?? $request->input('tenant_id');
        $request->merge(['tenant_id' => $tenantId]);

        $rules = [
            'tenant_id' => 'required|exists:tenants,id',
            'date' => 'required|date',
            'acceptance' => 'required|numeric',
            'on_time_to_origin' => 'required|numeric',
            'on_time_to_destination' => 'required|numeric',
            'maintenance_variance_to_spend' => 'required|numeric',
            'open_boc' => 'required|integer',
            'meets_safety_bonus_criteria' => 'required|boolean',
            'vcr_preventable' => 'required|integer',
        ];

        $validated = $request->validate($rules);

        $validated['on_time'] = $validated['on_time_to_origin'] == 0
            ? 0.5
            : ($validated['on_time_to_origin'] * 0.375 + $validated['on_time_to_destination'] * 0.625);

        $rule = PerformanceMetricRule::first();

        $validated['acceptance_rating'] = $this->getRating($validated['acceptance'], $rule, 'acceptance');
        $validated['on_time_rating'] = $this->getRating($validated['on_time'], $rule, 'on_time');
        $validated['maintenance_variance_to_spend_rating'] = $this->getRating($validated['maintenance_variance_to_spend'], $rule, 'maintenance_variance');
        $validated['open_boc_rating'] = $this->getRating($validated['open_boc'], $rule, 'open_boc');
        $validated['meets_safety_bonus_criteria_rating'] = in_array(
            $validated['meets_safety_bonus_criteria'] ? 'fantastic_plus' : 'poor',
            $rule->safety_bonus_eligible_levels ?? []
        ) ? 'fantastic_plus' : 'poor';
        $validated['vcr_preventable_rating'] = $this->getRating($validated['vcr_preventable'], $rule, 'vcr_preventable');

        Performance::updateOrCreate(
            ['tenant_id' => $validated['tenant_id'], 'date' => $validated['date']],
            $validated
        );

        return redirect()->back()->with('success', 'Performance saved successfully.');
    }

    // --- ADMIN UPDATE ---
public function adminUpdate(Request $request,  Performance $performance)
{
    $rules = [
        'tenant_id' => 'required|exists:tenants,id',
        'date' => 'required|date',
        'acceptance' => 'required|numeric',
        'on_time_to_origin' => 'required|numeric',
        'on_time_to_destination' => 'required|numeric',
        'maintenance_variance_to_spend' => 'required|numeric',
        'open_boc' => 'required|integer',
        'meets_safety_bonus_criteria' => 'required|boolean',
        'vcr_preventable' => 'required|integer',
    ];

    $validated = $request->validate($rules);

    $validated['on_time'] = $validated['on_time_to_origin'] == 0
        ? 0.5
        : ($validated['on_time_to_origin'] * 0.375 + $validated['on_time_to_destination'] * 0.625);

    $rule = PerformanceMetricRule::first();

    $validated['acceptance_rating'] = $this->getRating($validated['acceptance'], $rule, 'acceptance');
    $validated['on_time_rating'] = $this->getRating($validated['on_time'], $rule, 'on_time');
    $validated['maintenance_variance_to_spend_rating'] = $this->getRating($validated['maintenance_variance_to_spend'], $rule, 'maintenance_variance');
    $validated['open_boc_rating'] = $this->getRating($validated['open_boc'], $rule, 'open_boc');
    $validated['meets_safety_bonus_criteria_rating'] = in_array(
        $validated['meets_safety_bonus_criteria'] ? 'fantastic_plus' : 'poor',
        $rule->safety_bonus_eligible_levels ?? []
    ) ? 'fantastic_plus' : 'poor';
    $validated['vcr_preventable_rating'] = $this->getRating($validated['vcr_preventable'], $rule, 'vcr_preventable');

    $performance->update($validated);

    return redirect()->back()->with('success', 'Performance updated by admin successfully.');
}

// --- USER UPDATE ---
public function update(Request $request,$tenantSlug,  Performance $performance)
{
    $rules = [
        'date' => 'required|date',
        'acceptance' => 'required|numeric',
        'on_time_to_origin' => 'required|numeric',
        'on_time_to_destination' => 'required|numeric',
        'maintenance_variance_to_spend' => 'required|numeric',
        'open_boc' => 'required|integer',
        'meets_safety_bonus_criteria' => 'required|boolean',
        'vcr_preventable' => 'required|integer',
    ];

    $validated = $request->validate($rules);
    $validated['tenant_id'] = Auth::user()->tenant_id;

    $validated['on_time'] = $validated['on_time_to_origin'] == 0
        ? 0.5
        : ($validated['on_time_to_origin'] * 0.375 + $validated['on_time_to_destination'] * 0.625);

    $rule = PerformanceMetricRule::first();

    $validated['acceptance_rating'] = $this->getRating($validated['acceptance'], $rule, 'acceptance');
    $validated['on_time_rating'] = $this->getRating($validated['on_time'], $rule, 'on_time');
    $validated['maintenance_variance_to_spend_rating'] = $this->getRating($validated['maintenance_variance_to_spend'], $rule, 'maintenance_variance');
    $validated['open_boc_rating'] = $this->getRating($validated['open_boc'], $rule, 'open_boc');
    $validated['meets_safety_bonus_criteria_rating'] = in_array(
        $validated['meets_safety_bonus_criteria'] ? 'fantastic_plus' : 'poor',
        $rule->safety_bonus_eligible_levels ?? []
    ) ? 'fantastic_plus' : 'poor';
    $validated['vcr_preventable_rating'] = $this->getRating($validated['vcr_preventable'], $rule, 'vcr_preventable');

    $performance->update($validated);

    return redirect()->back()->with('success', 'Performance updated by user successfully.');
}

// --- ADMIN DELETE ---
public function adminDestroy( Performance $performance)
{
    $performance->delete();
    return redirect()->back()->with('success', 'Performance deleted by admin.');
}

// --- USER DELETE ---
public function destroy($tenantSlug, Performance $performance)
{
    // Optional: Add authorization logic here
    if ($performance->tenant_id !== Auth::user()->tenant_id) {
        abort(403, 'Unauthorized');
    }

    $performance->delete();
    return redirect()->back()->with('success', 'Performance deleted by user.');
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

    $isSuperAdmin = Auth::user()->tenant_id === null;

    // SuperAdmin expects tenant_name instead of tenant_id
    $expectedHeaders = $isSuperAdmin
        ? [
            'tenant_name',
            'date',
            'acceptance',
            'on_time_to_origin',
            'on_time_to_destination',
            'maintenance_variance_to_spend',
            'open_boc',
            'meets_safety_bonus_criteria',
            'vcr_preventable',
        ]
        : [
            'date',
            'acceptance',
            'on_time_to_origin',
            'on_time_to_destination',
            'maintenance_variance_to_spend',
            'open_boc',
            'meets_safety_bonus_criteria',
            'vcr_preventable',
        ];

    $headers = fgetcsv($handle, 0, ',');

    $rowsImported = 0;
    $rowsSkipped = 0;
    $rule = PerformanceMetricRule::first();

    while (($row = fgetcsv($handle, 0, ',')) !== false) {
        if (count($row) !== count($expectedHeaders)) {
            $rowsSkipped++;
            continue;
        }

        $data = array_combine($expectedHeaders, $row);

        try {
            $data['date'] = Carbon::createFromFormat('m/d/Y', $data['date'])->format('Y-m-d');
        } catch (\Exception $e) {
            $rowsSkipped++;
            continue;
        }

        $data['meets_safety_bonus_criteria'] = match (strtolower(trim($data['meets_safety_bonus_criteria']))) {
            'yes' => true,
            'no' => false,
            default => false,
        };

        $data = collect($data)->map(fn($val) => $val === '' ? null : $val)->toArray();

        if (!$isSuperAdmin) {
            $data['tenant_id'] = Auth::user()->tenant_id;
        } else {
            // Look up tenant ID by name
            $tenant = Tenant::where('name', $data['tenant_name'])->first();
            if (!$tenant) {
                $rowsSkipped++;
                continue;
            }
            $data['tenant_id'] = $tenant->id;
            unset($data['tenant_name']);
        }

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

        $data['on_time'] = $data['on_time_to_origin'] == 0
            ? 0.5
            : ($data['on_time_to_origin'] * 0.375 + $data['on_time_to_destination'] * 0.625);

        $data['acceptance_rating'] = $this->getRating($data['acceptance'], $rule, 'acceptance');
        $data['on_time_rating'] = $this->getRating($data['on_time'], $rule, 'on_time');
        $data['maintenance_variance_to_spend_rating'] = $this->getRating($data['maintenance_variance_to_spend'], $rule, 'maintenance_variance');
        $data['open_boc_rating'] = $this->getRating($data['open_boc'], $rule, 'open_boc');
        $data['meets_safety_bonus_criteria_rating'] = in_array(
            $data['meets_safety_bonus_criteria'] ? 'fantastic_plus' : 'poor',
            $rule->safety_bonus_eligible_levels ?? []
        ) ? 'fantastic_plus' : 'poor';
        $data['vcr_preventable_rating'] = $this->getRating($data['vcr_preventable'], $rule, 'vcr_preventable');

        Performance::updateOrCreate(
            ['tenant_id' => $data['tenant_id'], 'date' => $data['date']],
            $data
        );

        $rowsImported++;
    }

    fclose($handle);

    return back()->with('success', "$rowsImported rows imported or updated. $rowsSkipped skipped.");
}


    public function export()
    {
        $query = Performance::with('tenant');

        $performances = $query->get();

        if ($performances->isEmpty()) {
            return back()->with('error', 'No performance data to export.');
        }

        $fileName = 'performances_' . Str::random(8) . '.csv';
        $filePath = public_path($fileName);
        $file = fopen($filePath, 'w');

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

        fputcsv($file, $headers);

        foreach ($performances as $p) {
            fputcsv($file, [
                $p->tenant->name ?? '—',
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
                $p->meets_safety_bonus_criteria ? 'Yes' : 'No',
                $p->meets_safety_bonus_criteria_rating,
                $p->vcr_preventable,
                $p->vcr_preventable_rating,
            ]);
        }

        fclose($file);

        return Response::download($filePath)->deleteFileAfterSend(true);
    }

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

    protected function compare($value, $threshold, $operator)
    {
        return match ($operator) {
            'less' => $value < $threshold,
            'less_or_equal' => $value <= $threshold,
            'equal' => $value == $threshold,
            'more_or_equal' => $value >= $threshold,
            'more' => $value > $threshold,
            default => false,
        };
    }


}
