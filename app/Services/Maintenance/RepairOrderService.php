<?php

namespace App\Services\Maintenance;

use App\Models\RepairOrder;
use App\Models\Truck;
use App\Models\Vendor;
use App\Models\AreaOfConcern;
use App\Models\Tenant;
use Illuminate\Support\Facades\Auth;

/**
 * Class RepairOrderService
 *
 * Contains business logic for repair order operations, including CRUD, import, and export.
 */
class RepairOrderService
{
    /**
     * Get repair order entries for the index view.
     *
     * @return array
     */
    public function getIndexData(): array
    {
        $repairOrders = RepairOrder::with(['truck', 'vendor', 'areasOfConcern', 'tenant'])->latest()->paginate(10);
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
