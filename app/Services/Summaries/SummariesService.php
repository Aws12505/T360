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

    public function compileSummaries($dateFilter = null, $minInvoiceAmount = null, $outstandingDate = null): array
    {
        $dateFilter = $dateFilter ?? $this->filteringService->getDateFilter('t6w');
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

        // year for the start of the interval
        $year = $startDate->year;

        // compute week numbers (Sunday=first day)
        if (in_array($dateFilter, ['yesterday', 'current-week'])) {
            $weekNumber      = $this->weekNumberSundayStart($startDate);
            $startWeekNumber = $endWeekNumber = null;
        } else {
            $weekNumber      = null;
            $startWeekNumber = $this->weekNumberSundayStart($startDate);
            $endWeekNumber   = $this->weekNumberSundayStart($endDate);
        }

        $dateRange = [
            'start'           => $startDate->toDateString(),
            'end'             => $endDate->toDateString(),
            'label'           => $label,
            'weekNumber'      => $weekNumber,
            'startWeekNumber' => $startWeekNumber,
            'endWeekNumber'   => $endWeekNumber,
            'year'            => $year,
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

        // Convert outstandingDate to Carbon instance if it's provided
        $outstandingDateCarbon = $outstandingDate ? Carbon::parse($outstandingDate) : null;

        // Adjust dates for maintenance breakdown (weeks 16-24 instead of 17-25)
        $maintenanceStartDate = $startDate->copy()->subWeek();
        $maintenanceEndDate = $endDate->copy()->subWeek();

        return [
            'summaries' => $summaries,
            'tenantSlug' => $tenantSlug,
            'SuperAdmin' => $isSuperAdmin,
            'tenants' => $tenants,
            'delayBreakdowns' => $this->delayBreakdownService->getDelayBreakdown($startDate, $endDate),
            'rejectionBreakdowns' => $this->rejectionBreakdownService->getRejectionBreakdown($startDate, $endDate),
            'maintenanceBreakdowns' => $this->maintenanceBreakdownService->getMaintenanceBreakdown($maintenanceStartDate, $maintenanceEndDate, $minInvoiceAmount, $outstandingDateCarbon),
            'dateFilter' => $dateFilter,
            'dateRange' => $dateRange
        ];
    }

    /**
     * Get the week‐of‐year for a Carbon date, where weeks run Sunday → Saturday.
     *
     * @param  Carbon  $date
     * @return int
     */
    private function weekNumberSundayStart(Carbon $date): int
    {
        // 1..366
        $dayOfYear   = $date->dayOfYear;

        // 0=Sunday, …, 6=Saturday for Jan 1
        $firstDayDow = $date->copy()
                            ->startOfYear()
                            ->dayOfWeek;

        // shift so weeks bound on Sunday, then ceil
        return (int) ceil(($dayOfYear + $firstDayDow) / 7);
    }
}
