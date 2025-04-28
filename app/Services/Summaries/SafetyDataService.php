<?php

namespace App\Services\Summaries;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\PerformanceMetricRule;

class SafetyDataService
{
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
                SUM(collision_warning) AS collision_warning,
                SUM(backing) AS backing,
                SUM(roadside_parking) AS roadside_parking,
                SUM(high_g) AS high_g
            ")
            ->whereBetween('date', [$startDate, $endDate]);

        $this->applyTenantFilter($query);
        return $query->first();
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
                'followingDistance' => $aggregateSafetyData->following_distance ?? 0
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
                'collisionWarning' => $infractionsData->collision_warning ?? 0,
                'backing' => $infractionsData->backing ?? 0,
                'roadsideParking' => $infractionsData->roadside_parking ?? 0,
                'highG' => $infractionsData->high_g ?? 0
            ]
        ];
    }
}
