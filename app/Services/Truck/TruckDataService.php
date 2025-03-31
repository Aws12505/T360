<?php

namespace App\Services\Truck;

use App\Models\Truck;
use Illuminate\Support\Facades\Auth;
use App\Models\Tenant;

class TruckDataService
{
    /**
     * Get truck entries for the index view.
     */
    public function getTruckIndex()
    {
        $trucks = Truck::with('tenant')->paginate(10);
        $isSuperAdmin = is_null(Auth::user()->tenant_id);
        $tenantSlug = $isSuperAdmin ? null : Auth::user()->tenant->slug;
        $tenants = $isSuperAdmin ? Tenant::all() : [];
        return [
            'entries'     => $trucks,
            'tenantSlug' => $tenantSlug,
            'SuperAdmin' => $isSuperAdmin,
            'tenants'    => $tenants,
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
}
