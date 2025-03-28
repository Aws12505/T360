<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\TenantScope;
use Illuminate\Support\Facades\Auth;

/**
 * Class Rejection
 *
 * Represents a rejection event with details such as type, penalty, and associated reason code.
 *
 * Properties:
 * - rejection_type: Either 'block' or 'load'.
 * - rejection_category: Indicates timing of rejection (more_than_6, within_6, after_start).
 * - penalty: The numeric penalty associated.
 *
 * Relationships:
 * - Belongs to a Tenant.
 * - Belongs to a RejectionReasonCode.
 */
class Rejection extends Model
{
    protected $fillable = [
        'tenant_id',
        'date',
        'rejection_type',
        'driver_name',
        'rejection_category',
        'penalty',
        'reason_code_id',
        'disputed',
        'driver_controllable'
    ];

    /**
     * Get the rejection reason code associated with the rejection.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function reasonCode()
    {
        return $this->belongsTo(RejectionReasonCode::class);
    }

    /**
     * Get the tenant associated with the rejection.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Boot the model and apply the TenantScope if a user is authenticated.
     *
     * @return void
     */
    protected static function booted()
    {
        if (Auth::check()) {
            static::addGlobalScope(new TenantScope);
        }
    }
}
