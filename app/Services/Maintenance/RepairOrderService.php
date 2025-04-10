<?php

namespace App\Services\Maintenance;

use App\Models\RepairOrder;
use App\Models\Truck;
use App\Models\Vendor;
use App\Models\AreaOfConcern;
use App\Models\Tenant;
use App\Services\Filtering\FilteringService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

/**
 * Class RepairOrderService
 *
 * Contains business logic for repair order operations, including CRUD, import, and export.
 */
class RepairOrderService
{
    protected $filteringService;

    public function __construct(FilteringService $filteringService)
    {
        $this->filteringService = $filteringService;
    }

    /**
     * Get repair order entries for the index view.
     *
     * @return array
     */
    public function getIndexData(): array
    {
        $query = RepairOrder::with(['truck', 'vendor', 'areasOfConcern', 'tenant']);
        
        // Apply date filtering if requested
        $dateFilter = $this->filteringService->getDateFilter();
        $dateRange = [];
        
        if ($dateFilter !== 'full') {
            $query = $this->filteringService->applyDateFilter($query, $dateFilter, 'ro_open_date', $dateRange);
        }
        
        // Get per page value
        $perPage = $this->filteringService->getPerPage(Request::input('perPage', 10));
        
        // Apply tenant filter for non-admin users
        if (!is_null(Auth::user()->tenant_id)) {
            $query->where('tenant_id', Auth::user()->tenant_id);
        }
        
        $repairOrders = $query->latest()->paginate($perPage);
        
        $isSuperAdmin = is_null(Auth::user()->tenant_id);
        $tenantSlug = $isSuperAdmin ? null : Auth::user()->tenant->slug;
        $tenants = $isSuperAdmin ? Tenant::all() : [];
        
        return [
            'repairOrders' => $repairOrders,
            'tenantSlug' => $tenantSlug,
            'SuperAdmin' => $isSuperAdmin,
            'tenants' => $tenants,
            'trucks' => Truck::get(),
            'vendors' => Vendor::get(),
            'areasOfConcern' => AreaOfConcern::get(),
            'dateRange' => $dateRange,
            'dateFilter' => $dateFilter,
        ];
    }

    /**
     * Create a new repair order entry.
     *
     * @param array $data
     * @return RepairOrder
     */
    public function createRepairOrder(array $data)
    {
        $repairOrder = RepairOrder::create($data);
        if (isset($data['area_of_concerns'])) {
            $repairOrder->areasOfConcern()->sync($data['area_of_concerns']);
        }
        return $repairOrder;
    }

    /**
     * Update an existing repair order entry.
     *
     * @param int $id
     * @param array $data
     * @return RepairOrder
     */
    public function updateRepairOrder($id, array $data)
    {
        $repairOrder = RepairOrder::findOrFail($id);
        $repairOrder->update($data);
        if (isset($data['area_of_concerns'])) {
            $repairOrder->areasOfConcern()->sync($data['area_of_concerns']);
        }
        return $repairOrder;
    }

    /**
     * Delete a repair order entry.
     *
     * @param int $id
     * @return bool
     */
    public function deleteRepairOrder($id)
    {
        $repairOrder = RepairOrder::findOrFail($id);
        $repairOrder->delete();
        return true;
    }
}
