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
        
        $performances = $query->paginate($perPage);
        
        $isSuperAdmin = is_null(Auth::user()->tenant_id);
        $tenantSlug = $isSuperAdmin ? null : Auth::user()->tenant->slug;
        $tenants = $isSuperAdmin ? Tenant::all() : [];
        
        return [
            'performances' => $performances,
            'tenantSlug'   => $tenantSlug,
            'SuperAdmin'   => $isSuperAdmin,
            'tenants'      => $tenants,
            'dateFilter'   => $dateFilter,
            'dateRange'    => $dateRange,
            'perPage'      => $perPage,
        ];
    }

    /**
     * Store a new performance record.
     *
     * @param array $data
     * @return void
     */
    public function storePerformance(array $data)
    {
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
}
