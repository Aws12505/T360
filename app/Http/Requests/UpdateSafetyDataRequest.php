<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

/**
 * Class UpdateSafetyDataRequest
 *
 * Validates data for updating an existing safety data entry.
 *
 * Command:
 *   php artisan make:request UpdateSafetyDataRequest
 */
class UpdateSafetyDataRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return array_merge([
            'tenant_id' => 'required|exists:tenants,id',
            'user_name'        => 'required|string',
            'driver_name'      => 'nullable|string',
            'group'            => 'nullable|string',
            'group_hierarchy'  => 'nullable|string',
            'date'             => 'required|date',
            'vehicle_type'     => 'nullable|string',
        ], array_fill_keys([
            'minutes_analyzed', 'green_minutes_percent', 'overspeeding_percent', 'driver_score',
            'total_events_avg_fd_impact', 'average_following_distance_sec', 'average_following_distance_gz_impact',
            'total_events', 'high_g', 'low_impact', 'driver_initiated', 'potential_collision',
            'sign_violations', 'sign_violations_gz_impact', 'traffic_light_violation', 'traffic_light_violation_gz_impact',
            'u_turn', 'u_turn_gz_impact', 'hard_braking', 'hard_braking_gz_impact', 'hard_turn', 'hard_turn_gz_impact',
            'hard_acceleration', 'hard_acceleration_gz_impact', 'driver_distraction', 'driver_distraction_gz_impact',
            'following_distance', 'following_distance_gz_impact', 'speeding_violations', 'speeding_violations_gz_impact',
            'seatbelt_compliance', 'camera_obstruction', 'driver_drowsiness', 'weaving', 'weaving_gz_impact',
            'collision_warning', 'collision_warning_gz_impact', 'requested_video', 'backing', 'roadside_parking',
            'driver_distracted_hard_brake', 'following_distance_hard_brake', 'driver_distracted_following_distance',
            'driver_star', 'driver_star_gz_impact', 'safety_normalisation_factor'
        ], ['nullable', 'numeric']));
    }    
    protected function prepareForValidation()
{
    // If the authenticated user is not a SuperAdmin, always use the user's tenant_id.
    if (!is_null(Auth::user()->tenant_id)) { 
        $this->merge(['tenant_id' => Auth::user()->tenant_id]); 
    }
}
}
