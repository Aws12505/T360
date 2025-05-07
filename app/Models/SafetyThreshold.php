<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\TenantScope;
use Illuminate\Support\Facades\Auth;

/**
 * Class SafetyThreshold
 *
 * Represents thresholds for safety metrics with good and bad values.
 *
 * Properties:
 * - tenant_id: The ID of the tenant this threshold belongs to.
 * - metric_name: The name of the safety metric (corresponds to a column in safety_data).
 * - good_threshold: The threshold value for "good" performance.
 * - bad_threshold: The threshold value for "bad" performance.
 * - good_enabled: Whether the good threshold is enabled.
 * - bad_enabled: Whether the bad threshold is enabled.
 *
 * Relationships:
 * - Belongs to a Tenant.
 */
class SafetyThreshold extends Model
{
    use HasFactory;

    protected $table = 'safety_thresholds';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tenant_id',
        'metric_name',
        'good_threshold',
        'bad_threshold',
        'good_enabled',
        'bad_enabled',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'good_threshold' => 'float',
        'bad_threshold' => 'float',
        'good_enabled' => 'boolean',
        'bad_enabled' => 'boolean',
    ];

    /**
     * Get the tenant that the safety threshold belongs to.
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