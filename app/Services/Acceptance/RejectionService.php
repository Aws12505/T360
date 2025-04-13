<?php

namespace App\Services\Acceptance;

use App\Models\Rejection;
use App\Models\RejectionReasonCode;
use Illuminate\Support\Facades\Auth;
use App\Models\Tenant;
use App\Services\Filtering\FilteringService;

/**
 * Class RejectionService
 *
 * Contains business logic for rejection management and reason code operations.
 *
 * Created manually: touch app/Services/RejectionService.php
 */
class RejectionService
{
    protected FilteringService $filteringService;

    /**
     * Constructor.
     *
     * @param FilteringService $filteringService Service for filtering and pagination.
     */
    public function __construct(FilteringService $filteringService)
    {
        $this->filteringService = $filteringService;
    }

    /**
     * Get rejection data for the index view.
     *
     * @return array
     */
    public function getRejectionsIndex(): array
    {
        $user = Auth::user();
        $isSuperAdmin = is_null($user->tenant_id);
        
        // Get filtering parameters
        $dateFilter = $this->filteringService->getDateFilter();
        $perPage = $this->filteringService->getPerPage();
        
        // Build query
        $query = Rejection::with([
            'tenant',
            'reasonCode' => function ($query) {
                $query->withTrashed();
            }
        ]);
        
        // Apply date filtering
        $dateRange = [];
        $query = $this->filteringService->applyDateFilter($query, $dateFilter, 'date', $dateRange);
        
        // Paginate results
        $rejections = $query->paginate($perPage);
        
        return [
            'rejections'           => $rejections,
            'tenantSlug'           => $isSuperAdmin ? null : $user->tenant->slug,
            'isSuperAdmin'         => $isSuperAdmin,
            'tenants'              => $isSuperAdmin ? Tenant::all() : [],
            'rejection_reason_codes' => RejectionReasonCode::withTrashed()->get(),
            'dateFilter'           => $dateFilter,
            'dateRange'            => $dateRange,
            'perPage'              => $perPage,
        ];
    }

    /**
     * Create a new rejection.
     *
     * @param array $data
     * @return void
     */
    public function createRejection(array $data)
    {
        $user = Auth::user();
        $data['tenant_id'] = is_null($user->tenant_id) ? $data['tenant_id'] : $user->tenant_id;
        $data['penalty'] = match ($data['rejection_category']) {
            'more_than_6' => 1,
            'within_6'    => 4,
            'after_start' => 8,
        };
        Rejection::create($data);
    }

    /**
     * Update an existing rejection.
     *
     * @param int $id
     * @param array $data
     * @return void
     */
    public function updateRejection($id, array $data)
    {
        $user = Auth::user();
        $data['tenant_id'] = is_null($user->tenant_id) ? $data['tenant_id'] : $user->tenant_id;
        $data['penalty'] = match ($data['rejection_category']) {
            'more_than_6' => 1,
            'within_6'    => 4,
            'after_start' => 8,
        };
        $rejection = Rejection::findOrFail($id);
        $rejection->update($data);
    }

    /**
     * Delete a rejection.
     *
     * @param int $id
     * @return void
     */
    public function deleteRejection($id)
    {
        $rejection = Rejection::findOrFail($id);
        $rejection->delete();
    }
}
