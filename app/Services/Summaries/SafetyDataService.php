<?php

namespace App\Services\Summaries;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\PerformanceMetricRule;

class SafetyDataService
{
    public function getSafetyData($startDate, $endDate): array
    {
        $query = DB::table('safety_data')
            ->selectRaw("
                SUM(traffic_light_violation) AS traffic_light_violation,
                SUM(speeding_violations) AS speeding_violations,
                SUM(following_distance_hard_brake) AS following_distance_hard_brake,
                SUM(driver_distraction) AS driver_distraction,
                SUM(sign_violations) AS sign_violations,
                SUM(minutes_analyzed) AS total_minutes_analyzed,
                AVG(driver_score) AS average_driver_score
            ")
            ->whereBetween('date', [$startDate, $endDate]);

        if (Auth::check() && Auth::user()->tenant_id !== null) {
            $query->where('tenant_id', Auth::user()->tenant_id);
        }

        $safetyData = $query->first();
        $totalHours = ($safetyData->total_minutes_analyzed ?? 0) / 60;

        // Calculate violation rates per 1000 hours
        $trafficLightRate = $totalHours > 0 ? ($safetyData->traffic_light_violation ?? 0) / $totalHours * 1000 : 0;
        $speedingRate = $totalHours > 0 ? ($safetyData->speeding_violations ?? 0) / $totalHours * 1000 : 0;
        $followingDistanceRate = $totalHours > 0 ? ($safetyData->following_distance_hard_brake ?? 0) / $totalHours * 1000 : 0;
        $distractionRate = $totalHours > 0 ? ($safetyData->driver_distraction ?? 0) / $totalHours * 1000 : 0;
        $signViolationRate = $totalHours > 0 ? ($safetyData->sign_violations ?? 0) / $totalHours * 1000 : 0;

        // Get the performance metric rules
        $rules = PerformanceMetricRule::first();

        return [
            'traffic_light_violation'       => $safetyData->traffic_light_violation ?? 0,
            'speeding_violations'           => $safetyData->speeding_violations ?? 0,
            'following_distance_hard_brake' => $safetyData->following_distance_hard_brake ?? 0,
            'driver_distraction'            => $safetyData->driver_distraction ?? 0,
            'sign_violations'               => $safetyData->sign_violations ?? 0,
            'average_driver_score'          => $safetyData->average_driver_score ?? 0,
            'total_minutes_analyzed'        => $safetyData->total_minutes_analyzed ?? 0,
            'total_hours'                   => $totalHours,
            'rates' => [
                'traffic_light_violation'       => $trafficLightRate,
                'speeding_violations'           => $speedingRate,
                'following_distance_hard_brake' => $followingDistanceRate,
                'driver_distraction'            => $distractionRate,
                'sign_violations'               => $signViolationRate,
            ],
            'ratings' => [
                'traffic_light_violation'       => $this->getSafetyRating($trafficLightRate, $rules, 'traffic_light_violation'),
                'speeding_violations'           => $this->getSafetyRating($speedingRate, $rules, 'speeding_violation'),
                'following_distance'            => $this->getSafetyRating($followingDistanceRate, $rules, 'following_distance'),
                'driver_distraction'            => $this->getSafetyRating($distractionRate, $rules, 'driver_distraction'),
                'sign_violations'               => $this->getSafetyRating($signViolationRate, $rules, 'sign_violation'),
            ],
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
    private function getSafetyRating($rate, $rules, $metricPrefix): string
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
    private function compareValues($value, $threshold, $operator): bool
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
}
