<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Tenant
 *
 * Represents a tenant (or client company) in the multi-tenant application.
 *
 * Properties:
 * - id: The primary key.
 * - name: The tenant's unique name.
 * - slug: The unique slug identifier.
 *
 * Relationships:
 * - Has many Users.
 * - Has one PerformanceMetricRule.
 */
class Tenant extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['name', 'slug', 'timezone'];

    /**
     * Get the users that belong to the tenant.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the performance metric rule for the tenant.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function performanceMetricRule()
    {
        return $this->hasOne(PerformanceMetricRule::class);
    }

    /**
     * Get the subscription associated with the tenant.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function subscription()
    {
        return $this->hasOne(Subscription::class);
    }
    
    /**
     * Get the safety thresholds associated with the tenant.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function safetyThresholds()
    {
        return $this->hasMany(SafetyThreshold::class);
    }
}
