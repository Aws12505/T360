<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\PerformanceMetricRule;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\Tenant;

class SummariesController extends Controller
{
    public function getSummaries()
    {
        $today = Carbon::now();

$currentWeekStart = $today->copy()->startOfWeek(Carbon::SUNDAY);
$currentWeekEnd = $today->copy()->endOfWeek(Carbon::SATURDAY);

$rollingStart = $currentWeekStart->copy()->subWeeks(5); // not 6!
$rollingEnd = $currentWeekEnd;

$summaries = [
    'yesterday' => $this->fetchMetrics(
        Carbon::yesterday()->startOfDay(),
        Carbon::yesterday()->endOfDay(),
        'yesterday'
    ),

    'current_week' => $this->fetchMetrics(
        $currentWeekStart->copy()->startOfDay(),
        $currentWeekEnd->copy()->endOfDay(),
        'current_week'
    ),

    'rolling_6_weeks' => $this->fetchMetrics(
        $rollingStart->copy()->startOfDay(),
        $rollingEnd->copy()->endOfDay(),
        'rolling_6_weeks'
    ),

    'quarterly' => $this->fetchMetrics(
        $today->copy()->subMonths(3)->startOfDay(),
        $today->copy()->endOfDay(),
        'quarterly'
    ),
];

        $isSuperAdmin = is_null(Auth::user()->tenant_id);
        $tenants = $isSuperAdmin ? Tenant::all() : [];
        $tenantSlug = $isSuperAdmin ? null : Auth::user()->tenant->slug;

        // ✅ Debug response to inspect data being sent to Vue
       // return response()->json([
       //     'summaries' => $summaries,
       //     'tenantSlug' => $tenantSlug,
       //     'SuperAdmin' => $isSuperAdmin,
       //     'tenants' => $tenants,
       // ]);
//dd($summaries);
        // 🔁 When done debugging, switch to Inertia view rendering:
         return Inertia::render('Performance/Summary', [
             'summaries' => $summaries,
             'tenantSlug' => $tenantSlug,
             'SuperAdmin' => $isSuperAdmin,
             'tenants' => $tenants,
         ]);
    }

    private function fetchMetrics($startDate, $endDate, $label = '')
    {
        $rule = PerformanceMetricRule::first();
        $safetyBonusEligibleLevels = $rule->safety_bonus_eligible_levels ?? [];
    
        $rollingStart = Carbon::now()->subWeeks(6)->startOfWeek();
        $rollingEnd = Carbon::now();
    
        $mainData = DB::table('performances')
            ->selectRaw("
                AVG(NULLIF(acceptance, NULL)) AS average_acceptance,
                AVG(NULLIF(on_time, NULL)) AS average_on_time,
                AVG(NULLIF(maintenance_variance_to_spend, NULL)) AS average_maintenance_variance_to_spend,
                CASE
                    WHEN SUM(meets_safety_bonus_criteria) >= COUNT(meets_safety_bonus_criteria) / 2 THEN 1
                    ELSE 0
                END AS meets_safety_bonus_criteria
            ")
            ->whereBetween('date', [$startDate, $endDate])
            ->first();
    
        $rollingData = DB::table('performances')
            ->selectRaw("
                SUM(open_boc) AS sum_open_boc,
                SUM(vcr_preventable) AS sum_vcr_preventable
            ")
            ->whereBetween('date', [$rollingStart, $rollingEnd])
            ->first();
    
        // 👉 NEW safety data query
        $safetyData = DB::table('safety_data') // replace with actual table name
            ->selectRaw("
                SUM(traffic_light_violation) AS traffic_light_violation,
                SUM(speeding_violations) AS speeding_violations,
                SUM(following_distance_hard_brake) AS following_distance_hard_brake,
                SUM(driver_distraction) AS driver_distraction,
                SUM(sign_violations) AS sign_violations,
                AVG(driver_score) AS average_driver_score
            ")
            ->whereBetween('date', [$startDate, $endDate])
            ->first();
    
        $meetsCriteria = $mainData->meets_safety_bonus_criteria == 1;
        $safetyRating = $meetsCriteria
            ? (collect(['fantastic_plus', 'fantastic', 'good', 'fair', 'poor'])
                ->first(fn($lvl) => in_array($lvl, $safetyBonusEligibleLevels)) ?? 'poor')
            : 'poor';
    
        $ratings = [
            'average_acceptance' => $this->getRating($mainData->average_acceptance, $rule, 'acceptance'),
            'average_on_time' => $this->getRating($mainData->average_on_time, $rule, 'on_time'),
            'average_maintenance_variance_to_spend' => $this->getRating($mainData->average_maintenance_variance_to_spend, $rule, 'maintenance_variance'),
            'open_boc' => $this->getRating($rollingData->sum_open_boc, $rule, 'open_boc'),
            'vcr_preventable' => $this->getRating($rollingData->sum_vcr_preventable, $rule, 'vcr_preventable'),
            'meets_safety_bonus_criteria' => $safetyRating,
        ];
    
        return [
            'label' => $label,
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'data' => [
                'average_acceptance' => $mainData->average_acceptance ?? 0,
                'average_on_time' => $mainData->average_on_time ?? 0,
                'average_maintenance_variance_to_spend' => $mainData->average_maintenance_variance_to_spend ?? 0,
                'open_boc' => $rollingData->sum_open_boc ?? 0,
                'vcr_preventable' => $rollingData->sum_vcr_preventable ?? 0,
                'meets_safety_bonus_criteria' => $mainData->meets_safety_bonus_criteria ?? 0,
            ],
            'ratings' => $ratings,
            'safety_summary' => [
                'traffic_light_violation' => $safetyData->traffic_light_violation ?? 0,
                'speeding_violations' => $safetyData->speeding_violations ?? 0,
                'following_distance_hard_brake' => $safetyData->following_distance_hard_brake ?? 0,
                'driver_distraction' => $safetyData->driver_distraction ?? 0,
                'sign_violations' => $safetyData->sign_violations ?? 0,
                'average_driver_score' => $safetyData->average_driver_score ?? 0,
            ],
        ];
    }
    


    protected function getRating($value, $rule, $prefix)
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

    protected function compare($value, $threshold, $operator)
    {
        return match ($operator) {
            'less' => $value < $threshold,
            'less_or_equal' => $value <= $threshold,
            'equal' => $value == $threshold,
            'more_or_equal' => $value >= $threshold,
            'more' => $value > $threshold,
            default => false,
        };
    }
}
