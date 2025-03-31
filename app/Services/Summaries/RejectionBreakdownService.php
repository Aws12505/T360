<?php

namespace App\Services\Summaries;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class RejectionBreakdownService
{
    public function getRejectionBreakdown($startDate, $endDate): array
    {
        // Query for breakdown by driver
        $queryDriver = DB::table('rejections')
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

        if (Auth::check() && Auth::user()->tenant_id !== null) {
            $queryDriver->where('tenant_id', Auth::user()->tenant_id);
        }

        $queryDriver->where(function ($q) {
            $q->whereNull('driver_controllable')->orWhere('driver_controllable', true);
        })
        ->groupBy('driver_name');

        $byDriver = $queryDriver->get();

        // Query for breakdown by reason
        $queryReason = DB::table('rejections')
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

        if (Auth::check() && Auth::user()->tenant_id !== null) {
            $queryReason->where('rejections.tenant_id', Auth::user()->tenant_id);
        }

        $queryReason->where(function ($q) {
            $q->whereNull('driver_controllable')->orWhere('driver_controllable', true);
        })
        ->groupBy('rejection_reason_codes.reason_code');

        $byReason = $queryReason->get();

        return [
            'by_driver' => $byDriver,
            'by_reason' => $byReason,
        ];
    }
}
