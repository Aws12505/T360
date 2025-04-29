<?php

namespace App\Services\Filtering;

use Illuminate\Support\Facades\Request;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class FilteringService
{
    /**
     * Apply pagination parameters to a query.
     *
     * @param int|null $perPage
     * @param array $allowedPerPageValues
     * @return int
     */
    public function getPerPage(?int $perPage = null, array $allowedPerPageValues = [10, 25, 50, 100]): int
    {
        $requestedPerPage = $perPage ?? (int) Request::input('perPage', 10);
        
        // Validate perPage to only allow specific values
        if (!in_array($requestedPerPage, $allowedPerPageValues)) {
            return $allowedPerPageValues[0]; // Default to first allowed value if invalid
        }
        
        return $requestedPerPage;
    }
    
    /**
     * Apply date filtering to a query.
     *
     * @param Builder $query
     * @param string $dateFilter
     * @param string $dateColumn
     * @param array &$dateRange Reference to store date range information
     * @return Builder
     */
    public function applyDateFilter(Builder $query, string $dateFilter = 'full', string $dateColumn = 'date', array &$dateRange = []): Builder
    {
        $now = Carbon::now();
        $currentWeekStart = $now->copy()->startOfWeek(Carbon::SUNDAY);
        $currentWeekEnd = $now->copy()->endOfWeek(Carbon::SATURDAY);
        $rollingStart = $currentWeekStart->copy()->subWeeks(5);
        $rollingEnd = $currentWeekEnd;
        
        switch ($dateFilter) {
            case 'yesterday':
                $yesterday = Carbon::yesterday()->format('Y-m-d');
                $dateRange = [
                    'start' => $yesterday,
                    'end' => $yesterday,
                    'label' => 'Yesterday'
                ];
                return $query->whereDate($dateColumn, $yesterday);
                
            case 'current-week':
                $dateRange = [
                    'start' => $currentWeekStart->format('Y-m-d'),
                    'end' => $currentWeekEnd->format('Y-m-d'),
                    'label' => 'Current Week'
                ];
                return $query->whereDate($dateColumn, '>=', $currentWeekStart->format('Y-m-d'))
                             ->whereDate($dateColumn, '<=', $currentWeekEnd->format('Y-m-d'));
                
            case '6w':
                $dateRange = [
                    'start' => $rollingStart->format('Y-m-d'),
                    'end' => $rollingEnd->format('Y-m-d'),
                    'label' => '6 Weeks'
                ];
                return $query->whereDate($dateColumn, '>=', $rollingStart->format('Y-m-d'))
                             ->whereDate($dateColumn, '<=', $rollingEnd->format('Y-m-d'));
                
            case 'quarterly':
                $quarterStart = $now->copy()->subMonths(3)->format('Y-m-d');
                $dateRange = [
                    'start' => $quarterStart,
                    'end' => $now->format('Y-m-d'),
                    'label' => 'Quarterly'
                ];
                return $query->whereDate($dateColumn, '>=', $quarterStart)
                             ->whereDate($dateColumn, '<=', $now->format('Y-m-d'));
                
            case 'full':
            default:
                $dateRange = [
                    'label' => 'All Time'
                ];
                // No filtering, return all records
                return $query;
        }
    }
    
    /**
     * Get the date filter from request.
     *
     * @param string $default
     * @return string
     */
    public function getDateFilter(string $default = '6w'): string
    {
        return Request::input('dateFilter', $default);
    }
}