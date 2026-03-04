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
        'date' => 'datetime',
        'penalty' => 'integer',
        'delay_duration' => 'integer',
        'driver_controllable' => 'boolean',
        'carrier_controllable' => 'boolean',
    ];

    /**
     * Internal flag to skip controllable enforcement.
     */
    protected bool $skipControllableEnforcement = false;

    /**
     * Allow service layer to skip automatic logic.
     */
    public function skipControllableEnforcement(): self
    {
        $this->skipControllableEnforcement = true;
        return $this;
    }
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

        static::saving(function (Delay $delay) {

            // 🚫 Skip enforcement if explicitly disabled
            if ($delay->skipControllableEnforcement) {
                return true;
            }

            if (empty($delay->delay_reason)) {
                return true;
            }

            if (preg_match('/amazon/i', $delay->delay_reason)) {
                $delay->driver_controllable = false;
                $delay->carrier_controllable = false;
                return true;
            }

            if (preg_match('/mechanical[_ ]?trailer|delayed tender/i', $delay->delay_reason)) {
                $delay->driver_controllable = false;
                $delay->carrier_controllable = false;
            }

            return true;
        });

        // 🔒 Always reset after save
        static::saved(function (Delay $delay) {
            $delay->skipControllableEnforcement = false;
        });
    }
}
