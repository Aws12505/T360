<?php

namespace App\Services\MilesDriven;

use App\Models\MilesDriven;
use App\Models\Tenant;
use App\Services\Filtering\FilteringService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class MilesDrivenService
{
    protected $filteringService;

    public function __construct(FilteringService $filteringService)
    {
        $this->filteringService = $filteringService;
    }

    /**
     * Get miles driven entries for the index view.
     *
     * @return array
     */
    public function getMilesDrivenIndex()
    {
        $query = MilesDriven::with('tenant');
        
        // Apply date filtering if requested
        $dateFilter = $this->filteringService->getDateFilter();
        $dateRange = [];
        
        if ($dateFilter !== 'full') {
            $query = $this->filteringService->applyDateFilter($query, $dateFilter, 'week_start_date', $dateRange);
        }
        
        // Get per page value
        $perPage = $this->filteringService->getPerPage(Request::input('perPage', 10));
        
        // Apply tenant filter for non-admin users
        if (!is_null(Auth::user()->tenant_id)) {
            $query->where('tenant_id', Auth::user()->tenant_id);
        }
        
        $milesDriven = $query->latest('week_start_date')->paginate($perPage);
        
        $isSuperAdmin = is_null(Auth::user()->tenant_id);
        $tenantSlug = $isSuperAdmin ? null : Auth::user()->tenant->slug;
        $tenants = $isSuperAdmin ? Tenant::all() : [];
        
        return [
            'entries'    => $milesDriven,
            'tenantSlug' => $tenantSlug,
            'SuperAdmin' => $isSuperAdmin,
            'tenants'    => $tenants,
            'dateRange'  => $dateRange,
            'dateFilter' => $dateFilter,
        ];
    }

    /**
     * Create a new miles driven entry.
     *
     * @param array $data
     * @return MilesDriven
     */
    public function createMilesDriven(array $data)
    {
        return MilesDriven::create($data);
    }

    /**
     * Update an existing miles driven entry.
     *
     * @param int $id
     * @param array $data
     * @return MilesDriven
     */
    public function updateMilesDriven($id, array $data)
    {
        $milesDriven = MilesDriven::findOrFail($id);
        $milesDriven->update($data);
        return $milesDriven;
    }

    /**
     * Delete a miles driven entry.
     *
     * @param int $id
     * @return void
     */
    public function deleteMilesDriven($id)
    {
        $milesDriven = MilesDriven::findOrFail($id);
        $milesDriven->delete();
    }
}