<?php 

namespace App\Services\Performance;

use App\Models\PerformanceMetricRule;
use App\Models\TenantMetricsRule;
class PerformanceMetricRuleService
{
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
        TenantMetricsRule::updateOrCreate(
            ['tenant_id'   => $data['mvts_tenant_id']],        // unique key
            ['mvts_divisor' => $data['mvts_divisor']]
        );
    }

    /**
     * Retrieve the validation rules for performance metrics.
     *
     * @return array
     */
    public function getMetricValidationRules(): array
    {
        $performanceLevels = ['fantastic_plus', 'fantastic', 'good', 'fair', 'poor'];
        $performanceMetrics = ['acceptance', 'on_time', 'maintenance_variance', 'open_boc', 'vcr_preventable', 'vmcr_p'];
        
        $safetyLevels = ['gold', 'silver', 'not_eligible'];
        $safetyMetrics = [
            'driver_distraction', 
            'speeding_violation', 
            'sign_violation', 
            'traffic_light_violation', 
            'following_distance'
        ];
        
        $rules = [];
        
        // Performance metrics validation rules
        foreach ($performanceMetrics as $metric) {
            foreach ($performanceLevels as $level) {
                $rules["{$metric}_{$level}"] = ['required', 'numeric'];
                $rules["{$metric}_{$level}_operator"] = ['required', 'in:less,less_or_equal,equal,more_or_equal,more'];
            }
        }
        
        // Safety metrics validation rules
        foreach ($safetyMetrics as $metric) {
            foreach ($safetyLevels as $level) {
                $rules["{$metric}_{$level}"] = ['required', 'numeric'];
                $rules["{$metric}_{$level}_operator"] = ['required', 'in:less,less_or_equal,equal,more_or_equal,more'];
            }
        }
        
        $rules['safety_bonus_eligible_levels'] = ['nullable', 'array'];
        $rules['safety_bonus_eligible_levels.*'] = ['in:fantastic_plus,fantastic,good,fair,poor'];
        
        // Tenant selector must exist in tenants table
        $rules['mvts_tenant_id'] = ['required', 'integer', 'exists:tenants,id'];
        // Divisor must be a positive numeric (at least 0.001)
        $rules['mvts_divisor']   = ['required', 'numeric', 'min:0.001'];
        
        return $rules;
    }
}