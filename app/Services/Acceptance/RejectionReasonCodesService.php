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
     * Delete a rejection reason code.
     *
     * @param int $id
     * @return void
     */
    public function deleteReasonCode($id)
    {
        $reasonCode = RejectionReasonCode::findOrFail($id);
        $reasonCode->delete();
    }
}