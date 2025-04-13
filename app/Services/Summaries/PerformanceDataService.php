<?php

namespace App\Services\Summaries;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\PerformanceMetricRule;
use App\Services\Performance\PerformanceCalculationsService;

class PerformanceDataService
{
    protected PerformanceCalculationsService $performanceCalculationsService;

    public function __construct(PerformanceCalculationsService $performanceCalculationsService)
    {
        $this->performanceCalculationsService = $performanceCalculationsService;
    }

    public function getPerformanceData($startDate, $endDate, string $label = ''): array
    {
        $rule = PerformanceMetricRule::first();
        
        // Always use 6-week rolling window for rolling metrics
        $rollingStart = Carbon::now()->subWeeks(6)->startOfWeek();
        $rollingEnd   = Carbon::now();
    
        // Main performance query - uses the provided date range
        $queryMain = DB::table('performances')
            ->selectRaw("AVG(acceptance) AS average_acceptance, AVG(on_time) AS average_on_time, AVG(maintenance_variance_to_spend) AS average_maintenance_variance_to_spend, CASE WHEN SUM(meets_safety_bonus_criteria) >= COUNT(meets_safety_bonus_criteria) / 2 THEN 1 ELSE 0 END AS meets_safety_bonus_criteria")
            ->whereBetween('date', [$startDate, $endDate]);
    
        if (Auth::check() && Auth::user()->tenant_id !== null) {
            $queryMain->where('tenant_id', Auth::user()->tenant_id);
        }
    
        $mainData = $queryMain->first();
    
        // Rolling data query - always uses the 6-week rolling window
        $queryRolling = DB::table('performances')
            ->selectRaw("SUM(open_boc) AS sum_open_boc, SUM(vcr_preventable) AS sum_vcr_preventable, SUM(vmcr_p) AS sum_vmcr_p")
            ->whereBetween('date', [$rollingStart, $rollingEnd]);
    
        if (Auth::check() && Auth::user()->tenant_id !== null) {
            $queryRolling->where('tenant_id', Auth::user()->tenant_id);
        }
    
        $rollingData = $queryRolling->first();
    
        return [
            'label'      => $label,
            'start_date' => $startDate->toDateString(),
            'end_date'   => $endDate->toDateString(),
            'data'       => [
                'average_acceptance'                    => $mainData->average_acceptance ?? 0,
                'average_on_time'                       => $mainData->average_on_time ?? 0,
                'average_maintenance_variance_to_spend' => $mainData->average_maintenance_variance_to_spend ?? 0,
                'open_boc'                              => $rollingData->sum_open_boc ?? 0,
                'vcr_preventable'                       => $rollingData->sum_vcr_preventable ?? 0,
                'vmcr_p'                                => $rollingData->sum_vmcr_p ?? 0,
                'meets_safety_bonus_criteria'           => $mainData->meets_safety_bonus_criteria ?? 0,
            ],
            'ratings'    => [
                'average_acceptance'                    => $this->performanceCalculationsService->getRating($mainData->average_acceptance, $rule, 'acceptance'),
                'average_on_time'                       => $this->performanceCalculationsService->getRating($mainData->average_on_time, $rule, 'on_time'),
                'average_maintenance_variance_to_spend' => $this->performanceCalculationsService->getRating($mainData->average_maintenance_variance_to_spend, $rule, 'maintenance_variance'),
                'open_boc'                              => $this->performanceCalculationsService->getRating($rollingData->sum_open_boc, $rule, 'open_boc'),
                'vcr_preventable'                       => $this->performanceCalculationsService->getRating($rollingData->sum_vcr_preventable, $rule, 'vcr_preventable'),
                'vmcr_p'                                => $this->performanceCalculationsService->getRating($rollingData->sum_vmcr_p, $rule, 'vmcr_p'),
                'meets_safety_bonus_criteria'           => $this->performanceCalculationsService->getSafetyBonusRating($mainData->meets_safety_bonus_criteria, $rule),
            ],
        ];
    }
}
