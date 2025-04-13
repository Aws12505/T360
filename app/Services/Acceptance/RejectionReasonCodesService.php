<?php

namespace App\Services\Acceptance;

use App\Models\RejectionReasonCode;

class RejectionReasonCodesService
{
    /**
     * Create a new rejection reason code.
     *
     * @param array $data
     * @return void
     */
    public function createReasonCode(array $data)
    {
        RejectionReasonCode::create($data);
    }

    /**
     * Soft delete a rejection reason code.
     *
     * @param int $id
     * @return void
     */
    public function deleteReasonCode($id)
    {
        $code = RejectionReasonCode::findOrFail($id);
        $code->delete();
    }

    /**
     * Restore a soft-deleted rejection reason code.
     *
     * @param int $id
     * @return void
     */
    public function restoreReasonCode($id)
    {
        $code = RejectionReasonCode::withTrashed()->findOrFail($id);
        $code->restore();
    }

    /**
     * Permanently force delete a rejection reason code.
     *
     * @param int $id
     * @return void
     */
    public function forceDeleteReasonCode($id)
    {
        $code = RejectionReasonCode::withTrashed()->findOrFail($id);
        $code->forceDelete();
    }
}