<?php

namespace App\Services\Summaries;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Tenant;
use App\Services\Filtering\FilteringService;

class SummariesService
{
    protected PerformanceDataService $performanceDataService;
    protected SafetyDataService $safetyDataService;
    protected DelayBreakdownService $delayBreakdownService;
    protected RejectionBreakdownService $rejectionBreakdownService;
    protected MaintenanceBreakdownService $maintenanceBreakdownService;
    protected FilteringService $filteringService;

    public function __construct(
        PerformanceDataService $performanceDataService,
        SafetyDataService $safetyDataService,
        DelayBreakdownService $delayBreakdownService,
        RejectionBreakdownService $rejectionBreakdownService,
        MaintenanceBreakdownService $maintenanceBreakdownService,
        FilteringService $filteringService
    ) {
        $this->performanceDataService    = $performanceDataService;
        $this->safetyDataService         = $safetyDataService;
        $this->delayBreakdownService     = $delayBreakdownService;
        $this->rejectionBreakdownService = $rejectionBreakdownService;
        $this->maintenanceBreakdownService = $maintenanceBreakdownService;
        $this->filteringService = $filteringService;
    }

    public function compileSummaries($dateFilter = null): array
    {
        // Get date filter from request or use the provided parameter
        $dateFilter = $dateFilter ?? $this->filteringService->getDateFilter('yesterday');
        
        // Get date range based on filter
        $dateRange = [];
        $now = Carbon::now();
        
        switch ($dateFilter) {
            case 'yesterday':
                $startDate = Carbon::yesterday()->startOfDay();
                $endDate = Carbon::yesterday()->endOfDay();
                $label = 'Yesterday';
                break;
                
            case 'current-week':
                $startDate = $now->copy()->startOfWeek(Carbon::SUNDAY)->startOfDay();
                $endDate = $now->copy()->endOfWeek(Carbon::SATURDAY)->endOfDay();
                $label = 'Current Week';
                break;
                
            case 't6w':
                $startDate = $now->copy()->startOfWeek(Carbon::SUNDAY)->subWeeks(5)->startOfDay();
                $endDate = $now->copy()->endOfWeek(Carbon::SATURDAY)->endOfDay();
                $label = '6 Weeks';
                break;
                
            case 'quarterly':
                $startDate = $now->copy()->subMonths(3)->startOfDay();
                $endDate = $now->copy()->endOfDay();
                $label = 'Quarterly';
                break;
                
            default:
                // Default to yesterday if an invalid filter is provided
                $startDate = Carbon::yesterday()->startOfDay();
                $endDate = Carbon::yesterday()->endOfDay();
                $label = 'Yesterday';
                break;
        }
        
        $dateRange = [
            'start' => $startDate->format('Y-m-d'),
            'end' => $endDate->format('Y-m-d'),
            'label' => $label
        ];
        
        // Get data for the selected date range only
        // Note: PerformanceDataService will still use a 6-week rolling window for specific metrics
        $summaries = [
            'performance' => $this->performanceDataService->getPerformanceData(
                $startDate,
                $endDate,
                $label
            ),
            'safety' => $this->safetyDataService->getSafetyData(
                $startDate,
                $endDate
            ),
            'date_range' => $dateRange
        ];
        
        $isSuperAdmin = Auth::check() && is_null(Auth::user()->tenant_id);
        $tenantSlug = $isSuperAdmin ? null : (Auth::check() ? Auth::user()->tenant->slug : null);
        $tenants = $isSuperAdmin ? Tenant::all() : [];
        
        return [
            'summaries' => $summaries,
            'tenantSlug' => $tenantSlug,
            'SuperAdmin' => $isSuperAdmin,
            'tenants' => $tenants,
            'delayBreakdowns' => $this->delayBreakdownService->getDelayBreakdown(
                $startDate,
                $endDate
            ),
            'rejectionBreakdowns' => $this->rejectionBreakdownService->getRejectionBreakdown(
                $startDate,
                $endDate
            ),
            'maintenanceBreakdowns' => $this->maintenanceBreakdownService->getMaintenanceBreakdown(
                $startDate,
                $endDate
            ),
            'dateFilter' => $dateFilter,
            'dateRange' => $dateRange
        ];
    }
}
