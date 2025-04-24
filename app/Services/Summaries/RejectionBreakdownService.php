<?php

namespace App\Services\Summaries;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class RejectionBreakdownService
{
    /**
     * Get rejection breakdown by driver
     */
    public function getRejectionsByDriver($startDate, $endDate)
    {
        $query = DB::table('rejections')
            ->selectRaw("
                driver_name,
                COUNT(*) as total_rejections,
                SUM(penalty) as total_penalty,
                SUM(CASE WHEN rejection_type = 'block' THEN 1 ELSE 0 END) as total_block_rejections,
                SUM(CASE WHEN rejection_type = 'block' THEN penalty ELSE 0 END) as total_block_penalty,
                SUM(CASE WHEN rejection_type = 'load' THEN 1 ELSE 0 END) as total_load_rejections,
                SUM(CASE WHEN rejection_type = 'load' THEN penalty ELSE 0 END) as total_load_penalty
            ")
            ->whereBetween('date', [$startDate, $endDate]);

        $this->applyTenantFilter($query);
        $this->applyDriverControllableFilter($query);

        return $query->groupBy('driver_name')->get();
    }

    /**
     * Get rejection breakdown by reason code
     */
    public function getRejectionsByReason($startDate, $endDate)
    {
        $query = DB::table('rejections')
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
            ->whereBetween('rejections.date', [$startDate, $endDate]);

        $this->applyTenantFilter($query, 'rejections');
        $this->applyDriverControllableFilter($query);

        return $query->groupBy('rejection_reason_codes.reason_code')->get();
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
     * Get complete rejection breakdown data for the specified date range
     */
    public function getRejectionBreakdown($startDate, $endDate): array
    {
        return [
            'by_driver' => $this->getRejectionsByDriver($startDate, $endDate),
            'by_reason' => $this->getRejectionsByReason($startDate, $endDate),
        ];
    }
}
