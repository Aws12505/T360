<?php

namespace App\Services\Summaries;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DelayBreakdownService
{
    // ─────────────────────────────────────────────
    //  Core breakdown queries
    // ─────────────────────────────────────────────

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
        $this->applyDriverAnalyticsFilter($query);

        return $query->groupBy('driver_name')->get();
    }

    /**
     * Get delays grouped by delay_reason string (replaces the old delay_codes join).
     * Null/empty reasons are grouped under '—'.
     */
    public function getDelaysByReason($startDate, $endDate)
    {
        $query = DB::table('delays')
            ->selectRaw("
            COALESCE(NULLIF(TRIM(delay_reason), ''), '—') as reason,
            COUNT(*) as total_delays,
            SUM(penalty) as total_penalty,
            SUM(CASE WHEN delay_type = 'origin' THEN 1 ELSE 0 END) as total_origin_delays,
            SUM(CASE WHEN delay_type = 'origin' THEN penalty ELSE 0 END) as total_origin_penalty,
            SUM(CASE WHEN delay_type = 'destination' THEN 1 ELSE 0 END) as total_destination_delays,
            SUM(CASE WHEN delay_type = 'destination' THEN penalty ELSE 0 END) as total_destination_penalty
        ")
            ->whereBetween('date', [$startDate, $endDate]);

        $this->applyTenantFilter($query);
        $this->applyCompanyAnalyticsFilter($query);

        return $query
            ->groupBy(DB::raw("COALESCE(NULLIF(TRIM(delay_reason), ''), '—')"))
            ->orderBy('total_delays', 'desc')
            ->get();
    }

    public function getDelaysCategoryBreakdown($startDate, $endDate)
    {
        $query = DB::table('delays')
            ->selectRaw("
            COUNT(*) as total_delays,

            SUM(CASE WHEN delay_category = '1_60' THEN 1 ELSE 0 END) as category_1_60_count,
            SUM(CASE WHEN delay_category = '61_240' THEN 1 ELSE 0 END) as category_61_240_count,
            SUM(CASE WHEN delay_category = '241_600' THEN 1 ELSE 0 END) as category_241_600_count,
            SUM(CASE WHEN delay_category = '601_plus' THEN 1 ELSE 0 END) as category_601_plus_count
        ")
            ->whereBetween('date', [$startDate, $endDate]);

        $this->applyTenantFilter($query);
        $this->applyCompanyAnalyticsFilter($query);
        $this->applyDelayTypeFilter($query);

        return $query->first();
    }

    public function getBottomFiveDriversByPenalty($startDate, $endDate)
    {
        $base = function () use ($startDate, $endDate) {
            $query = DB::table('delays')
                ->whereBetween('date', [$startDate, $endDate])
                ->where('driver_controllable', true);

            $this->applyDelayTypeFilter($query);

            return $query;
        };

        $total = $base()
            ->selectRaw('driver_name, SUM(penalty) as total_penalty');

        $this->applyTenantFilter($total);

        $total = $total->groupBy('driver_name')
            ->orderBy('total_penalty', 'desc')
            ->limit(5)
            ->get();

        $origin = $base()
            ->selectRaw("
            driver_name,
            SUM(CASE WHEN delay_type = 'origin' THEN penalty ELSE 0 END) as total_penalty
        ")
            ->where('delay_type', 'origin');

        $this->applyTenantFilter($origin);

        $origin = $origin->groupBy('driver_name')
            ->orderBy('total_penalty', 'desc')
            ->limit(5)
            ->get();

        $destination = $base()
            ->selectRaw("
            driver_name,
            SUM(CASE WHEN delay_type = 'destination' THEN penalty ELSE 0 END) as total_penalty
        ")
            ->where('delay_type', 'destination');

        $this->applyTenantFilter($destination);

        $destination = $destination->groupBy('driver_name')
            ->orderBy('total_penalty', 'desc')
            ->limit(5)
            ->get();

        return [
            'total' => $total,
            'origin' => $origin,
            'destination' => $destination,
        ];
    }
    // ─────────────────────────────────────────────
    //  Aggregate helpers
    // ─────────────────────────────────────────────

    public function getDelayBreakdown($startDate, $endDate): array
    {
        return [
            'by_driver' => $this->getDelaysByDriver($startDate, $endDate),
            'by_reason' => $this->getDelaysByReason($startDate, $endDate),
        ];
    }

    public function getDelayBreakdownDetailsPage($startDate, $endDate): array
    {
        $category = $this->getDelaysCategoryBreakdown($startDate, $endDate);

        return [
            'by_category' => [
                'totalDelays' => (int) $category->total_delays,
                'between1_60Count' => (int) $category->category_1_60_count,
                'between61_240Count' => (int) $category->category_61_240_count,
                'between241_600Count' => (int) $category->category_241_600_count,
                'moreThan601Count' => (int) $category->category_601_plus_count
            ],

            'bottom_five_drivers' => $this->getBottomFiveDriversByPenalty($startDate, $endDate),
        ];
    }

    // ─────────────────────────────────────────────
    //  Line chart
    // ─────────────────────────────────────────────

    public function getLineChartData($startDate, $endDate): array
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        $dateFilter = $this->determineDateFilterType($start, $end);

        if ($dateFilter === 'yesterday') {

            $groupBy = 'DATE_FORMAT(date, "%Y-%m-%d")';
            $selectDate = DB::raw("$groupBy as grouped_date");
            $labelType = 'date';

        } elseif ($dateFilter === 'current-week') {

            $groupBy = 'DATE(date)';
            $selectDate = DB::raw("$groupBy as grouped_date");
            $labelType = 'day';

        } elseif ($dateFilter === '6w') {

            $groupBy = 'DATE(DATE_SUB(date, INTERVAL DAYOFWEEK(date) - 1 DAY))';
            $selectDate = DB::raw("$groupBy as grouped_date");

            $selectWeekNumber = DB::raw("WEEK($groupBy, 0) + 1 as week_number");
            $labelType = 'week';

        } else {

            $groupBy = 'DATE_FORMAT(date, "%Y-%m")';
            $selectDate = DB::raw("$groupBy as grouped_date");
            $labelType = 'month';
        }

        $query = DB::table('delays');

        if ($dateFilter === '6w') {
            $query->select(
                $selectDate,
                $selectWeekNumber,
                DB::raw("
                SUM(CASE 
                    WHEN delay_type = 'origin' AND carrier_controllable = 1 
                    THEN penalty ELSE 0 
                END) as origin_penalty,

                SUM(CASE 
                    WHEN delay_type = 'destination' AND carrier_controllable = 1 
                    THEN penalty ELSE 0 
                END) as destination_penalty,

                SUM(CASE 
                    WHEN delay_type = 'origin' 
                    THEN 1 ELSE 0 
                END) as origin_count,

                SUM(CASE 
                    WHEN delay_type = 'destination' 
                    THEN 1 ELSE 0 
                END) as destination_count
            ")
            );
        } else {
            $query->select(
                $selectDate,
                DB::raw("
                SUM(CASE 
                    WHEN delay_type = 'origin' AND carrier_controllable = 1 
                    THEN penalty ELSE 0 
                END) as origin_penalty,

                SUM(CASE 
                    WHEN delay_type = 'destination' AND carrier_controllable = 1 
                    THEN penalty ELSE 0 
                END) as destination_penalty,

                SUM(CASE 
                    WHEN delay_type = 'origin' 
                    THEN 1 ELSE 0 
                END) as origin_count,

                SUM(CASE 
                    WHEN delay_type = 'destination' 
                    THEN 1 ELSE 0 
                END) as destination_count
            ")
            );
        }

        $query->whereBetween('date', [$startDate, $endDate])
            ->groupBy('grouped_date');

        if ($dateFilter === '6w') {
            $query->groupBy('week_number')->orderBy('grouped_date');
        } else {
            $query->orderBy('grouped_date');
        }

        $this->applyTenantFilter($query);
        $this->applyDelayTypeFilter($query);

        $results = $query->get();

        $chartData = $results->map(function ($item) use ($labelType) {
            if ($labelType === 'week') {
                $formattedDate = 'W' . $item->week_number;
            } elseif ($labelType === 'month') {
                $formattedDate = Carbon::parse($item->grouped_date . '-01')->format('M');
            } elseif ($labelType === 'day') {
                $formattedDate = Carbon::parse($item->grouped_date)->format('D');
            } else {
                $formattedDate = $item->grouped_date;
            }

            $originPerformance = $item->origin_count > 0
                ? (1 - ($item->origin_penalty / $item->origin_count)) * 100
                : 100;

            $destinationPerformance = $item->destination_count > 0
                ? (1 - ($item->destination_penalty / $item->destination_count)) * 100
                : 100;

            $finalPerformance =
                ($originPerformance * 0.375) +
                ($destinationPerformance * 0.625);

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
            'averageOnTime' => $averageOnTime,
        ];
    }

    // ─────────────────────────────────────────────
    //  Private helpers
    // ─────────────────────────────────────────────

    public function applyTenantFilter($query, $tablePrefix = ''): void
    {
        if (Auth::check() && !is_null(Auth::user()->tenant_id)) {
            $column = $tablePrefix ? "{$tablePrefix}.tenant_id" : 'tenant_id';
            $query->where($column, Auth::user()->tenant_id);
        }
    }

    private function determineDateFilterType(Carbon $start, Carbon $end): string
    {
        $daysDiff = $start->diffInDays($end);
        $now = Carbon::now();

        if ($now->dayOfWeek === 0) {
            $now->subDay();
        }

        $yesterday = Carbon::yesterday();
        $currentWeekStart = $now->copy()->startOfWeek(Carbon::SUNDAY);
        $currentWeekEnd = $now->copy()->endOfWeek(Carbon::SATURDAY);
        $sixWeeksStart = $currentWeekStart->copy()->subWeeks(5);

        if ($start->isSameDay($yesterday) && $end->isSameDay($yesterday))
            return 'yesterday';
        if ($start->isSameDay($currentWeekStart) && $end->isSameDay($currentWeekEnd))
            return 'current-week';
        if ($start->isSameDay($sixWeeksStart) && $end->isSameDay($currentWeekEnd))
            return '6w';
        if ($daysDiff >= 85 && $daysDiff <= 95)
            return 'quarterly';

        return 'full';
    }

    public function getDelayBreakdownWithChart($startDate, $endDate): array
    {
        return array_merge(
            $this->getDelayBreakdown($startDate, $endDate),
            $this->getDelayBreakdownDetailsPage($startDate, $endDate),
            ['lineChartData' => $this->getLineChartData($startDate, $endDate)]
        );
    }

    private function applyCompanyAnalyticsFilter($query): void
    {
        $query->where('carrier_controllable', true);
    }

    private function applyDriverAnalyticsFilter($query): void
    {
        $query->where('driver_controllable', true);
    }

    private function applyDelayTypeFilter($query): void
    {
        $delayType = request()->input('delayType');

        if (in_array($delayType, ['origin', 'destination'])) {
            $query->where('delay_type', $delayType);
        }
    }
}
