<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
class SMSScoresThresholds extends Model
{
    protected $fillable = [
        'tenant_id',
        'on_time_good', 'on_time_bad', 'on_time_minor_improvement',
        'acceptance_good', 'acceptance_bad', 'acceptance_minor_improvement',
        'greenzone_score_good', 'greenzone_score_bad', 'greenzone_score_minor_improvement',
        'severe_alerts_good', 'severe_alerts_bad', 'severe_alerts_minor_improvement',
    ];
/**
     * Get the tenant that the safety data belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Boot the model and apply the TenantScope global scope if a user is authenticated.
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
