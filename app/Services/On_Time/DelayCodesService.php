<?php

namespace App\Services\On_Time;

use App\Models\DelayCode;

class DelayCodesService
{
    /**
     * Create a new delay code.
     *
     * @param array $data
     * @return void
     */
    public function createDelayCode(array $data)
    {
        DelayCode::create($data);
    }

    /**
     * Soft delete a delay code.
     *
     * @param int $id
     * @return void
     */
    public function deleteDelayCode($id)
    {
        $code = DelayCode::findOrFail($id);
        $code->delete();
    }

    /**
     * Restore a soft-deleted delay code.
     *
     * @param int $id
     * @return void
     */
    public function restoreDelayCode($id)
    {
        $code = DelayCode::withTrashed()->findOrFail($id);
        $code->restore();
    }

    /**
     * Permanently force delete a delay code.
     *
     * @param int $id
     * @return void
     */
    public function forceDeleteDelayCode($id)
    {
        $code = DelayCode::withTrashed()->findOrFail($id);
        $code->forceDelete();
    }
}
