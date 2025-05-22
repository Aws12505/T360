<?php

namespace App\Services\Safety;

use App\Models\SafetyData;
use App\Services\Filtering\FilteringService;
use App\Services\Summaries\SafetyDataService as SummariesSafetyDataService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use App\Models\Tenant;
use Carbon\Carbon;
/**
 * Class SafetyDataService
 *
 * Contains business logic for safety data operations, including CRUD, import, and export.
 *
 * Created manually: touch app/Services/SafetyDataService.php
 */
class SafetyDataService
{
    protected $filteringService;
    protected $summariesSafetyDataService;

    public function __construct(
        FilteringService $filteringService,
        SummariesSafetyDataService $summariesSafetyDataService
    ) {
        $this->filteringService = $filteringService;
        $this->summariesSafetyDataService = $summariesSafetyDataService;
    }

    /**
     * Get safety data entries for the index view.
     *
     * @return array
     */
    public function getSafetyDataIndex(): array
    {
        $query = SafetyData::with('tenant');
        
        // Apply date filtering if requested
        $dateFilter = $this->filteringService->getDateFilter();
        $dateRange = [];
        
        if ($dateFilter !== 'full') {
            $query = $this->filteringService->applyDateFilter($query, $dateFilter, 'date', $dateRange);
        }
        
        // Get per page value
        $perPage = $this->filteringService->getPerPage(Request::input('perPage', 10));
        
        // Apply tenant filter for non-admin users
        if (!is_null(Auth::user()->tenant_id)) {
            $query->where('tenant_id', Auth::user()->tenant_id);
        }
        
        $entries = $query->latest('date')->paginate($perPage);
        
        $isSuperAdmin = is_null(Auth::user()->tenant_id);
        $tenantSlug = $isSuperAdmin ? null : Auth::user()->tenant->slug;
        $tenants = $isSuperAdmin ? Tenant::all() : [];
        // Calculate week numbers for display
        $weekNumber = null;
        $startWeekNumber = null;
        $endWeekNumber = null;
        $year = null;
        if (!empty($dateRange) && isset($dateRange['start'])) {
            $startDate = Carbon::parse($dateRange['start']);
            $year = $startDate->year;
            // Removed dd($startDate) debug statement
            
            // compute week numbers (Sunday=first day)
            if (in_array($dateFilter, ['yesterday', 'current-week'])) {
                $weekNumber = $this->weekNumberSundayStart($startDate);
                $startWeekNumber = $endWeekNumber = null;
            } else {
                $weekNumber = null;
                $startWeekNumber = $this->weekNumberSundayStart($startDate);
                $endWeekNumber = isset($dateRange['end']) ? 
                    $this->weekNumberSundayStart(Carbon::parse($dateRange['end'])) : 
                    $startWeekNumber;
            }
        }
        $permissions = Auth::user()->getAllPermissions();

        // Get formatted safety data using the date range from filtering
        $safetyData = $this->getSafetyDataWithFiltering($dateFilter, $dateRange);
        return [
            'entries'    => $entries,
            'tenantSlug' => $tenantSlug,
            'SuperAdmin' => $isSuperAdmin,
            'tenants'    => $tenants,
            'dateRange'  => $dateRange,
            'dateFilter' => $dateFilter,
            'safetyData' => $safetyData,
            'weekNumber' => $weekNumber,
            'startWeekNumber' => $startWeekNumber,
            'endWeekNumber' => $endWeekNumber,
            'year' => $year,
            'permissions' => $permissions,
        ];
    }
/**
     * Get the week‐of‐year for a Carbon date, where weeks run Sunday → Saturday.
     *
     * @param  Carbon  $date
     * @return int
     */
    private function weekNumberSundayStart(Carbon $date): int
    {
        // 1..366
        $dayOfYear   = $date->dayOfYear;

        // 0=Sunday, …, 6=Saturday for Jan 1
        $firstDayDow = $date->copy()
                            ->startOfYear()
                            ->dayOfWeek;

        // shift so weeks bound on Sunday, then ceil
        return (int) ceil(($dayOfYear + $firstDayDow) / 7);
    }
    /**
     * Get formatted safety data using the current date filter
     * 
     * @param string $dateFilter The date filter to apply
     * @param array $dateRange The date range information
     * @return array The formatted safety data
     */
    public function getSafetyDataWithFiltering(string $dateFilter = 'full', array $dateRange = []): array
    {
        // If date range is not provided or empty, determine it based on the date filter
    if (empty($dateRange) || !isset($dateRange['start']) || !isset($dateRange['end'])) {
        // For 'full' filter, we need to handle it specially
        if ($dateFilter === 'full') {
            // Option 1: Use earliest and latest dates from the database
            $earliest = SafetyData::min('date') ?? now()->subYears(5)->format('Y-m-d');
            $latest = SafetyData::max('date') ?? now()->format('Y-m-d');
            
            $dateRange['start'] = $earliest;
            $dateRange['end'] = $latest;
            $dateRange['label'] = 'All Time';
        } 
    }
        
        // If we have a valid date range, get the formatted safety data
        if (isset($dateRange['start']) && isset($dateRange['end'])) {
            return $this->summariesSafetyDataService->getFormattedSafetyData(
                $dateRange['start'],
                $dateRange['end']
            );
        }
        
        // Default to empty data structure if no valid date range
        return [
            'greenZoneScore' => 0,
            'topDrivers' => [],
            'bottomDrivers' => [],
            'alerts' => [
                'distractedDriving' => 0,
                'speeding' => 0,
                'signViolation' => 0,
                'trafficLightViolation' => 0,
                'followingDistance' => 0
            ],
            'infractions' => [
                'driverStar' => 0,
                'potentialCollision' => 0,
                'hardBraking' => 0,
                'hardTurn' => 0,
                'hardAcceleration' => 0,
                'uTurn' => 0,
                'seatbeltCompliance' => 0,
                'cameraObstruction' => 0,
                'driverDrowsiness' => 0,
                'weaving' => 0,
                'collisionWarning' => 0,
                'backing' => 0,
                'roadsideParking' => 0,
                'highG' => 0
            ]
        ];
    }

    /**
     * Create a new safety data entry.
     *
     * @param array $data
     * @return void
     */
    public function createEntry(array $data)
    {
        SafetyData::create($data);
    }

    /**
     * Update an existing safety data entry.
     *
     * @param int $id
     * @param array $data
     * @return void
     */
    public function updateEntry($id, array $data)
    {
        $entry = SafetyData::findOrFail($id);
        $entry->update($data);
    }

    /**
     * Delete a safety data entry.
     *
     * @param int $id
     * @return void
     */
    public function deleteEntry($id)
    {
        $entry = SafetyData::findOrFail($id);
        $entry->delete();
    }

    /**
     * Delete multiple safety data entries.
     *
     * @param array $ids
     * @return void
     */
    public function deleteMultipleEntries(array $ids)
    {
        if (empty($ids)) {
            return;
        }
        
        // For security, ensure the user can only delete entries they have access to
        $query = SafetyData::whereIn('id', $ids);
        
        // If not a super admin, restrict to tenant's entries
        $user = Auth::user();
        if (!is_null($user->tenant_id)) {
            $query->where('tenant_id', $user->tenant_id);
        }
        
        $query->delete();
    }
}
