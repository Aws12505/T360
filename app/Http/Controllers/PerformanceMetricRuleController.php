<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\PerformanceMetricRule;

class PerformanceMetricRuleController extends Controller
{
    public function editGlobal()
    {
        $rule = PerformanceMetricRule::first();
        return Inertia::render('PerformanceRules/Admin', [
            'metrics' => $rule,
        ]);
    }

    public function updateGlobal(Request $request)
    {
        $validated = $this->validateData($request);
        PerformanceMetricRule::updateOrCreate(['id' => 1], $validated);
        return back()->with('success', 'Global performance metrics updated.');
    }

    protected function validateData(Request $request): array
    {
        return $request->validate($this->validationRules());
    }

    protected function validationRules(): array
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
}
