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
        $this->performanceDataService = $performanceDataService;
        $this->safetyDataService = $safetyDataService;
        $this->delayBreakdownService = $delayBreakdownService;
        $this->rejectionBreakdownService = $rejectionBreakdownService;
        $this->maintenanceBreakdownService = $maintenanceBreakdownService;
        $this->filteringService = $filteringService;
    }

    private function calculateAverageOnTime($startDate, $endDate): float
    {
        $query = DB::table('delays')
            ->selectRaw("
            SUM(CASE WHEN delay_type='origin' AND carrier_controllable = 1 THEN penalty ELSE 0 END) as origin_penalty,
            SUM(CASE WHEN delay_type='destination' AND carrier_controllable = 1 THEN penalty ELSE 0 END) as destination_penalty,

            SUM(CASE WHEN delay_type='origin' THEN 1 ELSE 0 END) as origin_count,
            SUM(CASE WHEN delay_type='destination' THEN 1 ELSE 0 END) as destination_count
        ")
            ->whereBetween('date', [$startDate, $endDate]);

        $this->applyTenantFilter($query);

        $result = $query->first();

        $originCount = $result->origin_count ?? 0;
        $destinationCount = $result->destination_count ?? 0;

        $originPenalty = $result->origin_penalty ?? 0;
        $destinationPenalty = $result->destination_penalty ?? 0;

        $originPerformance = $originCount > 0
            ? (1 - ($originPenalty / $originCount)) * 100
            : 100;

        $destinationPerformance = $destinationCount > 0
            ? (1 - ($destinationPenalty / $destinationCount)) * 100
            : 100;

        return round(
            ($originPerformance * 0.375) +
            ($destinationPerformance * 0.625),
            2
        );
    }

    private function calculateAverageAcceptance($startDate, $endDate): float
    {
        $query = DB::table('rejections')
            ->selectRaw("
            COUNT(*) as total_entries,
            SUM(CASE WHEN carrier_controllable = 1 THEN penalty ELSE 0 END) as carrier_penalty
        ")
            ->whereBetween('date', [$startDate, $endDate]);

        $this->applyTenantFilter($query);

        $result = $query->first();

        $entries = $result->total_entries ?? 0;
        $penalty = $result->carrier_penalty ?? 0;

        if ($entries == 0) {
            return 100;
        }

        return round((1 - ($penalty / $entries)) * 100, 2);
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
            $weekNumber = $this->weekNumberSundayStart($startDate);
            $startWeekNumber = $endWeekNumber = null;
        } else {
            $weekNumber = null;
            $startWeekNumber = $this->weekNumberSundayStart($startDate);
            $endWeekNumber = $this->weekNumberSundayStart($endDate);
        }

        $dateRange = [
            'start' => $startDate->toDateString(),
            'end' => $endDate->toDateString(),
            'label' => $label,
            'weekNumber' => $weekNumber,
            'startWeekNumber' => $startWeekNumber,
            'endWeekNumber' => $endWeekNumber,
            'year' => $year,
        ];
        if ($dateFilter == 't6w') {
            $maintenanceStartDate = $startDate->copy()->subWeek();
            $maintenanceEndDate = $endDate->copy()->subWeek();
        } else {
            $maintenanceStartDate = $startDate->copy();
            $maintenanceEndDate = $endDate->copy();
        }
        // Fetch data
        $outstandingDateCarbon = $outstandingDate ? Carbon::parse($outstandingDate) : null;
        $maintenaceBreakdown = $this->maintenanceBreakdownService->getMaintenanceBreakdown($maintenanceStartDate, $maintenanceEndDate, $minInvoiceAmount, $outstandingDateCarbon);
        $milesDriven = $this->getMilesDrivenSum($startDate, $endDate, $dateFilter);
        $averageAcceptance = $this->calculateAverageAcceptance($startDate, $endDate);
        $averageOnTime = $this->calculateAverageOnTime($startDate, $endDate);

        $performance = $this->performanceDataService->getPerformanceData(
            $startDate,
            $endDate,
            $label,
            $maintenaceBreakdown['qs_MVtS'] * 100,
            $averageAcceptance,
            $averageOnTime
        );

        $isSuperAdmin = Auth::check() && is_null(Auth::user()->tenant_id);
        $tenantSlug = $isSuperAdmin ? null : (Auth::check() ? Auth::user()->tenant->slug : null);
        $tenants = $isSuperAdmin ? Tenant::all() : [];

        // Convert outstandingDate to Carbon instance if it's provided
        $permissions = Auth::user()->getAllPermissions();
        // Adjust dates for maintenance breakdown (weeks 16-24 instead of 17-25)

        // $driverOverAll = $this->getDriversOverallPerformance($startDate, $endDate);
        return [
            'summaries' => [
                'performance' => $performance,
                'safety' => $this->safetyDataService->getSafetyData($startDate, $endDate),
                'date_range' => $dateRange
            ],
            'tenantSlug' => $tenantSlug,
            'SuperAdmin' => $isSuperAdmin,
            'tenants' => $tenants,
            'delayBreakdowns' => $this->delayBreakdownService->getDelayBreakdown($startDate, $endDate),
            'rejectionBreakdowns' => $this->rejectionBreakdownService->getRejectionBreakdown($startDate, $endDate),
            'maintenanceBreakdowns' => $maintenaceBreakdown,
            'dateFilter' => $dateFilter,
            'dateRange' => $dateRange,
            // 'driversOverallPerformance' => $driverOverAll,
            'permissions' => $permissions,
            'milesDriven' => $milesDriven,
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
        $dayOfYear = $date->dayOfYear;

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
        if (!($startDate instanceof Carbon)) {
            $startDate = Carbon::parse($startDate);
        }

        if (!($endDate instanceof Carbon)) {
            $endDate = Carbon::parse($endDate);
        }

        /*
        |--------------------------------------------------------------------------
        | DRIVERS
        |--------------------------------------------------------------------------
        */

        $driversQuery = DB::table('drivers')
            ->select('first_name', 'last_name', 'netradyne_user_name');

        $this->applyTenantFilter($driversQuery);

        $drivers = $driversQuery->get();

        if ($drivers->isEmpty()) {
            return ['drivers' => []];
        }

        /*
        |--------------------------------------------------------------------------
        | BUILD DRIVER NAME MAP
        |--------------------------------------------------------------------------
        */

        $driverNames = [];
        $usernameMap = [];

        foreach ($drivers as $driver) {
            $name = strtolower(trim($driver->first_name . ' ' . $driver->last_name));
            $driverNames[] = $name;

            if ($driver->netradyne_user_name) {
                $usernameMap[$driver->netradyne_user_name] = $name;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | SAFETY DATA (AGGREGATED)
        |--------------------------------------------------------------------------
        */

        $safetyQuery = DB::table('safety_data')
            ->whereBetween('date', [$startDate, $endDate])
            ->selectRaw("
            LOWER(driver_name) as driver_name,
            user_name,

            AVG(driver_score) as safety_score,
            SUM(minutes_analyzed) as minutes_analyzed,

            SUM(traffic_light_violation) as traffic_light_violation,
            SUM(speeding_violations) as speeding_violations,
            SUM(following_distance) as following_distance,
            SUM(roadside_parking) as roadside_parking,
            SUM(driver_distraction) as driver_distraction,
            SUM(sign_violations) as sign_violations
        ")
            ->groupBy('driver_name', 'user_name');

        $this->applyTenantFilter($safetyQuery);

        $safetyRows = $safetyQuery->get();

        $safetyMap = [];

        foreach ($safetyRows as $row) {

            $key = strtolower($row->driver_name);

            if (!$key && $row->user_name && isset($usernameMap[$row->user_name])) {
                $key = $usernameMap[$row->user_name];
            }

            if (!$key)
                continue;

            if (!isset($safetyMap[$key])) {
                $safetyMap[$key] = [
                    'safety_score' => 0,
                    'minutes_analyzed' => 0,
                    'traffic_light_violation' => 0,
                    'speeding_violations' => 0,
                    'following_distance' => 0,
                    'roadside_parking' => 0,
                    'driver_distraction' => 0,
                    'sign_violations' => 0
                ];
            }

            $safetyMap[$key]['safety_score'] += $row->safety_score ?? 0;
            $safetyMap[$key]['minutes_analyzed'] += $row->minutes_analyzed ?? 0;

            $safetyMap[$key]['traffic_light_violation'] += $row->traffic_light_violation ?? 0;
            $safetyMap[$key]['speeding_violations'] += $row->speeding_violations ?? 0;
            $safetyMap[$key]['following_distance'] += $row->following_distance ?? 0;
            $safetyMap[$key]['roadside_parking'] += $row->roadside_parking ?? 0;
            $safetyMap[$key]['driver_distraction'] += $row->driver_distraction ?? 0;
            $safetyMap[$key]['sign_violations'] += $row->sign_violations ?? 0;
        }

        /*
        |--------------------------------------------------------------------------
        | DELAY DATA (AGGREGATED)
        |--------------------------------------------------------------------------
        */

        $delayQuery = DB::table('delays')
            ->whereBetween('date', [$startDate, $endDate])
            ->selectRaw("
            LOWER(driver_name) as driver_name,

            SUM(CASE WHEN delay_type='origin' AND driver_controllable=1 THEN penalty ELSE 0 END) as origin_penalty,
            SUM(CASE WHEN delay_type='destination' AND driver_controllable=1 THEN penalty ELSE 0 END) as destination_penalty,

            SUM(CASE WHEN delay_type='origin' THEN 1 ELSE 0 END) as origin_count,
            SUM(CASE WHEN delay_type='destination' THEN 1 ELSE 0 END) as destination_count
        ")
            ->groupBy('driver_name');

        $this->applyTenantFilter($delayQuery);

        $delayRows = $delayQuery->get()->keyBy('driver_name');

        /*
        |--------------------------------------------------------------------------
        | REJECTION DATA (AGGREGATED)
        |--------------------------------------------------------------------------
        */

        $rejectionQuery = DB::table('rejections')
            ->join(DB::raw('(
            SELECT rejection_id, LOWER(driver_name) as driver_name FROM rejected_loads
            UNION
            SELECT rejection_id, LOWER(driver_name) as driver_name FROM rejected_blocks
        ) driver_rejections'), 'rejections.id', '=', 'driver_rejections.rejection_id')
            ->whereBetween('rejections.date', [$startDate, $endDate])
            ->selectRaw("
            driver_rejections.driver_name,

            COUNT(*) as total_entries,
            SUM(CASE WHEN rejections.driver_controllable=1 THEN rejections.penalty ELSE 0 END) as driver_penalty
        ")
            ->groupBy('driver_rejections.driver_name');

        $this->applyTenantFilter($rejectionQuery);

        $rejectionRows = $rejectionQuery->get()->keyBy('driver_name');

        /*
        |--------------------------------------------------------------------------
        | BUILD FINAL DRIVER SCORES
        |--------------------------------------------------------------------------
        */

        $driversOverallScores = [];

        foreach ($drivers as $driver) {

            $driverName = trim($driver->first_name . ' ' . $driver->last_name);
            $key = strtolower($driverName);

            $safety = $safetyMap[$key] ?? null;

            if (!$safety || $safety['minutes_analyzed'] == 0) {
                continue;
            }

            $delay = $delayRows[$key] ?? null;
            $rejection = $rejectionRows[$key] ?? null;

            /*
            |--------------------------------------------------------------------------
            | ACCEPTANCE SCORE
            |--------------------------------------------------------------------------
            */

            $entries = $rejection->total_entries ?? 0;
            $rejectionPenalties = $rejection->driver_penalty ?? 0;

            $acceptanceScore = $entries > 0
                ? (1 - ($rejectionPenalties / $entries)) * 100
                : 100;

            /*
            |--------------------------------------------------------------------------
            | ON-TIME SCORE
            |--------------------------------------------------------------------------
            */

            $originCount = $delay->origin_count ?? 0;
            $destinationCount = $delay->destination_count ?? 0;

            $originPenalty = $delay->origin_penalty ?? 0;
            $destinationPenalty = $delay->destination_penalty ?? 0;

            $delayPenalties = $originPenalty + $destinationPenalty;

            $originPerformance = $originCount > 0
                ? (1 - ($originPenalty / $originCount)) * 100
                : 100;

            $destinationPerformance = $destinationCount > 0
                ? (1 - ($destinationPenalty / $destinationCount)) * 100
                : 100;

            $onTimeScore = ($originPerformance * 0.375) + ($destinationPerformance * 0.625);

            /*
            |--------------------------------------------------------------------------
            | SAFETY SCORE
            |--------------------------------------------------------------------------
            */

            $rawSafetyScore = $safety['safety_score'];
            $minutesAnalyzed = $safety['minutes_analyzed'];

            $safetyScoreNormalized = $rawSafetyScore * 100 / 1050;

            /*
            |--------------------------------------------------------------------------
            | OVERALL SCORE
            |--------------------------------------------------------------------------
            */

            $overallScore = ($acceptanceScore + $onTimeScore + $safetyScoreNormalized) / 3;

            /*
            |--------------------------------------------------------------------------
            | RESULT
            |--------------------------------------------------------------------------
            */

            $driversOverallScores[] = [
                'driver_name' => $driverName,

                'acceptance_score' => round($acceptanceScore, 2),
                'on_time_score' => round($onTimeScore, 2),
                'safety_score' => round($safetyScoreNormalized, 2),

                'overall_score' => round($overallScore, 2),

                'raw_safety_score' => round($rawSafetyScore, 2),

                'rejection_penalties' => $rejectionPenalties,
                'delay_penalties' => $delayPenalties,

                'minutes_analyzed' => $minutesAnalyzed,

                'traffic_light_violation' => $safety['traffic_light_violation'],
                'speeding_violations' => $safety['speeding_violations'],
                'following_distance' => $safety['following_distance'],
                'roadside_parking' => $safety['roadside_parking'],
                'driver_distraction' => $safety['driver_distraction'],
                'sign_violations' => $safety['sign_violations'],
            ];
        }

        usort($driversOverallScores, fn($a, $b) => $b['overall_score'] <=> $a['overall_score']);

        return [
            'drivers' => $driversOverallScores
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
    private function resolveDateRange($dateFilter): array
    {
        $now = Carbon::now();
        $isSunday = $now->dayOfWeek === 0;

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

                $endDate = $startDate->copy()->addDays(6)->endOfDay();
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
        }

        $year = $startDate->year;

        if (in_array($dateFilter, ['yesterday', 'current-week'])) {

            $weekNumber = $this->weekNumberSundayStart($startDate);

            $startWeekNumber = null;
            $endWeekNumber = null;

        } else {

            $weekNumber = null;

            $startWeekNumber = $this->weekNumberSundayStart($startDate);
            $endWeekNumber = $this->weekNumberSundayStart($endDate);
        }

        return [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'dateRange' => [
                'start' => $startDate->toDateString(),
                'end' => $endDate->toDateString(),
                'label' => $label,
                'weekNumber' => $weekNumber,
                'startWeekNumber' => $startWeekNumber,
                'endWeekNumber' => $endWeekNumber,
                'year' => $year
            ]
        ];
    }
    public function getDriverScorecardData($dateFilter): array
    {
        $dateFilter = $dateFilter ?? 'yesterday';

        $range = $this->resolveDateRange($dateFilter);

        $startDate = $range['startDate'];
        $endDate = $range['endDate'];
        $dateRange = $range['dateRange'];

        $drivers = $this->getDriversOverallPerformance($startDate, $endDate);

        $isSuperAdmin = Auth::check() && is_null(Auth::user()->tenant_id);

        $tenantSlug = $isSuperAdmin
            ? null
            : (Auth::check() ? Auth::user()->tenant->slug : null);

        $permissions = Auth::user()->getAllPermissions();

        return [

            'driversOverallPerformance' => $drivers,

            'dateFilter' => $dateFilter,

            'dateRange' => $dateRange,

            'tenantSlug' => $tenantSlug,

            'permissions' => $permissions
        ];
    }

}
