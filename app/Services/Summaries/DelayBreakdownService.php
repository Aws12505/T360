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
            SUM(CASE WHEN delay_category = '601_plus' THEN 1 ELSE 0 END) as category_601_plus_count,

            SUM(CASE WHEN delay_category = '1_60' AND delay_type = 'origin' THEN 1 ELSE 0 END) as category_1_60_origin_count,
            SUM(CASE WHEN delay_category = '1_60' AND delay_type = 'destination' THEN 1 ELSE 0 END) as category_1_60_destination_count,

            SUM(CASE WHEN delay_category = '61_240' AND delay_type = 'origin' THEN 1 ELSE 0 END) as category_61_240_origin_count,
            SUM(CASE WHEN delay_category = '61_240' AND delay_type = 'destination' THEN 1 ELSE 0 END) as category_61_240_destination_count,

            SUM(CASE WHEN delay_category = '241_600' AND delay_type = 'origin' THEN 1 ELSE 0 END) as category_241_600_origin_count,
            SUM(CASE WHEN delay_category = '241_600' AND delay_type = 'destination' THEN 1 ELSE 0 END) as category_241_600_destination_count,

            SUM(CASE WHEN delay_category = '601_plus' AND delay_type = 'origin' THEN 1 ELSE 0 END) as category_601_plus_origin_count,
            SUM(CASE WHEN delay_category = '601_plus' AND delay_type = 'destination' THEN 1 ELSE 0 END) as category_601_plus_destination_count,

            SUM(CASE WHEN delay_type = 'origin' THEN 1 ELSE 0 END) as total_origin_delays,
            SUM(CASE WHEN delay_type = 'destination' THEN 1 ELSE 0 END) as total_destination_delays
        ")
            ->whereBetween('date', [$startDate, $endDate]);

        $this->applyTenantFilter($query);
        $this->applyCompanyAnalyticsFilter($query);

        return $query->first();
    }

    public function getBottomFiveDriversByPenalty($startDate, $endDate)
    {
        $base = fn() => DB::table('delays')
            ->whereBetween('date', [$startDate, $endDate])
            ->where('driver_controllable', true);

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
                'moreThan601Count' => (int) $category->category_601_plus_count,
                'origin' => [
                    'totalDelays' => (int) $category->total_origin_delays,
                    'between1_60Count' => (int) $category->category_1_60_origin_count,
                    'between61_240Count' => (int) $category->category_61_240_origin_count,
                    'between241_600Count' => (int) $category->category_241_600_origin_count,
                    'moreThan601Count' => (int) $category->category_601_plus_origin_count,
                ],
                'destination' => [
                    'totalDelays' => (int) $category->total_destination_delays,
                    'between1_60Count' => (int) $category->category_1_60_destination_count,
                    'between61_240Count' => (int) $category->category_61_240_destination_count,
                    'between241_600Count' => (int) $category->category_241_600_destination_count,
                    'moreThan601Count' => (int) $category->category_601_plus_destination_count,
                ],
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

        [$groupBy, $dateFormat, $labelFormat] = match ($dateFilter) {
            'yesterday' => [DB::raw('DATE_FORMAT(date, "%Y-%m-%d")'), 'Y-m-d', 'Y-m-d'],
            'current-week' => [DB::raw('DATE(date)'), 'Y-m-d', 'D'],
            '6w' => [DB::raw('YEARWEEK(date, 6)'), 'Y-W', 'W'],
            default => [DB::raw('DATE_FORMAT(date, "%Y-%m")'), 'Y-m', 'M'],
        };

        $query = DB::table('delays')
            ->select(
                $groupBy,
                DB::raw("
                SUM(CASE WHEN delay_type='origin' THEN penalty ELSE 0 END) as origin_penalty,
                SUM(CASE WHEN delay_type='destination' THEN penalty ELSE 0 END) as destination_penalty,
                SUM(CASE WHEN delay_type='origin' THEN 1 ELSE 0 END) as origin_count,
                SUM(CASE WHEN delay_type='destination' THEN 1 ELSE 0 END) as destination_count
            ")
            )
            ->whereBetween('date', [$startDate, $endDate])
            ->groupBy($groupBy)
            ->orderBy($groupBy);

        $this->applyTenantFilter($query);
        $chartData = $query->get()->map(function ($item) use ($dateFormat, $labelFormat) {

            $dateValue = $item->{array_key_first((array) $item)};

            $formattedDate = match ($dateFormat) {
                'Y-W' => 'W' . substr($dateValue, 4),
                'Y-m' => Carbon::parse($dateValue . '-01')->format($labelFormat),
                default => Carbon::parse($dateValue)->format($labelFormat),
            };

            $originPerformance = null;
            $destinationPerformance = null;
            if ($item->origin_count > 0) {
                $originPerformance = (1 - ($item->origin_penalty / $item->origin_count)) * 100;
            } else {
                $originPerformance = 100;
            }

            if ($item->destination_count > 0) {
                $destinationPerformance = (1 - ($item->destination_penalty / $item->destination_count)) * 100;
            } else {
                $destinationPerformance = 100;
            }

            $finalPerformance = null;

            $finalPerformance =
                ($originPerformance * 0.375) +
                ($destinationPerformance * 0.625);

            return [
                'date' => $formattedDate,
                'onTimePerformance' => $finalPerformance !== null
                    ? round($finalPerformance, 1)
                    : null
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
}
