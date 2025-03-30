<?php 

namespace App\Services\Performance;

use App\Models\PerformanceMetricRule;
class PerformanceMetricRuleService{
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
        PerformanceMetricRule::updateOrCreate(['id' => PerformanceMetricRule::first()?->id ?? 1], $data);
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

}