<?php

namespace App\Services\Summaries;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\PerformanceMetricRule;
use Carbon\Carbon;

class SafetyDataService
{

    protected ?int $email_tenant_id;

public function __construct(?int $email_tenant_id = null) {
    $this->email_tenant_id = $email_tenant_id;
}
    /**
     * Get aggregate safety data for the specified date range
     */
    public function getAggregateSafetyData($startDate, $endDate)
    {
        $query = DB::table('safety_data')
            ->selectRaw("
                SUM(traffic_light_violation) AS traffic_light_violation,
                SUM(speeding_violations) AS speeding_violations,
                SUM(following_distance) AS following_distance,
                SUM(roadside_parking) AS roadside_parking,
                SUM(driver_distraction) AS driver_distraction,
                SUM(sign_violations) AS sign_violations,
                SUM(minutes_analyzed) AS total_minutes_analyzed,
                AVG(driver_score) AS average_driver_score
            ")
            ->whereBetween('date', [$startDate, $endDate]);

        $this->applyTenantFilter($query);
        return $query->first();
    }

    /**
     * Calculate violation rates per 1000 hours
     */
    public function calculateViolationRates($safetyData, $totalHours): array
    {
        return [
            'traffic_light_violation' => $totalHours > 0 ? ($safetyData->traffic_light_violation ?? 0) / $totalHours * 1000 : 0,
            'speeding_violations' => $totalHours > 0 ? ($safetyData->speeding_violations ?? 0) / $totalHours * 1000 : 0,
            'following_distance' => $totalHours > 0 ? ($safetyData->following_distance ?? 0) / $totalHours * 1000 : 0,
            'driver_distraction' => $totalHours > 0 ? ($safetyData->driver_distraction ?? 0) / $totalHours * 1000 : 0,
            'sign_violations' => $totalHours > 0 ? ($safetyData->sign_violations ?? 0) / $totalHours * 1000 : 0,
            'roadside_parking' => $totalHours > 0 ? ($safetyData->roadside_parking ?? 0) / $totalHours * 1000 : 0,
        ];
    }

    /**
     * Get top drivers by score
     */
    public function getTopDrivers($startDate, $endDate, $limit = 5)
    {
        $query = $this->getDriverScoreQuery($startDate, $endDate)
            ->orderBy('average_score', 'desc')
            ->limit($limit);

        return $this->mapDriverResults($query->get());
    }

    /**
     * Get bottom drivers by score
     */
    public function getBottomDrivers($startDate, $endDate, $limit = 5)
    {
        $query = $this->getDriverScoreQuery($startDate, $endDate)
            ->where('minutes_analyzed', '>', 0)
            ->orderBy('average_score', 'asc')
            ->limit($limit);

        return $this->mapDriverResults($query->get());
    }

    /**
     * Get base query for driver scores
     */
    public function getDriverScoreQuery($startDate, $endDate)
    {
        $query = DB::table('safety_data')
            ->select('driver_name', DB::raw('AVG(driver_score) as average_score'))
            ->whereBetween('date', [$startDate, $endDate])
            ->whereNotNull('driver_name')
            ->whereNotNull('driver_score')
            ->where('driver_name', '!=', '');

        $this->applyTenantFilter($query);
        return $query->groupBy('driver_name');
    }

    /**
     * Map driver query results to formatted array
     */
    public function mapDriverResults($drivers)
    {
        return $drivers->map(function($driver, $index) {
            return [
                'name' => $driver->driver_name,
                'score' => round($driver->average_score, 1),
                'rank' => $index + 1
            ];
        });
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
     * Calculate safety ratings for all violation types
     */
    public function calculateSafetyRatings($rates, $rules): array
    {
        return [
            'traffic_light_violation' => $this->getSafetyRating($rates['traffic_light_violation'], $rules, 'traffic_light_violation'),
            'speeding_violations' => $this->getSafetyRating($rates['speeding_violations'], $rules, 'speeding_violation'),
            'following_distance' => $this->getSafetyRating($rates['following_distance'], $rules, 'following_distance'),
            'driver_distraction' => $this->getSafetyRating($rates['driver_distraction'], $rules, 'driver_distraction'),
            'sign_violations' => $this->getSafetyRating($rates['sign_violations'], $rules, 'sign_violation'),
            'roadside_parking' => $this->getSafetyRating($rates['roadside_parking'], $rules, 'roadside_parking'),
        ];
    }

    /**
     * Get complete safety data for the specified date range
     */
    public function getSafetyData($startDate, $endDate): array
    {
        $safetyData = $this->getAggregateSafetyData($startDate, $endDate);
        $totalHours = ($safetyData->total_minutes_analyzed ?? 0) / 60;
        $rules = PerformanceMetricRule::first();
        
        $rates = $this->calculateViolationRates($safetyData, $totalHours);
        $ratings = $this->calculateSafetyRatings($rates, $rules);
        
        $topDrivers = $this->getTopDrivers($startDate, $endDate);
        $bottomDrivers = $this->getBottomDrivers($startDate, $endDate);

        return [
            'traffic_light_violation' => $safetyData->traffic_light_violation ?? 0,
            'speeding_violations' => $safetyData->speeding_violations ?? 0,
            'following_distance' => $safetyData->following_distance ?? 0,
            'roadside_parking' => $safetyData->roadside_parking ?? 0,
            'driver_distraction' => $safetyData->driver_distraction ?? 0,
            'sign_violations' => $safetyData->sign_violations ?? 0,
            'average_driver_score' => $safetyData->average_driver_score ?? 0,
            'total_minutes_analyzed' => $safetyData->total_minutes_analyzed ?? 0,
            'total_hours' => $totalHours,
            'top_drivers' => $topDrivers,
            'bottom_drivers' => $bottomDrivers,
            'rates' => $rates,
            'ratings' => $ratings,
        ];
    }

    /**
     * Calculate safety rating based on violation rate and metric rules
     *
     * @param float $rate The violation rate per 1000 hours
     * @param PerformanceMetricRule $rules The performance metric rules
     * @param string $metricPrefix The prefix for the metric in the rules table
     * @return string The safety rating (gold, silver, or not_eligible)
     */
    public function getSafetyRating($rate, $rules, $metricPrefix): string
    {
        if (!$rules) {
            return 'not_eligible';
        }

        // Check gold tier
        $goldThreshold = $rules->{"{$metricPrefix}_gold"} ?? null;
        $goldOperator = $rules->{"{$metricPrefix}_gold_operator"} ?? null;
        
        if ($goldThreshold !== null && $goldOperator && $this->compareValues($rate, $goldThreshold, $goldOperator)) {
            return 'gold';
        }

        // Check silver tier
        $silverThreshold = $rules->{"{$metricPrefix}_silver"} ?? null;
        $silverOperator = $rules->{"{$metricPrefix}_silver_operator"} ?? null;
        
        if ($silverThreshold !== null && $silverOperator && $this->compareValues($rate, $silverThreshold, $silverOperator)) {
            return 'silver';
        }

        // Default to not eligible
        return 'not_eligible';
    }

    /**
     * Compare values using the specified operator
     *
     * @param float $value The value to compare
     * @param float $threshold The threshold to compare against
     * @param string $operator The comparison operator
     * @return bool Whether the comparison is true
     */
    public function compareValues($value, $threshold, $operator): bool
    {
        return match ($operator) {
            'less'          => $value < $threshold,
            'less_or_equal' => $value <= $threshold,
            'equal'         => $value == $threshold,
            'more_or_equal' => $value >= $threshold,
            'more'          => $value > $threshold,
            default         => false,
        };
    }

    /**
     * Get infractions data from the database
     * 
     * @param string $startDate The start date for the query
     * @param string $endDate The end date for the query
     * @return object The infractions data
     */
    public function getInfractionsData($startDate, $endDate)
    {
        $query = DB::table('safety_data')
            ->selectRaw("
                SUM(driver_star) AS driver_star,
                SUM(potential_collision) AS potential_collision,
                SUM(hard_braking) AS hard_braking,
                SUM(hard_turn) AS hard_turn,
                SUM(hard_acceleration) AS hard_acceleration,
                SUM(u_turn) AS u_turn,
                SUM(seatbelt_compliance) AS seatbelt_compliance,
                SUM(camera_obstruction) AS camera_obstruction,
                SUM(driver_drowsiness) AS driver_drowsiness,
                SUM(weaving) AS weaving,
                SUM(swerve) AS swerve,
                SUM(lane_conduct) AS lane_conduct,
                SUM(collision_warning) AS collision_warning,
                SUM(backing) AS backing,
                SUM(high_g) AS high_g
            ")
            ->whereBetween('date', [$startDate, $endDate]);

        $this->applyTenantFilter($query);
        return $query->first();
    }

    /**
     * Get line chart data for safety trends
     * 
     * @param string $startDate The start date for the query
     * @param string $endDate The end date for the query
     * @return array The line chart data
     */
    public function getLineChartData($startDate, $endDate): array
    {
        // Use Carbon for consistent date handling with FilteringService
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        
        // Determine date filter type based on date range
        $dateFilter = $this->determineDateFilterType($start, $end);
        // Determine grouping based on date filter type
        if ($dateFilter === 'yesterday') {
            // For yesterday, we'll show hourly data if available
            $dateFormat = 'Y-m-d';
            $groupBy = DB::raw('DATE_FORMAT(date, "%Y-%m-%d")');
            $labelFormat = 'H:00'; // Hour format
        } elseif ($dateFilter === 'current-week') {
            // Current week - group by day
            $dateFormat = 'Y-m-d';
            $groupBy = DB::raw('DATE(date)');
            $labelFormat = 'D'; // Day name (Mon, Tue, etc.)
        } elseif ($dateFilter === '6w') {
            // 6 weeks - group by week with weeks starting on Sunday
            $dateFormat = 'Y-W';
            // Use YEARWEEK with mode 0 (weeks starting on Sunday)
            $groupBy = DB::raw('YEARWEEK(date, 6)');
            $labelFormat = '\WW'; // Week number (W1, W2, etc.)
        } else {
            // Quarterly or longer - group by month
            $dateFormat = 'Y-m';
            $groupBy = DB::raw('DATE_FORMAT(date, "%Y-%m")');
            $labelFormat = 'M'; // Month name (Jan, Feb, etc.)
        }
        $query = DB::table('safety_data')
            ->select($groupBy, DB::raw('AVG(driver_score) as greenZoneScore'))
            ->whereBetween('date', [$startDate, $endDate])
            ->groupBy($groupBy)
            ->orderBy($groupBy);
        
        $this->applyTenantFilter($query);
        $results = $query->get();
        // Format dates based on the determined grouping
        return $results->map(function($item) use ($dateFormat, $labelFormat, $dateFilter) {
            // Get the first property (date or yearweek)
            $dateValue = $item->{array_key_first((array)$item)};
            
            if ($dateFormat === 'Y-m-d') {
                // For daily grouping
                $date = Carbon::parse($dateValue);
                $formattedDate = $date->format($labelFormat);
            } elseif ($dateFormat === 'Y-m-d') {
                // For hourly grouping
                $date = Carbon::parse($dateValue);
                $formattedDate = $date->format($labelFormat);
            } elseif ($dateFormat === 'Y-m') {
                // For monthly grouping
                $date = Carbon::parse($dateValue . '-01');
                $formattedDate = $date->format($labelFormat);
            } else {
                // For weekly grouping
                // Extract year and week from YEARWEEK format (YYYYWW)
                $year = substr($dateValue, 0, 4);
                $week = substr($dateValue, 4);
                $formattedDate = 'W' . $week;
            }
            
            return [
                'date' => $formattedDate,
                'greenZoneScore' => round($item->greenZoneScore, 1)
            ];
        })->toArray();
    }
    
    /**
     * Determine the date filter type based on the date range
     * 
     * @param Carbon $start The start date
     * @param Carbon $end The end date
     * @return string The date filter type (yesterday, current-week, 6w, quarterly, or full)
     */
    private function determineDateFilterType(Carbon $start, Carbon $end): string
    {
        $daysDifference = $start->diffInDays($end);
        $now = Carbon::now();
        $isSunday = $now->dayOfWeek() === 0;
        if ($isSunday) {
            $now = $now->subDay();
        }
        $yesterday = Carbon::yesterday();
        $currentWeekStart = $now->copy()->startOfWeek(Carbon::SUNDAY);
        $currentWeekEnd = $now->copy()->endOfWeek(Carbon::SATURDAY);
        $sixWeeksStart = $currentWeekStart->copy()->subWeeks(5);
        
        // Check if the date range matches yesterday
        if ($start->isSameDay($yesterday) && $end->isSameDay($yesterday)) {
            return 'yesterday';
        }
        
        // Check if the date range matches current week
        if ($start->isSameDay($currentWeekStart) && $end->isSameDay($currentWeekEnd)) {
            return 'current-week';
        }
        
        // Check if the date range matches 6 weeks
        if ($start->isSameDay($sixWeeksStart) && $end->isSameDay($currentWeekEnd)) {
            return '6w';
        }
        
        // Check if the date range is approximately 3 months
        if ($daysDifference >= 85 && $daysDifference <= 95) {
            return 'quarterly';
        }
        
        // Default to full if none of the above match
        return 'full';
    }

    /**
     * Get formatted safety data for the frontend in the same structure as the hardcoded values
     * 
     * @param string $startDate The start date for the query
     * @param string $endDate The end date for the query
     * @return array The formatted safety data
     */
    public function getFormattedSafetyData($startDate, $endDate): array
    {
        // Get top and bottom drivers
        $topDrivers = $this->getTopDrivers($startDate, $endDate);
        $bottomDrivers = $this->getBottomDrivers($startDate, $endDate);
        
        // Get aggregate safety data for alerts
        $aggregateSafetyData = $this->getAggregateSafetyData($startDate, $endDate);
        
        // Get infractions data
        $infractionsData = $this->getInfractionsData($startDate, $endDate);
        
        // Get line chart data
        $lineChartData = $this->getLineChartData($startDate, $endDate);
        
        // Format the data according to the structure in Safety.vue
        return [
            'greenZoneScore' => $aggregateSafetyData->average_driver_score ?? 0,
            'topDrivers' => $topDrivers,
            'bottomDrivers' => $bottomDrivers,
            'alerts' => [
                'distractedDriving' => $aggregateSafetyData->driver_distraction ?? 0,
                'speeding' => $aggregateSafetyData->speeding_violations ?? 0,
                'signViolation' => $aggregateSafetyData->sign_violations ?? 0,
                'trafficLightViolation' => $aggregateSafetyData->traffic_light_violation ?? 0,
                'followingDistance' => $aggregateSafetyData->following_distance ?? 0,
                'roadsideParking' => $aggregateSafetyData->roadside_parking ?? 0,
            ],
            'infractions' => [
                'driverStar' => $infractionsData->driver_star ?? 0,
                'potentialCollision' => $infractionsData->potential_collision ?? 0,
                'hardBraking' => $infractionsData->hard_braking ?? 0,
                'hardTurn' => $infractionsData->hard_turn ?? 0,
                'hardAcceleration' => $infractionsData->hard_acceleration ?? 0,
                'uTurn' => $infractionsData->u_turn ?? 0,
                'seatbeltCompliance' => $infractionsData->seatbelt_compliance ?? 0,
                'cameraObstruction' => $infractionsData->camera_obstruction ?? 0,
                'driverDrowsiness' => $infractionsData->driver_drowsiness ?? 0,
                'weaving' => $infractionsData->weaving ?? 0,
                'swerve' => $infractionsData->swerve ?? 0,
                'laneConduct' => $infractionsData->lane_conduct ?? 0,
                'collisionWarning' => $infractionsData->collision_warning ?? 0,
                'backing' => $infractionsData->backing ?? 0,
                'highG' => $infractionsData->high_g ?? 0
            ],
            'lineChartData' => $lineChartData
        ];
    }
}
