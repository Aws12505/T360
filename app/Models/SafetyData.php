<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\TenantScope;
use Illuminate\Support\Facades\Auth;

/**
 * Class SafetyData
 *
 * Represents safety data entries containing various metrics per driver.
 *
 * Properties include metrics such as minutes analyzed, percentages,
 * driver scores, and other event-based measurements.
 *
 * Relationships:
 * - Belongs to a Tenant.
 */
class SafetyData extends Model
{
    use HasFactory;

    protected $table = 'safety_data';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tenant_id', 'driver_name', 'user_name', 'group', 'group_hierarchy', 'minutes_analyzed', 'green_minutes_percent',
        'overspeeding_percent', 'driver_score', 'total_events_avg_fd_impact', 'average_following_distance_sec',
        'average_following_distance_gz_impact', 'total_events', 'high_g', 'low_impact', 'driver_initiated',
        'potential_collision', 'sign_violations', 'sign_violations_gz_impact', 'traffic_light_violation',
        'traffic_light_violation_gz_impact', 'u_turn', 'u_turn_gz_impact', 'hard_braking', 'hard_braking_gz_impact',
        'hard_turn', 'hard_turn_gz_impact', 'hard_acceleration', 'hard_acceleration_gz_impact', 'driver_distraction',
        'driver_distraction_gz_impact', 'following_distance', 'following_distance_gz_impact', 'speeding_violations',
        'speeding_violations_gz_impact', 'seatbelt_compliance', 'camera_obstruction', 'driver_drowsiness', 'weaving',
        'weaving_gz_impact', 'swerve', 'collision_warning', 'collision_warning_gz_impact', 'requested_video', 'backing',
        'roadside_parking', 'lane_conduct', 'driver_distracted_hard_brake', 'following_distance_hard_brake',
        'driver_distracted_following_distance', 'driver_star', 'driver_star_gz_impact', 'vehicle_type',
        'safety_normalisation_factor', 'date',
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
