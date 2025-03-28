<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\TenantScope;
use Illuminate\Support\Facades\Auth;

/**
 * Class Delay
 *
 * Represents a delay event.
 *
 * Properties:
 * - delay_type: 'origin' or 'destination'.
 * - delay_category: Specifies the penalty range.
 * - penalty: The computed penalty.
 *
 * Relationships:
 * - Belongs to a Tenant.
 * - Belongs to a DelayCode.
 */
class Delay extends Model
{
    protected $fillable = [
        'tenant_id',
        'date',
        'delay_type',
        'driver_name',
        'delay_category',
        'penalty',
        'delay_code_id',
        'disputed',
        'driver_controllable'
    ];

    /**
     * Get the delay code associated with the delay.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function delayCode()
    {
        return $this->belongsTo(DelayCode::class);
    }

    /**
     * Get the tenant that the delay belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Boot the model and apply TenantScope if a user is authenticated.
     *
     * @return void
     */
    protected static function booted()
    {
        if(Auth::check()) {
            static::addGlobalScope(new TenantScope);
        }
    }
}
