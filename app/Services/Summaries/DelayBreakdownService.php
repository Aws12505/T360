<?php

namespace App\Services\Summaries;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DelayBreakdownService
{
    /**
     * Get delay breakdown by driver
     */
    public function getDelaysByDriver($startDate, $endDate)
    {
        $query = DB::table('delays')
            ->selectRaw("
                driver_name,
                COUNT(*) as total_delays,
                SUM(penalty) as total_penalty,
                SUM(CASE WHEN delay_type = 'origin' THEN 1 ELSE 0 END) as total_origin_delays,
                SUM(CASE WHEN delay_type = 'origin' THEN penalty ELSE 0 END) as total_origin_penalty,
                SUM(CASE WHEN delay_type = 'destination' THEN 1 ELSE 0 END) as total_destination_delays,
                SUM(CASE WHEN delay_type = 'destination' THEN penalty ELSE 0 END) as total_destination_penalty
            ")
            ->whereBetween('date', [$startDate, $endDate]);

        $this->applyTenantFilter($query);
        $this->applyDriverControllableFilter($query);

        return $query->groupBy('driver_name')->get();
    }

    /**
     * Get delay breakdown by delay code
     */
    public function getDelaysByCode($startDate, $endDate)
    {
        $query = DB::table('delays')
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
            ->whereBetween('delays.date', [$startDate, $endDate]);

        $this->applyTenantFilter($query, 'delays');
        $this->applyDriverControllableFilter($query);

        return $query->groupBy('delay_codes.code')->get();
    }

    /**
     * Apply tenant filter to query if user is authenticated
     */
    public function applyTenantFilter($query, $tablePrefix = '')
    {
        if (Auth::check() && Auth::user()->tenant_id !== null) {
            $columnName = $tablePrefix ? "{$tablePrefix}.tenant_id" : 'tenant_id';
            $query->where($columnName, Auth::user()->tenant_id);
        }
    }

    /**
     * Apply driver controllable filter to query
     */
    public function applyDriverControllableFilter($query)
    {
        $query->where(function ($q) {
            $q->whereNull('driver_controllable')
              ->orWhere('driver_controllable', true);
        });
    }

    /**
     * Get complete delay breakdown data for the specified date range
     */
    public function getDelayBreakdown($startDate, $endDate): array
    {
        return [
            'by_driver' => $this->getDelaysByDriver($startDate, $endDate),
            'by_code'   => $this->getDelaysByCode($startDate, $endDate),
        ];
    }
}
