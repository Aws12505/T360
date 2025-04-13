<?php

namespace App\Services\Maintenance;

use App\Models\Vendor;

class VendorsService
{
    /**
     * Create a new vendor.
     *
     * @param array $data
     * @return void
     */
    public function createVendor(array $data)
    {
        Vendor::create($data);
    }

    /**
     * Soft delete a vendor.
     *
     * @param int $id
     * @return void
     */
    public function deleteVendor($id)
    {
        $vendor = Vendor::findOrFail($id);
        $vendor->delete();
    }
    
    /**
     * Restore a soft-deleted vendor.
     *
     * @param int $id
     * @return void
     */
    public function restoreVendor($id)
    {
        $vendor = Vendor::withTrashed()->findOrFail($id);
        $vendor->restore();
    }

    /**
     * Permanently force delete a vendor.
     *
     * @param int $id
     * @return void
     */
    public function forceDeleteVendor($id)
    {
        $vendor = Vendor::withTrashed()->findOrFail($id);
        $vendor->forceDelete();
    }
}
