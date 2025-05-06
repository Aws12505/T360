<?php

namespace App\Services\Summaries;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

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
     * Get count of all delays and breakdown by delay category
     */
    public function getDelaysCategoryBreakdown($startDate, $endDate)
    {
        $query = DB::table('delays')
            ->selectRaw("
                COUNT(*) as total_delays,
                SUM(CASE WHEN delay_category = '1_120' THEN 1 ELSE 0 END) as category_1_120_count,
                SUM(CASE WHEN delay_category = '121_600' THEN 1 ELSE 0 END) as category_121_600_count,
                SUM(CASE WHEN delay_category = '601_plus' THEN 1 ELSE 0 END) as category_601_plus_count
            ")
            ->whereBetween('date', [$startDate, $endDate]);

        $this->applyTenantFilter($query);
        $this->applyDriverControllableFilter($query);

        return $query->first();
    }

    /**
     * Get bottom five drivers with highest penalty sum
     */
    public function getBottomFiveDriversByPenalty($startDate, $endDate)
    {
        $query = DB::table('delays')
            ->selectRaw("
                driver_name,
                SUM(penalty) as total_penalty
            ")
            ->whereBetween('date', [$startDate, $endDate]);

        $this->applyTenantFilter($query);
        $this->applyDriverControllableFilter($query);

        return $query->groupBy('driver_name')
            ->orderBy('total_penalty', 'desc')
            ->limit(5)
            ->get();
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
    public function getDelayBreakdownDetailsPage($startDate, $endDate): array
    {
        return [
            'by_category' => $this->getDelaysCategoryBreakdown($startDate, $endDate),
            'bottom_five_drivers' => $this->getBottomFiveDriversByPenalty($startDate, $endDate),
        ];
    }

    /**
     * Get line chart data for on-time performance trends
     * 
     * @param string $startDate The start date for the query
     * @param string $endDate The end date for the query
     * @return array The line chart data
     */
    public function getLineChartData($startDate, $endDate): array
    {
        // Use Carbon for consistent date handling
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $daysDifference = $end->diffInDays($start);
        
        // Determine date filter type based on date range
        $dateFilter = $this->determineDateFilterType($start, $end);
        
        // Determine grouping based on date filter type
        if ($dateFilter === 'yesterday') {
            // For yesterday, we'll show hourly data if available
            $dateFormat = 'Y-m-d H';
            $groupBy = DB::raw('DATE_FORMAT(date, "%Y-%m-%d %H")');
            $labelFormat = 'H:00'; // Hour format
        } elseif ($dateFilter === 'current-week') {
            // Current week - group by day
            $dateFormat = 'Y-m-d';
            $groupBy = DB::raw('DATE(date)');
            $labelFormat = 'D'; // Day name (Mon, Tue, etc.)
        } elseif ($dateFilter === '6w') {
            // 6 weeks - group by week with weeks starting on Sunday
            $dateFormat = 'Y-W';
            // Use YEARWEEK with mode 0 (weeks starting on Sunday)
            $groupBy = DB::raw('YEARWEEK(date, 0)');
            $labelFormat = '\WW'; // Week number (W1, W2, etc.)
        } else {
            // Quarterly or longer - group by month
            $dateFormat = 'Y-m';
            $groupBy = DB::raw('DATE_FORMAT(date, "%Y-%m")');
            $labelFormat = 'M'; // Month name (Jan, Feb, etc.)
        }
        
        $query = DB::table('performances')
            ->select($groupBy, DB::raw('AVG(on_time) as onTimePerformance'))
            ->whereBetween('date', [$startDate, $endDate])
            ->groupBy($groupBy)
            ->orderBy($groupBy);
        
        $this->applyTenantFilter($query);
        $results = $query->get();
        
        // Format dates based on the determined grouping
        return $results->map(function($item) use ($dateFormat, $labelFormat, $dateFilter) {
            // Get the first property (date or yearweek)
            $dateValue = $item->{array_key_first((array)$item)};
            
            if ($dateFormat === 'Y-m-d') {
                // For daily grouping
                $date = Carbon::parse($dateValue);
                $formattedDate = $date->format($labelFormat);
            } elseif ($dateFormat === 'Y-m-d H') {
                // For hourly grouping
                $date = Carbon::parse($dateValue);
                $formattedDate = $date->format($labelFormat);
            } elseif ($dateFormat === 'Y-m') {
                // For monthly grouping
                $date = Carbon::parse($dateValue . '-01');
                $formattedDate = $date->format($labelFormat);
            } else {
                // For weekly grouping
                // Extract year and week from YEARWEEK format (YYYYWW)
                $year = substr($dateValue, 0, 4);
                $week = substr($dateValue, 4);
                $formattedDate = 'W' . $week;
            }
            
            return [
                'date' => $formattedDate,
                'onTimePerformance' => round($item->onTimePerformance, 1)
            ];
        })->toArray();
    }
    
    /**
     * Determine the date filter type based on the date range
     * 
     * @param Carbon $start The start date
     * @param Carbon $end The end date
     * @return string The date filter type (yesterday, current-week, 6w, quarterly, or full)
     */
    private function determineDateFilterType(Carbon $start, Carbon $end): string
    {
        $daysDifference = $end->diffInDays($start);
        $now = Carbon::now();
        $yesterday = Carbon::yesterday();
        $currentWeekStart = $now->copy()->startOfWeek(Carbon::SUNDAY);
        $currentWeekEnd = $now->copy()->endOfWeek(Carbon::SATURDAY);
        $sixWeeksStart = $currentWeekStart->copy()->subWeeks(5);
        
        // Check if the date range matches yesterday
        if ($start->isSameDay($yesterday) && $end->isSameDay($yesterday)) {
            return 'yesterday';
        }
        
        // Check if the date range matches current week
        if ($start->isSameDay($currentWeekStart) && $end->isSameDay($currentWeekEnd)) {
            return 'current-week';
        }
        
        // Check if the date range matches 6 weeks
        if ($start->isSameDay($sixWeeksStart) && $end->isSameDay($currentWeekEnd)) {
            return '6w';
        }
        
        // Check if the date range is approximately 3 months
        if ($daysDifference >= 85 && $daysDifference <= 95) {
            return 'quarterly';
        }
        
        // Default to full if none of the above match
        return 'full';
    }
    
    /**
     * Get complete delay breakdown data with line chart for the specified date range
     */
    public function getDelayBreakdownWithChart($startDate, $endDate): array
    {
        $basicData = $this->getDelayBreakdown($startDate, $endDate);
        $detailsData = $this->getDelayBreakdownDetailsPage($startDate, $endDate);
        $lineChartData = $this->getLineChartData($startDate, $endDate);
        
        return array_merge(
            $basicData,
            $detailsData,
            ['lineChartData' => $lineChartData]
        );
    }
}
