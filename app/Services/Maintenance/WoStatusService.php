<?php

namespace App\Services\Maintenance;

use App\Models\WoStatus;

class WoStatusService
{
    /**
     * Create a new work order status.
     *
     * @param array $data
     * @return void
     */
    public function createWoStatus(array $data)
    {
        WoStatus::create($data);
    }

    /**
     * Soft delete a work order status.
     *
     * @param int $id
     * @return void
     */
    public function deleteWoStatus($id)
    {
        $woStatus = WoStatus::findOrFail($id);
        $woStatus->delete();
    }
    
    /**
     * Restore a soft-deleted work order status.
     *
     * @param int $id
     * @return void
     */
    public function restoreWoStatus($id)
    {
        $woStatus = WoStatus::withTrashed()->findOrFail($id);
        $woStatus->restore();
    }

    /**
     * Permanently force delete a work order status.
     *
     * @param int $id
     * @return void
     */
    public function forceDeleteWoStatus($id)
    {
        $woStatus = WoStatus::withTrashed()->findOrFail($id);
        $woStatus->forceDelete();
    }
}