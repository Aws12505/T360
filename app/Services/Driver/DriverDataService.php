<?php

namespace App\Services\Driver;

use App\Models\Driver;
use App\Models\Tenant;
use Illuminate\Support\Facades\Auth;

class DriverDataService
{
    /**
     * Get driver entries for the index view.
     */
    public function getDriverIndex()
    {
        $drivers = Driver::with('tenant')->paginate(10);
        $isSuperAdmin = is_null(Auth::user()->tenant_id);
        $tenantSlug = $isSuperAdmin ? null : Auth::user()->tenant->slug;
        $tenants = $isSuperAdmin ? Tenant::all() : [];
        return [
            'entries'    => $drivers,
            'tenantSlug' => $tenantSlug,
            'SuperAdmin' => $isSuperAdmin,
            'tenants'    => $tenants,
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
}
