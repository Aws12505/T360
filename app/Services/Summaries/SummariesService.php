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
    $dateFilter = $dateFilter ?? $this->filteringService->getDateFilter('yesterday');
    $dateRange = [];
    $now = Carbon::now();

    switch ($dateFilter) {
        case 'yesterday':
            $startDate = Carbon::yesterday()->startOfDay();
            $endDate = Carbon::yesterday()->endOfDay();
            $label = 'Yesterday';
            break;

        case 'current-week':
            $startDate = $now->copy()->startOfDay()->modify('last sunday');
            $endDate = $startDate->copy()->addDays(6)->endOfDay(); // Saturday
            $label = 'Current Week';
            break;

        case 't6w':
            $startDate = $now->copy()->modify('last sunday')->subWeeks(5)->startOfDay();
            $endDate = $now->copy()->modify('this saturday')->endOfDay();
            $label = '6 Weeks';
            break;

        case 'quarterly':
            $startDate = $now->copy()->subMonths(3)->modify('last sunday')->startOfDay();
            $endDate = $now->copy()->modify('this saturday')->endOfDay();
            $label = 'Quarterly';
            break;

        default:
            $startDate = Carbon::yesterday()->startOfDay();
            $endDate = Carbon::yesterday()->endOfDay();
            $label = 'Yesterday';
            break;
    }

    $year = $startDate->copy()->year;
    $weekNumber = null;
    $startWeekNumber = null;
    $endWeekNumber = null;

    // Custom week number calculation (Sunday-based)
    $getWeekNumber = function (Carbon $date) {
        $sundayStart = Carbon::parse($date)->modify('last sunday');
        return intval($sundayStart->format('W'));
    };

    if (in_array($dateFilter, ['yesterday', 'current-week'])) {
        $weekNumber = $getWeekNumber($startDate);
    } else {
        $startWeekNumber = $getWeekNumber($startDate);
        $endWeekNumber = $getWeekNumber($endDate);
    }

    $dateRange = [
        'start' => $startDate->format('Y-m-d'),
        'end' => $endDate->format('Y-m-d'),
        'label' => $label,
        'weekNumber' => $weekNumber,
        'startWeekNumber' => $startWeekNumber,
        'endWeekNumber' => $endWeekNumber,
        'year' => $year
    ];

    // Fetch data
    $summaries = [
        'performance' => $this->performanceDataService->getPerformanceData($startDate, $endDate, $label),
        'safety' => $this->safetyDataService->getSafetyData($startDate, $endDate),
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
        'delayBreakdowns' => $this->delayBreakdownService->getDelayBreakdown($startDate, $endDate),
        'rejectionBreakdowns' => $this->rejectionBreakdownService->getRejectionBreakdown($startDate, $endDate),
        'maintenanceBreakdowns' => $this->maintenanceBreakdownService->getMaintenanceBreakdown($startDate, $endDate),
        'dateFilter' => $dateFilter,
        'dateRange' => $dateRange
    ];
}

}
