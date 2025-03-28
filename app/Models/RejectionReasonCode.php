<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class RejectionReasonCode
 *
 * Represents a unique code for a rejection reason.
 *
 * Properties:
 * - reason_code: The unique string representing the reason.
 *
 * Relationships:
 * - Has many Rejections.
 */
class RejectionReasonCode extends Model
{
    protected $fillable = ['reason_code'];

    /**
     * Get the rejections associated with this reason code.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function rejections()
    {
        return $this->hasMany(Rejection::class);
    }
}
