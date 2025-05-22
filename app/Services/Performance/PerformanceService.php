<?php

namespace App\Services\Performance;

use App\Models\Performance;
use App\Models\PerformanceMetricRule;
use App\Models\Tenant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Carbon\Carbon;
use App\Services\Performance\PerformanceCalculationsService;
use App\Services\Filtering\FilteringService;

/**
 * Class PerformanceService
 *
 * Contains business logic for performance records including rating calculations,
 * CRUD operations, and summary compilation.
 *
 * Created manually: touch app/Services/PerformanceService.php
 */
class PerformanceService
{
    protected PerformanceCalculationsService $performanceCalculationsService;
    protected FilteringService $filteringService;

    /**
     * Constructor.
     *
     * @param PerformanceCalculationsService $performanceCalculationsService Service for performance operations.
     * @param FilteringService $filteringService Service for filtering and pagination.
     */
    public function __construct(
        PerformanceCalculationsService $performanceCalculationsService,
        FilteringService $filteringService
    ) {
        $this->performanceCalculationsService = $performanceCalculationsService;
        $this->filteringService = $filteringService;
    }

    /**
     * Retrieve performance records for the index view.
     *
     * @return array
     */
    public function getPerformanceIndex(): array
    {
        $dateFilter = $this->filteringService->getDateFilter();
        $perPage = $this->filteringService->getPerPage();
        
        $query = Performance::with('tenant');
        
        // Apply date filtering
        $dateRange = [];
        $query = $this->filteringService->applyDateFilter($query, $dateFilter, 'date', $dateRange);
        
        // Order by date descending (newest to oldest)
        $query = $query->orderBy('date', 'desc');
        
        $performances = $query->paginate($perPage);
        
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

        return [
            'performances' => $performances,
            'tenantSlug'   => $tenantSlug,
            'SuperAdmin'   => $isSuperAdmin,
            'tenants'      => $tenants,
            'dateFilter'   => $dateFilter,
            'dateRange'    => $dateRange,
            'perPage'      => $perPage,
            'weekNumber'   => $weekNumber,
            'startWeekNumber' => $startWeekNumber,
            'endWeekNumber'   => $endWeekNumber,
            'year'         => $year,
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
     * Store a new performance record.
     *
     * @param array $data
     * @return void
     */
    public function storePerformance(array $data)
    {
        if($data['maintenance_variance_to_spend'] <= 5) {
            $data['maintenance_variance_to_spend'] = $data['maintenance_variance_to_spend'] * 100;
        }
        if($data['on_time_to_destination'] <= 5) {
            $data['on_time_to_destination'] = $data['on_time_to_destination'] * 100;
        }
        if($data['on_time_to_origin'] <= 5) {
            $data['on_time_to_origin'] = $data['on_time_to_origin'] * 100;
        }
        if($data['acceptance'] <= 5) {
            $data['acceptance'] = $data['acceptance'] * 100;
        }
        // Calculate the composite on_time value.
        $data['on_time'] = $data['on_time_to_origin'] == 0
            ? 0.5
            : ($data['on_time_to_origin'] * 0.375 + $data['on_time_to_destination'] * 0.625);

        $rule = PerformanceMetricRule::first();

        // Calculate ratings.
        $data['acceptance_rating'] = $this->performanceCalculationsService->getRating($data['acceptance'], $rule, 'acceptance');
        $data['on_time_rating'] = $this->performanceCalculationsService->getRating($data['on_time'], $rule, 'on_time');
        $data['maintenance_variance_to_spend_rating'] = $this->performanceCalculationsService->getRating($data['maintenance_variance_to_spend'], $rule, 'maintenance_variance');
        $data['open_boc_rating'] = $this->performanceCalculationsService->getRating($data['open_boc'], $rule, 'open_boc');
        $data['meets_safety_bonus_criteria_rating'] = $this->performanceCalculationsService->getSafetyBonusRating($data['meets_safety_bonus_criteria'], $rule);
        $data['vcr_preventable_rating'] = $this->performanceCalculationsService->getRating($data['vcr_preventable'], $rule, 'vcr_preventable');
        $data['vmcr_p_rating'] = $this->performanceCalculationsService->getRating($data['vmcr_p'], $rule, 'vmcr_p');
        
        Performance::updateOrCreate(
            ['tenant_id' => $data['tenant_id'], 'date' => $data['date']],
            $data
        );
    }

    /**
     * Update an existing performance record.
     *
     * @param int $id
     * @param array $data
     * @return void
     */
    public function updatePerformance($id, array $data)
    {
        if($data['maintenance_variance_to_spend'] <= 5) {
            $data['maintenance_variance_to_spend'] = $data['maintenance_variance_to_spend'] * 100;
        }
        if($data['on_time_to_destination'] <= 5) {
            $data['on_time_to_destination'] = $data['on_time_to_destination'] * 100;
        }
        if($data['on_time_to_origin'] <= 5) {
            $data['on_time_to_origin'] = $data['on_time_to_origin'] * 100;
        }
        if($data['acceptance'] <= 5) {
            $data['acceptance'] = $data['acceptance'] * 100;
        }
        $data['on_time'] = $data['on_time_to_origin'] == 0
            ? 0.5
            : ($data['on_time_to_origin'] * 0.375 + $data['on_time_to_destination'] * 0.625);

        $rule = PerformanceMetricRule::first();
        $data['acceptance_rating'] = $this->performanceCalculationsService->getRating($data['acceptance'], $rule, 'acceptance');
        $data['on_time_rating'] = $this->performanceCalculationsService->getRating($data['on_time'], $rule, 'on_time');
        $data['maintenance_variance_to_spend_rating'] = $this->performanceCalculationsService->getRating($data['maintenance_variance_to_spend'], $rule, 'maintenance_variance');
        $data['open_boc_rating'] = $this->performanceCalculationsService->getRating($data['open_boc'], $rule, 'open_boc');
        $data['meets_safety_bonus_criteria_rating'] = $this->performanceCalculationsService->getSafetyBonusRating($data['meets_safety_bonus_criteria'], $rule);
        $data['vcr_preventable_rating'] = $this->performanceCalculationsService->getRating($data['vcr_preventable'], $rule, 'vcr_preventable');
        $data['vmcr_p_rating'] = $this->performanceCalculationsService->getRating($data['vmcr_p'], $rule, 'vmcr_p');

        $performance = Performance::findOrFail($id);
        $performance->update($data);
    }

    /**
     * Delete a performance record.
     *
     * @param int $id
     * @param int|null $tenantId
     * @return void
     */
    public function deletePerformance($id, $tenantId = null)
    {
        $performance = Performance::findOrFail($id);
        if ($tenantId && $performance->tenant_id !== $tenantId) {
            abort(403, 'Unauthorized');
        }
        $performance->delete();
    }

    /**
     * Delete multiple performance entries.
     *
     * @param array $ids Array of performance IDs to delete
     * @param int|null $tenantId
     * @return void
     */
    public function deleteMultiplePerformances(array $ids, $tenantId = null)
    {
        if (empty($ids)) {
            return;
        }
        
        // For security, ensure the user can only delete performances they have access to
        $query = Performance::whereIn('id', $ids);
        
        // If not a super admin, restrict to tenant's performances
        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }
        
        $query->delete();
    }
}
