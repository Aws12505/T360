<?php

namespace App\Services\Summaries;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Request;
class RejectionBreakdownService
{

    /*
    |--------------------------------------------------------------------------
    | Base Queries (Reusable)
    |--------------------------------------------------------------------------
    */

    private function baseRejectionQuery($startDate, $endDate)
    {
        return DB::table('rejections')
            ->whereBetween('date', [$startDate, $endDate]);
    }

    private function baseBlockQuery($startDate, $endDate)
    {
        return $this->baseRejectionQuery($startDate, $endDate)
            ->join('rejected_blocks', 'rejected_blocks.rejection_id', '=', 'rejections.id');
    }

    private function baseLoadQuery($startDate, $endDate)
    {
        return $this->baseRejectionQuery($startDate, $endDate)
            ->join('rejected_loads', 'rejected_loads.rejection_id', '=', 'rejections.id');
    }

    private function baseAdvancedQuery($startDate, $endDate)
    {
        return $this->baseRejectionQuery($startDate, $endDate)
            ->join('advanced_rejected_blocks', 'advanced_rejected_blocks.rejection_id', '=', 'rejections.id');
    }


    /*
    |--------------------------------------------------------------------------
    | Driver Breakdown
    |--------------------------------------------------------------------------
    */

    public function getRejectionsByDriver($startDate, $endDate)
    {
        $blockQuery = $this->baseBlockQuery($startDate, $endDate)
            ->selectRaw("
                rejected_blocks.driver_name,
                COUNT(*) as total_block_rejections,
                SUM(rejections.penalty) as total_block_penalty
            ");

        $this->applyTenantFilter($blockQuery, 'rejections');
        $this->applyDriverAnalyticsFilter($blockQuery);

        $blockResults = $blockQuery
            ->groupBy('rejected_blocks.driver_name')
            ->get()
            ->keyBy('driver_name');


        $loadQuery = $this->baseLoadQuery($startDate, $endDate)
            ->selectRaw("
                rejected_loads.driver_name,
                COUNT(*) as total_load_rejections,
                SUM(rejections.penalty) as total_load_penalty
            ");

        $this->applyTenantFilter($loadQuery, 'rejections');
        $this->applyDriverAnalyticsFilter($loadQuery);

        $loadResults = $loadQuery
            ->groupBy('rejected_loads.driver_name')
            ->get()
            ->keyBy('driver_name');


        $allDrivers = $blockResults->keys()->merge($loadResults->keys())->unique();

        return $allDrivers->map(function ($driver) use ($blockResults, $loadResults) {

            $block = $blockResults->get($driver);
            $load = $loadResults->get($driver);

            return (object) [
                'driver_name' => $driver,

                'total_rejections' =>
                    ($block->total_block_rejections ?? 0) +
                    ($load->total_load_rejections ?? 0),

                'total_penalty' =>
                    ($block->total_block_penalty ?? 0) +
                    ($load->total_load_penalty ?? 0),

                'total_block_rejections' => $block->total_block_rejections ?? 0,
                'total_block_penalty' => $block->total_block_penalty ?? 0,

                'total_load_rejections' => $load->total_load_rejections ?? 0,
                'total_load_penalty' => $load->total_load_penalty ?? 0,
            ];
        })->values();
    }


    /*
    |--------------------------------------------------------------------------
    | Rejections by Reason
    |--------------------------------------------------------------------------
    */

    public function getRejectionsByReason($startDate, $endDate)
    {
        $normalizedReason = "COALESCE(NULLIF(TRIM(rejection_reason), ''), '—')";

        $baseQuery = $this->baseRejectionQuery($startDate, $endDate)
            ->selectRaw("{$normalizedReason} as rejection_reason, penalty");

        $this->applyTenantFilter($baseQuery);
        $this->applyCompanyAnalyticsFilter($baseQuery);

        return DB::query()
            ->fromSub($baseQuery, 'rejection_reason_summary')
            ->selectRaw("
                rejection_reason,
                COUNT(*) as total_rejections,
                SUM(penalty) as total_penalty
            ")
            ->groupBy('rejection_reason')
            ->orderBy('total_rejections', 'desc')
            ->get();
    }


    /*
    |--------------------------------------------------------------------------
    | Category Breakdown
    |--------------------------------------------------------------------------
    */

    public function getRejectionsCategoryBreakdown($startDate, $endDate)
    {

        $totalQuery = $this->baseRejectionQuery($startDate, $endDate)
            ->selectRaw("
                COUNT(*) as total_rejections,
                SUM(penalty) as total_penalty
            ");

        $this->applyTenantFilter($totalQuery);
        $this->applyCompanyAnalyticsFilter($totalQuery);

        $total = $totalQuery->first();


        $advanced = $this->baseAdvancedQuery($startDate, $endDate)
            ->selectRaw("
                COUNT(*) as advanced_rejection_count,
                SUM(rejections.penalty) as advanced_rejection_penalty
            ");

        $this->applyTenantFilter($advanced, 'rejections');
        $this->applyCompanyAnalyticsFilter($advanced);

        $advanced = $advanced->first();


        $blocks = $this->baseBlockQuery($startDate, $endDate)
            ->selectRaw("
                COUNT(*) as total_block_rejections,

                SUM(CASE WHEN rejection_bucket='less_than_24' THEN 1 ELSE 0 END) as less_than_24_count,
                SUM(CASE WHEN rejection_bucket='more_than_24' THEN 1 ELSE 0 END) as more_than_24_count,

                SUM(CASE WHEN rejection_bucket='less_than_24' THEN rejections.penalty ELSE 0 END) as less_than_24_penalty,
                SUM(CASE WHEN rejection_bucket='more_than_24' THEN rejections.penalty ELSE 0 END) as more_than_24_penalty
            ");

        $this->applyTenantFilter($blocks, 'rejections');
        $this->applyCompanyAnalyticsFilter($blocks);

        $blocks = $blocks->first();


        $loads = $this->baseLoadQuery($startDate, $endDate)
            ->selectRaw("
                COUNT(*) as total_load_rejections,

                SUM(CASE WHEN rejection_bucket='rejected_after_start_time' THEN 1 ELSE 0 END) as after_start_count,
                SUM(CASE WHEN rejection_bucket='rejected_0_6_hours_before_start_time' THEN 1 ELSE 0 END) as within_6_count,
                SUM(CASE WHEN rejection_bucket='rejected_6_plus_hours_before_start_time' THEN 1 ELSE 0 END) as more_than_6_count,

                SUM(CASE WHEN rejection_bucket='rejected_after_start_time' THEN rejections.penalty ELSE 0 END) as after_start_penalty,
                SUM(CASE WHEN rejection_bucket='rejected_0_6_hours_before_start_time' THEN rejections.penalty ELSE 0 END) as within_6_penalty,
                SUM(CASE WHEN rejection_bucket='rejected_6_plus_hours_before_start_time' THEN rejections.penalty ELSE 0 END) as more_than_6_penalty
            ");

        $this->applyTenantFilter($loads, 'rejections');
        $this->applyCompanyAnalyticsFilter($loads);

        $loads = $loads->first();


        return (object) [
            'total_rejections' => $total->total_rejections ?? 0,
            'total_penalty' => $total->total_penalty ?? 0,

            'advanced_rejection_count' => $advanced->advanced_rejection_count ?? 0,
            'advanced_rejection_penalty' => $advanced->advanced_rejection_penalty ?? 0,

            'total_block_rejections' => $blocks->total_block_rejections ?? 0,
            'less_than_24_count' => $blocks->less_than_24_count ?? 0,
            'more_than_24_count' => $blocks->more_than_24_count ?? 0,

            'less_than_24_penalty' => $blocks->less_than_24_penalty ?? 0,
            'more_than_24_penalty' => $blocks->more_than_24_penalty ?? 0,

            'total_load_rejections' => $loads->total_load_rejections ?? 0,

            'after_start_count' => $loads->after_start_count ?? 0,
            'within_6_count' => $loads->within_6_count ?? 0,
            'more_than_6_count' => $loads->more_than_6_count ?? 0,

            'after_start_penalty' => $loads->after_start_penalty ?? 0,
            'within_6_penalty' => $loads->within_6_penalty ?? 0,
            'more_than_6_penalty' => $loads->more_than_6_penalty ?? 0,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Bottom Drivers
    |--------------------------------------------------------------------------
    */

    public function getBottomFiveDriversByPenalty($startDate, $endDate)
    {

        $block = $this->baseBlockQuery($startDate, $endDate)
            ->selectRaw("
                rejected_blocks.driver_name,
                SUM(rejections.penalty) as total_penalty
            ");

        $this->applyTenantFilter($block, 'rejections');
        $this->applyDriverAnalyticsFilter($block);

        $block = $block->groupBy('rejected_blocks.driver_name')
            ->orderBy('total_penalty', 'desc')
            ->limit(5)
            ->get();


        $load = $this->baseLoadQuery($startDate, $endDate)
            ->selectRaw("
                rejected_loads.driver_name,
                SUM(rejections.penalty) as total_penalty
            ");

        $this->applyTenantFilter($load, 'rejections');
        $this->applyDriverAnalyticsFilter($load);

        $load = $load->groupBy('rejected_loads.driver_name')
            ->orderBy('total_penalty', 'desc')
            ->limit(5)
            ->get();


        return [
            'total' => $block->merge($load)->sortByDesc('total_penalty')->take(5)->values(),
            'block' => $block,
            'load' => $load,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Line Chart
    |--------------------------------------------------------------------------
    */

    public function getLineChartData($startDate, $endDate): array
    {
        $dateFilter = Request::input('dateFilter', 'yesterday');

        $usesWeekNumber = in_array($dateFilter, ['6w', 'quarterly', 'custom']);

        if ($dateFilter === 'yesterday') {
            $groupBy = 'DATE_FORMAT(date, "%Y-%m-%d")';
            $selectDate = DB::raw("$groupBy as grouped_date");
            $labelType = 'date';

        } elseif ($dateFilter === 'current-week') {
            $groupBy = 'DATE(date)';
            $selectDate = DB::raw("$groupBy as grouped_date");
            $labelType = 'day';

        } elseif ($usesWeekNumber) {
            $groupBy = 'DATE(DATE_SUB(date, INTERVAL DAYOFWEEK(date) - 1 DAY))';
            $selectDate = DB::raw("$groupBy as grouped_date");
            $selectWeekNumber = DB::raw("WEEK($groupBy, 0) +1 as week_number");
            $labelType = 'week';

        } else {
            $groupBy = 'DATE(date)';
            $selectDate = DB::raw("$groupBy as grouped_date");
            $labelType = 'date';
        }

        $query = DB::table('rejections');

        $aggregates = DB::raw("
        COUNT(*) as total_entries,
        SUM(CASE
            WHEN carrier_controllable = 1
            THEN penalty ELSE 0
        END) as carrier_penalty
    ");

        if ($usesWeekNumber) {
            $query->select(
                $selectDate,
                $selectWeekNumber,
                $aggregates
            );
        } else {
            $query->select(
                $selectDate,
                $aggregates
            );
        }

        $query->whereBetween('date', [$startDate, $endDate])
            ->groupBy('grouped_date')
            ->orderBy('grouped_date');

        if ($usesWeekNumber) {
            $query->groupBy('week_number');
        }

        $this->applyTenantFilter($query);
        $this->applyCompanyAnalyticsFilter($query);

        $results = $query->get();

        $chartData = $results->map(function ($item) use ($labelType) {
            if ($labelType === 'week') {
                $formattedDate = isset($item->week_number)
                    ? 'W' . $item->week_number
                    : $item->grouped_date;
            } elseif ($labelType === 'month') {
                $formattedDate = Carbon::parse($item->grouped_date . '-01')->format('M');
            } elseif ($labelType === 'day') {
                $formattedDate = Carbon::parse($item->grouped_date)->format('D');
            } else {
                $formattedDate = $item->grouped_date;
            }

            $entries = $item->total_entries ?? 0;
            $penalty = $item->carrier_penalty ?? 0;

            $finalPerformance = $entries > 0
                ? (1 - ($penalty / $entries)) * 100
                : 100;

            return [
                'date' => $formattedDate,
                'onTimePerformance' => round($finalPerformance, 1),
            ];
        })->toArray();

        $values = collect($chartData)
            ->pluck('onTimePerformance')
            ->filter();

        $averageOnTime = $values->count()
            ? round($values->avg(), 1)
            : null;

        return [
            'chartData' => $chartData,
            'averageAcceptance' => $averageOnTime,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Aggregates
    |--------------------------------------------------------------------------
    */

    public function getRejectionBreakdown($startDate, $endDate): array
    {
        return [
            'by_driver' => $this->getRejectionsByDriver($startDate, $endDate),
            'by_reason' => $this->getRejectionsByReason($startDate, $endDate),
        ];
    }

    public function getRejectionBreakdownDetailsPage($startDate, $endDate): array
    {
        return [
            'by_category' => $this->getRejectionsCategoryBreakdown($startDate, $endDate),
            'bottom_five_drivers' => $this->getBottomFiveDriversByPenalty($startDate, $endDate),
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Filters
    |--------------------------------------------------------------------------
    */

    public function applyTenantFilter($query, $tablePrefix = '')
    {
        if (Auth::check() && !is_null(Auth::user()->tenant_id)) {
            $column = $tablePrefix ? "{$tablePrefix}.tenant_id" : 'tenant_id';
            $query->where($column, Auth::user()->tenant_id);
        }
    }

    private function applyCompanyAnalyticsFilter($query)
    {
        $query->where('carrier_controllable', true);
    }

    private function applyDriverAnalyticsFilter($query)
    {
        $query->where('driver_controllable', true);
    }


    /*
    |--------------------------------------------------------------------------
    | Combined Breakdown + Chart
    |--------------------------------------------------------------------------
    */

    public function getRejectionBreakdownWithChart($startDate, $endDate): array
    {
        return array_merge(
            $this->getRejectionBreakdown($startDate, $endDate),
            $this->getRejectionBreakdownDetailsPage($startDate, $endDate),
            ['lineChartData' => $this->getLineChartData($startDate, $endDate)]
        );
    }
}