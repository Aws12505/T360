<?php

namespace App\Services\SMSCoaching;

class SmsCoachingMetrics
{
    public const STATUS_GOOD = 'good';
    public const STATUS_BAD = 'bad';
    public const STATUS_MINOR = 'minor_improvement';

    public const METRIC_GENERAL = 'general';

    private const METRICS = [
        self::METRIC_GENERAL => [
            'label' => 'General',
            'type' => 'general',
            'thresholds' => false,
            'direction' => null,
            'driver_key' => null,
        ],
        'acceptance' => [
            'label' => 'Acceptance',
            'type' => 'score',
            'thresholds' => true,
            'direction' => 'high',
            'driver_key' => 'acceptance_score',
        ],
        'on_time' => [
            'label' => 'On-Time',
            'type' => 'score',
            'thresholds' => true,
            'direction' => 'high',
            'driver_key' => 'on_time_score',
        ],
        'greenzone' => [
            'label' => 'Green Zone',
            'type' => 'score',
            'thresholds' => true,
            'direction' => 'high',
            'driver_key' => 'safety_score',
        ],
        'traffic_light_violation' => [
            'label' => 'Traffic Light Violations',
            'type' => 'count',
            'thresholds' => true,
            'direction' => 'low',
            'driver_key' => 'traffic_light_violation',
        ],
        'speeding_violations' => [
            'label' => 'Speeding Violations',
            'type' => 'count',
            'thresholds' => true,
            'direction' => 'low',
            'driver_key' => 'speeding_violations',
        ],
        'following_distance' => [
            'label' => 'Following Distance',
            'type' => 'count',
            'thresholds' => true,
            'direction' => 'low',
            'driver_key' => 'following_distance',
        ],
        'roadside_parking' => [
            'label' => 'Roadside Parking',
            'type' => 'count',
            'thresholds' => true,
            'direction' => 'low',
            'driver_key' => 'roadside_parking',
        ],
        'driver_distraction' => [
            'label' => 'Driver Distraction',
            'type' => 'count',
            'thresholds' => true,
            'direction' => 'low',
            'driver_key' => 'driver_distraction',
        ],
        'sign_violations' => [
            'label' => 'Sign Violations',
            'type' => 'count',
            'thresholds' => true,
            'direction' => 'low',
            'driver_key' => 'sign_violations',
        ],
    ];

    public static function all(): array
    {
        return self::METRICS;
    }

    public static function keys(): array
    {
        return array_keys(self::METRICS);
    }

    public static function thresholdKeys(): array
    {
        return array_keys(array_filter(self::METRICS, fn($metric) => $metric['thresholds']));
    }

    public static function label(string $key): string
    {
        return self::METRICS[$key]['label'] ?? $key;
    }

    public static function driverKey(string $key): ?string
    {
        return self::METRICS[$key]['driver_key'] ?? null;
    }

    public static function direction(string $key): ?string
    {
        return self::METRICS[$key]['direction'] ?? null;
    }

    public static function isThresholdMetric(string $key): bool
    {
        return (self::METRICS[$key]['thresholds'] ?? false) === true;
    }
}
