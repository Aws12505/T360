<?php

namespace App\Services;

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
     * Delete an area of concern.
     *
     * @param int $id
     * @return void
     */
    public function deleteAreaOfConcern($id)
    {
        $areaOfConcern = AreaOfConcern::findOrFail($id);
        $areaOfConcern->delete();
    }
}
