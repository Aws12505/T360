<?php

namespace App\Services\MilesDriven;

use App\Models\MilesDriven;
use App\Models\Tenant;
use Illuminate\Support\Facades\Auth;

class MilesDrivenService
{
    /**
     * Get miles driven entries for the index view.
     *
     * @return array
     */
    public function getMilesDrivenIndex()
    {
        $milesDriven = MilesDriven::with('tenant')->paginate(10);
        $isSuperAdmin = is_null(Auth::user()->tenant_id);
        $tenantSlug = $isSuperAdmin ? null : Auth::user()->tenant->slug;
        $tenants = $isSuperAdmin ? Tenant::all() : [];
        
        return [
            'entries'    => $milesDriven,
            'tenantSlug' => $tenantSlug,
            'SuperAdmin' => $isSuperAdmin,
            'tenants'    => $tenants,
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