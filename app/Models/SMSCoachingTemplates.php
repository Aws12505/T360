<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\Models\Scopes\TenantScope;

class SMSCoachingTemplates extends Model
{
    protected $table = 's_m_s_coaching_templates';

    protected $fillable = [
        'coaching_message',
        'acceptance',
        'ontime',
        'greenzone',
        'severe_alerts',
        'tenant_id',
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
