<?php

namespace App\Services\Performance;

use App\Models\PerformanceMetricRule;
class PerformanceCalculationsService{
    
    /**
     * Compare a value to a threshold using a specified operator.
     *
     * @param mixed $value
     * @param mixed $threshold
     * @param string $operator
     * @return bool
     */
    public function compare($value, $threshold, $operator)
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
     * Calculate a rating based on a given value and metric rule.
     *
     * @param mixed $value
     * @param PerformanceMetricRule $rule
     * @param string $prefix
     * @return string|null
     */
    public function getRating($value, $rule, $prefix)
    {
        $levels = ['fantastic_plus', 'fantastic', 'good', 'fair', 'poor'];
        foreach ($levels as $level) {
            $threshold = $rule["{$prefix}_{$level}"];
            $operator = $rule["{$prefix}_{$level}_operator"];
            if ($this->compare($value, $threshold, $operator)) {
                return $level;
            }
        }
        return null;
    }

    /**
     * Determine the safety bonus rating.
     *
     * @param mixed $isEligible
     * @param PerformanceMetricRule $rule
     * @return string
     */
    public function getSafetyBonusRating($isEligible, $rule): string
    {
        $levels = ['fantastic_plus', 'fantastic', 'good', 'fair', 'poor'];
        $eligibleLevels = $rule->safety_bonus_eligible_levels ?? [];
        return $isEligible
            ? (collect($levels)->first(fn($level) => in_array($level, $eligibleLevels)) ?? 'poor')
            : 'poor';
    }

}