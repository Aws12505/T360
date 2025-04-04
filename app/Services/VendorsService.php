<?php

namespace App\Services;

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
     * Delete a vendor.
     *
     * @param int $id
     * @return void
     */
    public function deleteVendor($id)
    {
        $vendor = Vendor::findOrFail($id);
        $vendor->delete();
    }
}
