<?php

namespace App\Services\Summaries;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Tenant;
use App\Services\Filtering\FilteringService;
use App\Models\MilesDriven;
use Illuminate\Support\Facades\DB;
use App\Models\Driver;

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
        $dateFilter = $dateFilter ?? $this->filteringService->getDateFilter('yesterday');
        $dateRange = [];
        $now = Carbon::now();
        $isSunday = $now->dayOfWeek === 0; // 0 = Sunday in Carbon

        switch ($dateFilter) {
            case 'yesterday':
                $startDate = Carbon::yesterday()->startOfDay();
                $endDate = Carbon::yesterday()->endOfDay();
                $label = 'Yesterday';
                break;

            case 'current-week':
                $startDate = $now->copy()->startOfDay()->modify('last sunday');
                if ($isSunday) {
                    $startDate->subWeek();
                }
                $endDate = $startDate->copy()->addDays(6)->endOfDay(); // Saturday
                $label = 'Current Week';
                break;

            case 't6w':
                $startDate = $now->copy()->modify('last sunday');
                if ($isSunday) {
                    $startDate->subWeek();
                }
                $startDate->subWeeks(5)->startOfDay();
                $endDate = $now->copy()->modify('this saturday');
                if ($isSunday) {
                    $endDate->subWeek();
                }
                $endDate->endOfDay();
                $label = '6 Weeks';
                break;

            case 'quarterly':
                $startDate = $now->copy()->subMonths(3)->modify('last sunday');
                if ($isSunday) {
                    $startDate->subWeek();
                }
                $startDate->startOfDay();
                $endDate = $now->copy()->modify('this saturday');
                if ($isSunday) {
                    $endDate->subWeek();
                }
                $endDate->endOfDay();
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
        $permissions=Auth::user()->getAllPermissions();
        // Adjust dates for maintenance breakdown (weeks 16-24 instead of 17-25)
        $maintenanceStartDate = $startDate->copy()->subWeek();
        $maintenanceEndDate = $endDate->copy()->subWeek();
        $driverOverAll = $this->getDriversOverallPerformance($startDate, $endDate);
        return [
            'summaries' => $summaries,
            'tenantSlug' => $tenantSlug,
            'SuperAdmin' => $isSuperAdmin,
            'tenants' => $tenants,
            'delayBreakdowns' => $this->delayBreakdownService->getDelayBreakdown($startDate, $endDate),
            'rejectionBreakdowns' => $this->rejectionBreakdownService->getRejectionBreakdown($startDate, $endDate),
            'maintenanceBreakdowns' => $this->maintenanceBreakdownService->getMaintenanceBreakdown($maintenanceStartDate, $maintenanceEndDate, $minInvoiceAmount, $outstandingDateCarbon),
            'dateFilter' => $dateFilter,
            'dateRange' => $dateRange,
            'driversOverallPerformance' => $driverOverAll,
            'permissions' => $permissions,
            'milesDriven' => $this->getMilesDrivenSum($startDate, $endDate, $dateFilter),
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

    /**
     * Get drivers' overall performance scores
     * 
     * @param string|Carbon $startDate The start date for the query
     * @param string|Carbon $endDate The end date for the query
     * @return array The drivers' overall performance data
     */
    public function getDriversOverallPerformance($startDate, $endDate): array
    {
        // Ensure dates are Carbon instances
        if (!($startDate instanceof Carbon)) {
            $startDate = Carbon::parse($startDate);
        }
        
        if (!($endDate instanceof Carbon)) {
            $endDate = Carbon::parse($endDate);
        }
        
        
        $totalRejectionPenaltiesQuery = DB::table('rejections')
            ->whereBetween('date', [$startDate, $endDate])
            ->where('driver_controllable', true)
            ->selectRaw('SUM(penalty) as total_rejection_penalties');
        
        $this->applyTenantFilter($totalRejectionPenaltiesQuery);
        $totalRejectionPenalties = $totalRejectionPenaltiesQuery->first()->total_rejection_penalties ?? 0;
        
        $totalDelayPenaltiesQuery = DB::table('delays')
            ->whereBetween('date', [$startDate, $endDate])
            ->where('driver_controllable', true)
            ->selectRaw('SUM(penalty) as total_delay_penalties');
        
        $this->applyTenantFilter($totalDelayPenaltiesQuery);
        $totalDelayPenalties = $totalDelayPenaltiesQuery->first()->total_delay_penalties ?? 0;
        
        // Get all drivers
        $driversQuery = DB::table('drivers')
            ->select('id', 'first_name', 'last_name', 'netradyne_user_name');
        
        $this->applyTenantFilter($driversQuery);
        $drivers = $driversQuery->get();
        
        // Initialize array to store results
        $driversOverallScores = [];
        
        // Process each driver in a single loop
        foreach ($drivers as $driver) {
            $driverName = $driver->first_name . ' ' . $driver->last_name;
            $netradyneUserName = $driver->netradyne_user_name;
            
            // Get safety score for the driver
            $safetyScoreQuery = DB::table('safety_data')
                ->where(function($query) use ($driverName, $netradyneUserName) {
                    $query->where('user_name', $netradyneUserName)
                          ->orWhere('driver_name', $driverName);
                })
                ->whereBetween('date', [$startDate, $endDate])
                ->selectRaw("AVG(driver_score) as safety_score, SUM(minutes_analyzed) as minutes_analyzed");
            
            $this->applyTenantFilter($safetyScoreQuery);
            $safetyScore = $safetyScoreQuery->first()->safety_score ?? 0;
            $minutesAnalyzed = $safetyScoreQuery->first()->minutes_analyzed?? 0;

            if($minutesAnalyzed == 0){
                continue;
            }


            // Get rejection penalties for the driver
            $rejectionPenaltiesQuery = DB::table('rejections')
                ->where('driver_name', $driverName)
                ->whereBetween('date', [$startDate, $endDate])
                ->where('driver_controllable', true)
                ->selectRaw('SUM(penalty) as total_rejection_penalties');
            
            $this->applyTenantFilter($rejectionPenaltiesQuery);
            $rejectionPenalties = $rejectionPenaltiesQuery->first()->total_rejection_penalties ?? 0;
            
            // Get delay penalties for the driver
            $delayPenaltiesQuery = DB::table('delays')
                ->where('driver_name', $driverName)
                ->whereBetween('date', [$startDate, $endDate])
                ->where('driver_controllable', true)
                ->selectRaw('SUM(penalty) as total_delay_penalties');
            
            $this->applyTenantFilter($delayPenaltiesQuery);
            $delayPenalties = $delayPenaltiesQuery->first()->total_delay_penalties ?? 0;
            
            // Calculate scores directly
            $acceptanceScore = 100;
            if ($totalRejectionPenalties > 0) {
                $acceptanceScore = 100 - ($rejectionPenalties * 100 / $totalRejectionPenalties);
            }
            
            $onTimeScore = 100;
            if ($totalDelayPenalties > 0) {
                $onTimeScore = 100 - ($delayPenalties * 100 / $totalDelayPenalties);
            }
            
            $safetyScoreNormalized = 0;
                $safetyScoreNormalized = $safetyScore * 100 / 1050;
            
            $overallScore = ($acceptanceScore + $onTimeScore + $safetyScoreNormalized) / 3;
            
            // Add driver data with all scores calculated
            $driversOverallScores[] = [
                'driver_name' => $driverName,
                'acceptance_score' => round($acceptanceScore, 2),
                'on_time_score' => round($onTimeScore, 2),
                'safety_score' => round($safetyScoreNormalized, 2),
                'overall_score' => round($overallScore, 2),
                'raw_safety_score' => round($safetyScore,2),
                'rejection_penalties' => $rejectionPenalties,
                'delay_penalties' => $delayPenalties,
                'minutes_analyzed' => $minutesAnalyzed,
            ];
        }
        
        // Sort drivers by overall score (highest to lowest)
        usort($driversOverallScores, function($a, $b) {
            return $b['overall_score'] <=> $a['overall_score'];
        });
        
        return [
            'drivers' => $driversOverallScores,
        ];
    }
    
    /**
     * Apply tenant filter to query if user is authenticated
     */
    private function applyTenantFilter($query)
    {
        if (Auth::check() && Auth::user()->tenant_id !== null) {
            $query->where('tenant_id', Auth::user()->tenant_id);
        }
    }

    /**
     * Get the sum of miles driven within a specified timeframe, except for yesterday timeframe
     * 
     * @param string|Carbon $startDate The start date for the query
     * @param string|Carbon $endDate The end date for the query
     * @param string|null $dateFilter The date filter type
     * @return float The total miles driven
     */
    public function getMilesDrivenSum($startDate, $endDate, $dateFilter = null): float
{
    // Skip calculation for yesterday timeframe
    if ($dateFilter === 'yesterday') {
        return 0;
    }

    // Ensure dates are Carbon instances
    if (!($startDate instanceof Carbon)) {
        $startDate = Carbon::parse($startDate);
    }

    if (!($endDate instanceof Carbon)) {
        $endDate = Carbon::parse($endDate);
    }

    // Query to get sum of miles driven within the date range
    $query = DB::table('miles_driven')
        ->where(function ($q) use ($startDate, $endDate) {
            $q->whereBetween('week_start_date', [$startDate, $endDate])
              ->orWhereBetween('week_end_date', [$startDate, $endDate]);
        })
        ->selectRaw('SUM(miles) as total_miles');

    // Apply tenant filter if user is authenticated
    $this->applyTenantFilter($query);

    // Get the result safely
    $result = $query->first();

    return $result ? (float) $result->total_miles : 0;
}
}
