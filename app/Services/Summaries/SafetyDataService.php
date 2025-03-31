<?php

namespace App\Services\Summaries;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

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
                AVG(driver_score) AS average_driver_score
            ")
            ->whereBetween('date', [$startDate, $endDate]);

        if (Auth::check() && Auth::user()->tenant_id !== null) {
            $query->where('tenant_id', Auth::user()->tenant_id);
        }

        $safetyData = $query->first();

        return [
            'traffic_light_violation'       => $safetyData->traffic_light_violation ?? 0,
            'speeding_violations'           => $safetyData->speeding_violations ?? 0,
            'following_distance_hard_brake' => $safetyData->following_distance_hard_brake ?? 0,
            'driver_distraction'            => $safetyData->driver_distraction ?? 0,
            'sign_violations'               => $safetyData->sign_violations ?? 0,
            'average_driver_score'          => $safetyData->average_driver_score ?? 0,
        ];
    }
}
