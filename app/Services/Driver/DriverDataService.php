<?php

namespace App\Services\Driver;

use App\Models\Driver;
use App\Models\Tenant;
use App\Services\Filtering\FilteringService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class DriverDataService
{
    protected $filteringService;

    public function __construct(FilteringService $filteringService)
    {
        $this->filteringService = $filteringService;
    }

    /**
     * Get driver entries for the index view.
     */
    public function getDriverIndex()
    {
        $query = Driver::query()->with('tenant');
        
        // Apply date filtering if requested
        $dateFilter = $this->filteringService->getDateFilter();
        $dateRange = [];
        
        if ($dateFilter !== 'full') {
            $query = $this->filteringService->applyDateFilter($query, $dateFilter, 'hiring_date', $dateRange);
        }
        
        // Get per page value
        $perPage = $this->filteringService->getPerPage(Request::input('perPage', 10));
        
        // Apply tenant filter for non-admin users
        if (!is_null(Auth::user()->tenant_id)) {
            $query->where('tenant_id', Auth::user()->tenant_id);
        }
        $drivers = $query->paginate($perPage);
        $isSuperAdmin = is_null(Auth::user()->tenant_id);
        $tenantSlug = $isSuperAdmin ? null : Auth::user()->tenant->slug;
        $tenants = $isSuperAdmin ? Tenant::all() : [];
        
        return [
            'entries'    => $drivers,
            'tenantSlug' => $tenantSlug,
            'SuperAdmin' => $isSuperAdmin,
            'tenants'    => $tenants,
            'dateRange'  => $dateRange,
            'dateFilter' => $dateFilter,
        ];
    }

    /**
     * Create a new driver entry.
     */
    public function createDriver(array $data)
    {
        Driver::create($data);
    }

    /**
     * Update an existing driver entry.
     */
    public function updateDriver($id, array $data)
    {
        $driver = Driver::findOrFail($id);
        $driver->update($data);
    }

    /**
     * Delete a driver entry.
     */
    public function deleteDriver($id)
    {
        $driver = Driver::findOrFail($id);
        $driver->delete();
    }

    /**
     * Delete multiple driver entries.
     *
     * @param array $ids Array of driver IDs to delete
     * @return void
     */
    public function deleteMultipleDrivers(array $ids)
    {
        if (empty($ids)) {
            return;
        }
        
        // For security, ensure the user can only delete drivers they have access to
        $query = Driver::whereIn('id', $ids);
        
        // If not a super admin, restrict to tenant's drivers
        $user = Auth::user();
        if (!is_null($user->tenant_id)) {
            $query->where('tenant_id', $user->tenant_id);
        }
        
        $query->delete();
    }
}
