<?php

namespace App\Services\Maintenance;

use App\Models\AreaOfConcern;

class AreasOfConcernService
{
    /**
     * Create a new area of concern.
     *
     * @param array $data
     * @return void
     */
    public function createAreaOfConcern(array $data)
    {
        AreaOfConcern::create($data);
    }

    /**
     * Soft delete an area of concern.
     *
     * @param int $id
     * @return void
     */
    public function deleteAreaOfConcern($id)
    {
        $areaOfConcern = AreaOfConcern::findOrFail($id);
        $areaOfConcern->delete();
    }
    
    /**
     * Restore a soft-deleted area of concern.
     *
     * @param int $id
     * @return void
     */
    public function restoreAreaOfConcern($id)
    {
        $areaOfConcern = AreaOfConcern::withTrashed()->findOrFail($id);
        $areaOfConcern->restore();
    }

    /**
     * Permanently force delete an area of concern.
     *
     * @param int $id
     * @return void
     */
    public function forceDeleteAreaOfConcern($id)
    {
        $areaOfConcern = AreaOfConcern::withTrashed()->findOrFail($id);
        $areaOfConcern->forceDelete();
    }
}
