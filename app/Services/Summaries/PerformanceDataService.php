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
    protected MaintenanceBreakdownService $maintenanceBreakdownService;
    protected ?int $email_tenant_id;


    public function __construct(
        PerformanceCalculationsService $performanceCalculationsService,
        MaintenanceBreakdownService $maintenanceBreakdownService,
        ?int $email_tenant_id = null
    ) {
        $this->performanceCalculationsService = $performanceCalculationsService;
        $this->maintenanceBreakdownService = $maintenanceBreakdownService;
        $this->email_tenant_id = $email_tenant_id;
    }

    /**
     * Get main performance metrics for the specified date range
     */
    public function getMainPerformanceData($startDate, $endDate)
    {
        $query = DB::table('performances')
            ->selectRaw("
                AVG(acceptance) AS average_acceptance, 
                AVG(on_time) AS average_on_time, 
                CASE WHEN SUM(meets_safety_bonus_criteria) >= COUNT(meets_safety_bonus_criteria) / 2 
                    THEN 1 ELSE 0 END AS meets_safety_bonus_criteria
            ")
            ->whereBetween('date', [$startDate, $endDate]);

        $this->applyTenantFilter($query);
        return $query->first();
    }

    /**
     * Get rolling performance metrics for the last 6 weeks
     */
    public function getRollingPerformanceData()
    {
        $rollingStart = Carbon::now()->subWeeks(6)->startOfWeek();
        $rollingEnd = Carbon::now();

        $query = DB::table('performances')
            ->selectRaw("
                SUM(open_boc) AS sum_open_boc, 
                SUM(vcr_preventable) AS sum_vcr_preventable, 
                SUM(vmcr_p) AS sum_vmcr_p
            ")
            ->whereBetween('date', [$rollingStart, $rollingEnd]);

        $this->applyTenantFilter($query);
        return $query->first();
    }

    /**
     * Get the latest updated_at timestamp from performances table
     */
    public function getLatestUpdateTimestamp()
    {
        $query = DB::table('performances')
            ->select('updated_at')
            ->orderBy('updated_at', 'desc')
            ->limit(1);
            
        $this->applyTenantFilter($query);
        $result = $query->first();
        
        return $result ? $result->updated_at : null;
    }

    /**
     * Apply tenant filter to query if user is authenticated
     */
    public function applyTenantFilter($query)
    {
        if ($this->email_tenant_id !== null) {
            $query->where('tenant_id', $this->email_tenant_id);
            return;
        }
        if (Auth::check() && Auth::user()->tenant_id !== null) {
            $query->where('tenant_id', Auth::user()->tenant_id);
        }
    }

    /**
     * Calculate performance ratings based on rules
     */
    public function calculateRatings($mainData, $rollingData, $maintenanceData, $rule): array
    {
        return [
            'average_acceptance' => $this->performanceCalculationsService->getRating(
                $mainData->average_acceptance, 
                $rule, 
                'acceptance'
            ),
            'average_on_time' => $this->performanceCalculationsService->getRating(
                $mainData->average_on_time, 
                $rule, 
                'on_time'
            ),
            'average_maintenance_variance_to_spend' => $this->performanceCalculationsService->getRating(
                $maintenanceData['qs_MVtS'], 
                $rule, 
                'maintenance_variance'
            ),
            'open_boc' => $this->performanceCalculationsService->getRating(
                $rollingData->sum_open_boc, 
                $rule, 
                'open_boc'
            ),
            'vcr_preventable' => $this->performanceCalculationsService->getRating(
                $rollingData->sum_vcr_preventable, 
                $rule, 
                'vcr_preventable'
            ),
            'vmcr_p' => $this->performanceCalculationsService->getRating(
                $rollingData->sum_vmcr_p, 
                $rule, 
                'vmcr_p'
            ),
            'meets_safety_bonus_criteria' => $this->performanceCalculationsService->getSafetyBonusRating(
                $mainData->meets_safety_bonus_criteria, 
                $rule
            ),
        ];
    }

    /**
     * Get complete performance data for the specified date range
     */
    public function getPerformanceData($startDate, $endDate, string $label = '',$milesDriven,$passedQSMVtS): array
    {
        $rule = PerformanceMetricRule::first();
        
        $mainData = $this->getMainPerformanceData($startDate, $endDate);
        $rollingData = $this->getRollingPerformanceData();
        $lastUpdated = $this->getLatestUpdateTimestamp();
        if(!is_null($milesDriven) && $milesDriven > 0){
        $rollingData->sum_vcr_preventable = $rollingData->sum_vcr_preventable/$milesDriven;
        }
        // Get QS invoice amount and total miles for MVtS calculation
        $qsMVtS = $passedQSMVtS;
        $ratings = $this->calculateRatings($mainData, $rollingData, ['qs_MVtS' => $qsMVtS], $rule);

        return [
            'label' => $label,
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'last_updated' => $lastUpdated,
            'data' => [
                'average_acceptance' => $mainData->average_acceptance ?? 0,
                'average_on_time' => $mainData->average_on_time ?? 0,
                'average_maintenance_variance_to_spend' => $qsMVtS ?? 0,
                'open_boc' => $rollingData->sum_open_boc ?? 0,
                'vcr_preventable' => $rollingData->sum_vcr_preventable ?? 0,
                'vmcr_p' => $rollingData->sum_vmcr_p ?? 0,
                'meets_safety_bonus_criteria' => $mainData->meets_safety_bonus_criteria ?? 0,
            ],
            'ratings' => $ratings,
        ];
    }
}
