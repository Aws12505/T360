<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\TenantScope;
use Illuminate\Support\Facades\Auth;

/**
 * Class Performance
 *
 * Represents a performance record containing various performance metrics
 * and their calculated ratings.
 *
 * Properties include:
 * - acceptance, on_time metrics, maintenance_variance_to_spend, open_boc, meets_safety_bonus_criteria, vcr_preventable, vmcr_p.
 * - Calculated rating fields: acceptance_rating, on_time_rating, etc.
 *
 * Relationships:
 * - Belongs to a Tenant.
 */
class Performance extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tenant_id',
        'date',
        'acceptance',
        'on_time_to_origin',
        'on_time_to_destination',
        'on_time',
        'maintenance_variance_to_spend',
        'open_boc',
        'meets_safety_bonus_criteria',
        'vcr_preventable',
        'vmcr_p',
        'acceptance_rating',
        'on_time_rating',
        'maintenance_variance_to_spend_rating',
        'open_boc_rating',
        'meets_safety_bonus_criteria_rating',
        'vcr_preventable_rating',
        'vmcr_p_rating',
    ];

    /**
     * Get the tenant associated with the performance record.
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
        if(Auth::check()) {
            static::addGlobalScope(new TenantScope);
        }
    }
}
