<?php

namespace App\Services\Safety;

use App\Models\SafetyData;
use App\Models\SafetyThreshold;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class SafetyThresholdService
{
    /**
     * Get all safety thresholds for the current tenant.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllThresholds()
    {
        $tenantId = Auth::user()->tenant_id;
        return SafetyThreshold::where('tenant_id', $tenantId)->get();
    }

    /**
     * Get threshold for a specific metric.
     *
     * @param string $metricName
     * @return SafetyThreshold|null
     */
    public function getThresholdForMetric($metricName)
    {
        $tenantId = Auth::user()->tenant_id;
        return SafetyThreshold::where('tenant_id', $tenantId)
            ->where('metric_name', $metricName)
            ->first();
    }

    /**
     * Create or update a threshold for a metric.
     *
     * @param array $data
     * @return SafetyThreshold
     */
    public function createOrUpdateThreshold(array $data)
    {
        $tenantId = Auth::user()->tenant_id ?? $data['tenant_id'];
        
        return SafetyThreshold::updateOrCreate(
            [
                'tenant_id' => $tenantId,
                'metric_name' => $data['metric_name'],
            ],
            [
                'good_threshold' => $data['good_threshold'] ?? null,
                'bad_threshold' => $data['bad_threshold'] ?? null,
                'good_enabled' => $data['good_enabled'] ?? true,
                'bad_enabled' => $data['bad_enabled'] ?? true,
            ]
        );
    }

    /**
     * Delete a threshold.
     *
     * @param int $id
     * @return bool
     */
    public function deleteThreshold($id)
    {
        $threshold = SafetyThreshold::findOrFail($id);
        return $threshold->delete();
    }

    /**
     * Get all available safety metrics from the SafetyData model.
     *
     * @return array
     */
    public function getAvailableSafetyMetrics()
    {
        // Get all fillable fields from the SafetyData model
        $safetyData = new SafetyData();
        $fillable = $safetyData->getFillable();
        
        // Filter out non-metric fields and impact metrics
        $excludedFields = ['tenant_id', 'driver_name', 'user_name', 'group', 'group_hierarchy', 'date'];
        
        return array_values(array_filter($fillable, function($field) use ($excludedFields) {
            // Exclude both non-metric fields and any field containing "impact"
            return !in_array($field, $excludedFields) && !str_contains($field, 'impact');
        }));
    }

    /**
     * Evaluate a safety metric against its thresholds.
     *
     * @param string $metricName
     * @param float $value
     * @return array
     */
    public function evaluateMetric($metricName, $value)
    {
        $threshold = $this->getThresholdForMetric($metricName);
        
        if (!$threshold) {
            return [
                'status' => 'neutral',
                'good_threshold' => null,
                'bad_threshold' => null,
            ];
        }
        
        $status = 'neutral';
        
        if ($threshold->good_enabled && $threshold->good_threshold !== null) {
            if ($value <= $threshold->good_threshold) {
                $status = 'good';
            }
        }
        
        if ($threshold->bad_enabled && $threshold->bad_threshold !== null) {
            if ($value >= $threshold->bad_threshold) {
                $status = 'bad';
            }
        }
        
        return [
            'status' => $status,
            'good_threshold' => $threshold->good_threshold,
            'bad_threshold' => $threshold->bad_threshold,
        ];
    }
}