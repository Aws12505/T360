<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\TenantScope;
use Illuminate\Support\Facades\Auth;

class SafetyData extends Model
{
    use HasFactory;

    protected $table = 'safety_data';

    protected $fillable = [
        'tenant_id',
        'driver_name',
        'user_name',
        'group',
        'group_hierarchy',
        'minutes_analyzed',
        'green_minutes_percent',
        'overspeeding_percent',
        'driver_score',
        'total_events',
        'average_following_distance',
        'sign_violations',
        'traffic_light_violation',
        'driver_distraction',
        'following_distance',
        'speeding_violations',
        'driver_drowsiness',
        'driver_star',
        'vehicle_type',
        'safety_normalisation_factor',
        'date',
    ];
    

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
    protected static function booted()
    {
        if(Auth::user())
        static::addGlobalScope(new TenantScope);
    }
    
}
