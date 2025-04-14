<?php

namespace App\Services\Truck;

use App\Models\Truck;
use Illuminate\Support\Facades\Auth;
use App\Models\Tenant;
use Illuminate\Support\Facades\Request;

class TruckDataService
{
    /**
     * Get truck entries for the index view.
     */
    public function getTruckIndex()
    {
        // Get per page parameter from request, default to 10
        $perPage = Request::input('perPage', 10);
        
        $trucks = Truck::with('tenant')->paginate($perPage);
        $isSuperAdmin = is_null(Auth::user()->tenant_id);
        $tenantSlug = $isSuperAdmin ? null : Auth::user()->tenant->slug;
        $tenants = $isSuperAdmin ? Tenant::all() : [];
        return [
            'entries'     => $trucks,
            'tenantSlug' => $tenantSlug,
            'SuperAdmin' => $isSuperAdmin,
            'tenants'    => $tenants,
            'perPage'    => $perPage,
        ];
    }

    /**
     * Create a new truck entry.
     */
    public function createTruck(array $data)
    {
        Truck::create($data);
    }

    /**
     * Update an existing truck entry.
     */
    public function updateTruck($id, array $data)
    {
        $truck = Truck::findOrFail($id);
        $truck->update($data);
    }

    /**
     * Delete a truck entry.
     */
    public function deleteTruck($id)
    {
        $truck = Truck::findOrFail($id);
        $truck->delete();
    }

    /**
     * Delete multiple truck entries.
     */
    public function deleteMultipleTrucks(array $ids)
    {
        if (empty($ids)) {
            return;
        }
        
        Truck::whereIn('id', $ids)->delete();
    }
}
