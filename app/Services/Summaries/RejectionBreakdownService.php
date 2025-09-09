<?php

namespace App\Services\Summaries;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

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

        return $query->groupBy('rejection_reason_codes.reason_code')->get();
    }

    /**
     * Get count of all rejections and breakdown by rejection category
     */
    public function getRejectionsCategoryBreakdown($startDate, $endDate)
    {
        $query = DB::table('rejections')
            ->selectRaw("
                COUNT(*) as total_rejections,
                SUM(CASE WHEN rejection_category = 'more_than_6' THEN 1 ELSE 0 END) as more_than_6_count,
                SUM(CASE WHEN rejection_category = 'within_6' THEN 1 ELSE 0 END) as within_6_count,
                SUM(CASE WHEN rejection_category = 'after_start' THEN 1 ELSE 0 END) as after_start_count,
                SUM(CASE WHEN rejection_category = 'within_24' THEN 1 ELSE 0 END) as within_24_count,
                SUM(CASE WHEN rejection_category = 'more_than_24' THEN 1 ELSE 0 END) as more_than_24_count,
                SUM(CASE WHEN rejection_category = 'advanced_rejection' THEN 1 ELSE 0 END) as advanced_rejection_count,
                
                SUM(CASE WHEN rejection_category = 'more_than_6' AND rejection_type = 'block' THEN 1 ELSE 0 END) as more_than_6_block_count,
                SUM(CASE WHEN rejection_category = 'more_than_6' AND rejection_type = 'load' THEN 1 ELSE 0 END) as more_than_6_load_count,

                SUM(CASE WHEN rejection_category = 'within_24' AND rejection_type = 'block' THEN 1 ELSE 0 END) as within_24_block_count,
                SUM(CASE WHEN rejection_category = 'within_24' AND rejection_type = 'load' THEN 1 ELSE 0 END) as within_24_load_count,

                SUM(CASE WHEN rejection_category = 'more_than_24' AND rejection_type = 'block' THEN 1 ELSE 0 END) as more_than_24_block_count,
                SUM(CASE WHEN rejection_category = 'more_than_24' AND rejection_type = 'load' THEN 1 ELSE 0 END) as more_than_24_load_count,
                
                SUM(CASE WHEN rejection_category = 'advanced_rejection' AND rejection_type = 'block' THEN 1 ELSE 0 END) as advanced_rejection_block_count,
                SUM(CASE WHEN rejection_category = 'advanced_rejection' AND rejection_type = 'load' THEN 1 ELSE 0 END) as advanced_rejection_load_count,
                
                SUM(CASE WHEN rejection_category = 'within_6' AND rejection_type = 'block' THEN 1 ELSE 0 END) as within_6_block_count,
                SUM(CASE WHEN rejection_category = 'within_6' AND rejection_type = 'load' THEN 1 ELSE 0 END) as within_6_load_count,
                
                SUM(CASE WHEN rejection_category = 'after_start' AND rejection_type = 'block' THEN 1 ELSE 0 END) as after_start_block_count,
                SUM(CASE WHEN rejection_category = 'after_start' AND rejection_type = 'load' THEN 1 ELSE 0 END) as after_start_load_count,
                
                SUM(CASE WHEN rejection_type = 'block' THEN 1 ELSE 0 END) as total_block_rejections,
                SUM(CASE WHEN rejection_type = 'load' THEN 1 ELSE 0 END) as total_load_rejections
               ")
            ->whereBetween('date', [$startDate, $endDate]);

            $this->applyTenantFilter($query);
        return $query->first();
    }

    /**
     * Get bottom five drivers with highest penalty sum
     */
    public function getBottomFiveDriversByPenalty($startDate, $endDate)
    {
        // Get bottom five drivers by total penalty
        $bottomFiveTotal = DB::table('rejections')
            ->selectRaw("
                driver_name,
                SUM(penalty) as total_penalty
            ")
            ->whereBetween('date', [$startDate, $endDate]);
            
        $this->applyTenantFilter($bottomFiveTotal);
        
        $bottomFiveTotal = $bottomFiveTotal->groupBy('driver_name')
            ->orderBy('total_penalty', 'desc')
            ->limit(5)
            ->get();
            
        // Get bottom five drivers by block penalty
        $bottomFiveBlock = DB::table('rejections')
            ->selectRaw("
                driver_name,
                SUM(CASE WHEN rejection_type = 'block' THEN penalty ELSE 0 END) as total_penalty
            ")
            ->whereBetween('date', [$startDate, $endDate])
            ->where('rejection_type', 'block');
            
        $this->applyTenantFilter($bottomFiveBlock);
        
        $bottomFiveBlock = $bottomFiveBlock->groupBy('driver_name')
            ->orderBy('total_penalty', 'desc')
            ->limit(5)
            ->get();
            
        // Get bottom five drivers by load penalty
        $bottomFiveLoad = DB::table('rejections')
            ->selectRaw("
                driver_name,
                SUM(CASE WHEN rejection_type = 'load' THEN penalty ELSE 0 END) as total_penalty
            ")
            ->whereBetween('date', [$startDate, $endDate])
            ->where('rejection_type', 'load');
            
        $this->applyTenantFilter($bottomFiveLoad);
        
        $bottomFiveLoad = $bottomFiveLoad->groupBy('driver_name')
            ->orderBy('total_penalty', 'desc')
            ->limit(5)
            ->get();
            
        return [
            'total' => $bottomFiveTotal,
            'block' => $bottomFiveBlock,
            'load' => $bottomFiveLoad
        ];
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
     * Get complete rejection breakdown data for the specified date range
     */
    public function getRejectionBreakdown($startDate, $endDate): array
    {
        return [
            'by_driver' => $this->getRejectionsByDriver($startDate, $endDate),
            'by_reason' => $this->getRejectionsByReason($startDate, $endDate),
        ];
    }

    /**
     * Get rejection breakdown details for the details page
     */
    public function getRejectionBreakdownDetailsPage($startDate, $endDate): array
    {
        return [
            'by_category' => $this->getRejectionsCategoryBreakdown($startDate, $endDate),
            'bottom_five_drivers' => $this->getBottomFiveDriversByPenalty($startDate, $endDate),
        ];
    }

    /**
     * Get line chart data for acceptance performance trends
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
        // Determine date filter type based on date range
        $dateFilter = $this->determineDateFilterType($start, $end);
        
        // Determine grouping based on date filter type
        if ($dateFilter === 'yesterday') {
            // For yesterday, we'll show hourly data if available
            $dateFormat = 'Y-m-d';
            $groupBy = DB::raw('DATE_FORMAT(date, "%Y-%m-%d")');
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
            $groupBy = DB::raw('YEARWEEK(date, 6)');
            $labelFormat = '\WW'; // Week number (W1, W2, etc.)
        } else {
            // Quarterly or longer - group by month
            $dateFormat = 'Y-m';
            $groupBy = DB::raw('DATE_FORMAT(date, "%Y-%m")');
            $labelFormat = 'M'; // Month name (Jan, Feb, etc.)
        }
        // Get the average acceptance performance across the entire date range
        $averageQuery = DB::table('performances')
            ->selectRaw('AVG(acceptance) as averageAcceptance')
            ->whereBetween('date', [$startDate, $endDate]);
        
        $this->applyTenantFilter($averageQuery);
        $averageResult = $averageQuery->first();
        $averageAcceptance = $averageResult ? round($averageResult->averageAcceptance, 1) : null;
        
        $query = DB::table('performances')
            ->select($groupBy, DB::raw('AVG(acceptance) as acceptancePerformance'))
            ->whereBetween('date', [$startDate, $endDate])
            ->groupBy($groupBy)
            ->orderBy($groupBy);
        
        $this->applyTenantFilter($query);
        $results = $query->get();
        
        // Format dates based on the determined grouping
        $chartData = $results->map(function($item) use ($dateFormat, $labelFormat, $dateFilter) {
            // Get the first property (date or yearweek)
            $dateValue = $item->{array_key_first((array)$item)};
            
            if ($dateFormat === 'Y-m-d') {
                // For daily grouping
                $date = Carbon::parse($dateValue);
                $formattedDate = $date->format($labelFormat);
            } elseif ($dateFormat === 'Y-m-d') {
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
                'acceptancePerformance' => round($item->acceptancePerformance, 1)
            ];
        })->toArray();
        return [
            'chartData' => $chartData,
            'averageAcceptance' => $averageAcceptance
        ];
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
        $daysDifference = $start->diffInDays($end);
        $now = Carbon::now();
        $isSunday = $now->dayOfWeek === 0;
        if($isSunday){
            $now = $now->copy()->subDays(1);
        }
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
     * Get complete rejection breakdown data with line chart for the specified date range
     */
    public function getRejectionBreakdownWithChart($startDate, $endDate): array
    {
        $basicData = $this->getRejectionBreakdown($startDate, $endDate);
        $detailsData = $this->getRejectionBreakdownDetailsPage($startDate, $endDate);
        $lineChartData = $this->getLineChartData($startDate, $endDate);
        
        return array_merge(
            $basicData,
            $detailsData,
            ['lineChartData' => $lineChartData]
        );
    }
}
