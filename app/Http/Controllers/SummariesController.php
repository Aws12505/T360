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
$rejectionBreakdowns = [
    'yesterday' => $this->getRejectionBreakdown(Carbon::yesterday()->startOfDay(),
    Carbon::yesterday()->endOfDay()),
    'current_week' => $this->getRejectionBreakdown($currentWeekStart->copy()->startOfDay(),
    $currentWeekEnd->copy()->endOfDay()),
    'rolling_6_weeks' => $this->getRejectionBreakdown($rollingStart->copy()->startOfDay(),
    $rollingEnd->copy()->endOfDay()),
    'quarterly' => $this->getRejectionBreakdown($today->copy()->subMonths(3)->startOfDay(),
    $today->copy()->endOfDay()),
];
        $isSuperAdmin = is_null(Auth::user()->tenant_id);
        $tenants = $isSuperAdmin ? Tenant::all() : [];
        $tenantSlug = $isSuperAdmin ? null : Auth::user()->tenant->slug;
        $delayBreakdowns = [
            'yesterday' => $this->getDelayBreakdown(Carbon::yesterday()->startOfDay(),
            Carbon::yesterday()->endOfDay()),
            'current_week' => $this->getDelayBreakdown($currentWeekStart->copy()->startOfDay(),
            $currentWeekEnd->copy()->endOfDay()),
            'rolling_6_weeks' => $this->getDelayBreakdown($rollingStart->copy()->startOfDay(),
            $rollingEnd->copy()->endOfDay()),
            'quarterly' => $this->getDelayBreakdown($today->copy()->subMonths(3)->startOfDay(),
            $today->copy()->endOfDay()),
        ];
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
             'delayBreakdowns' => $delayBreakdowns,
             'tenants' => $tenants,
             'rejectionBreakdowns' => $rejectionBreakdowns,
         ]);
    }
    private function getDelayBreakdown($startDate, $endDate)
    {
        $filter = fn($q) =>
            $q->where(function ($q) {
                $q->whereNull('driver_controllable')->orWhere('driver_controllable', true);
            });

        $byDriver = DB::table('delays')
            ->selectRaw("
                driver_name,
                COUNT(*) as total_delays,
                SUM(penalty) as total_penalty,
                SUM(CASE WHEN delay_type = 'origin' THEN 1 ELSE 0 END) as total_origin_delays,
                SUM(CASE WHEN delay_type = 'origin' THEN penalty ELSE 0 END) as total_origin_penalty,
                SUM(CASE WHEN delay_type = 'destination' THEN 1 ELSE 0 END) as total_destination_delays,
                SUM(CASE WHEN delay_type = 'destination' THEN penalty ELSE 0 END) as total_destination_penalty
            ")
            ->whereBetween('date', [$startDate, $endDate])
            ->tap($filter)
            ->groupBy('driver_name')
            ->get();

        $byCode = DB::table('delays')
            ->join('delay_codes', 'delays.delay_code_id', '=', 'delay_codes.id')
            ->selectRaw("
                delay_codes.code,
                COUNT(*) as total_delays,
                SUM(delays.penalty) as total_penalty,
                SUM(CASE WHEN delays.delay_type = 'origin' THEN 1 ELSE 0 END) as total_origin_delays,
                SUM(CASE WHEN delays.delay_type = 'origin' THEN delays.penalty ELSE 0 END) as total_origin_penalty,
                SUM(CASE WHEN delays.delay_type = 'destination' THEN 1 ELSE 0 END) as total_destination_delays,
                SUM(CASE WHEN delays.delay_type = 'destination' THEN delays.penalty ELSE 0 END) as total_destination_penalty
            ")
            ->whereBetween('delays.date', [$startDate, $endDate])
            ->tap($filter)
            ->groupBy('delay_codes.code')
            ->get();

        return [
            'by_driver' => $byDriver,
            'by_code' => $byCode,
        ];
    }
    private function getRejectionBreakdown($startDate, $endDate)
    {
        // Only include where driver_controllable IS NULL or TRUE
        $driverFilter = fn($query) =>
            $query->where(function ($q) {
                $q->whereNull('driver_controllable')->orWhere('driver_controllable', true);
            });

        // ➤ By Driver
        $byDriver = DB::table('rejections')
            ->selectRaw("
                driver_name,
                COUNT(*) as total_rejections,
                SUM(penalty) as total_penalty,
                SUM(CASE WHEN rejection_type = 'block' THEN 1 ELSE 0 END) as total_block_rejections,
                SUM(CASE WHEN rejection_type = 'block' THEN penalty ELSE 0 END) as total_block_penalty,
                SUM(CASE WHEN rejection_type = 'load' THEN 1 ELSE 0 END) as total_load_rejections,
                SUM(CASE WHEN rejection_type = 'load' THEN penalty ELSE 0 END) as total_load_penalty
            ")
            ->whereBetween('date', [$startDate, $endDate])
            ->tap($driverFilter)
            ->groupBy('driver_name')
            ->get();

        // ➤ By Reason Code
        $byReason = DB::table('rejections')
            ->join('rejection_reason_codes', 'rejections.reason_code_id', '=', 'rejection_reason_codes.id')
            ->selectRaw("
                rejection_reason_codes.reason_code,
                COUNT(*) as total_rejections,
                SUM(rejections.penalty) as total_penalty,
                SUM(CASE WHEN rejections.rejection_type = 'block' THEN 1 ELSE 0 END) as total_block_rejections,
                SUM(CASE WHEN rejections.rejection_type = 'block' THEN rejections.penalty ELSE 0 END) as total_block_penalty,
                SUM(CASE WHEN rejections.rejection_type = 'load' THEN 1 ELSE 0 END) as total_load_rejections,
                SUM(CASE WHEN rejections.rejection_type = 'load' THEN rejections.penalty ELSE 0 END) as total_load_penalty
            ")
            ->whereBetween('rejections.date', [$startDate, $endDate])
            ->tap($driverFilter)
            ->groupBy('rejection_reason_codes.reason_code')
            ->get();

        return [
            'by_driver' => $byDriver,
            'by_reason' => $byReason,
        ];
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
