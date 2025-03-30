<?php

namespace App\Services\Summaries;

use Illuminate\Support\Facades\DB;

class DelayBreakdownService
{
    public function getDelayBreakdown($startDate, $endDate): array
    {
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
            ->where(function ($q) {
                $q->whereNull('driver_controllable')->orWhere('driver_controllable', true);
            })
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
            ->where(function ($q) {
                $q->whereNull('driver_controllable')->orWhere('driver_controllable', true);
            })
            ->groupBy('delay_codes.code')
            ->get();

        return [
            'by_driver' => $byDriver,
            'by_code'   => $byCode,
        ];
    }
}
