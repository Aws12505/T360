<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\TenantScope;
use Illuminate\Support\Facades\Auth;


class Delay extends Model
{
    protected $fillable = [
        'tenant_id',
        'date',
        'delay_type',
        'driver_name',
        'delay_category',
        'penalty',
        'disputed',
        'driver_controllable',
        'carrier_controllable',
        'delay_duration',
        'delay_reason',
        'load_id',
    ];

    protected $casts = [
        'date'               => 'datetime',
        'penalty'            => 'integer',
        'delay_duration'     => 'integer',
        'driver_controllable' => 'boolean',
        'carrier_controllable' => 'boolean',
    ];
    /**
     * Get the tenant that the delay belongs to.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Boot the model and apply TenantScope if a user is authenticated.
     */
    protected static function booted()
    {
        if (Auth::check()) {
            static::addGlobalScope(new TenantScope);
        }
    }
}
