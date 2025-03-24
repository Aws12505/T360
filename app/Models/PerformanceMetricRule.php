<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\TenantScope;
use Illuminate\Support\Facades\Auth;
class PerformanceMetricRule extends Model
{
    use HasFactory;
    protected $casts = [
        'safety_bonus_eligible_levels' => 'array', // NEW
    ];
    protected $fillable = [

        // Acceptance
        'acceptance_fantastic_plus',
        'acceptance_fantastic_plus_operator',
        'acceptance_fantastic',
        'acceptance_fantastic_operator',
        'acceptance_good',
        'acceptance_good_operator',
        'acceptance_fair',
        'acceptance_fair_operator',
        'acceptance_poor',
        'acceptance_poor_operator',

        // On-Time
        'on_time_fantastic_plus',
        'on_time_fantastic_plus_operator',
        'on_time_fantastic',
        'on_time_fantastic_operator',
        'on_time_good',
        'on_time_good_operator',
        'on_time_fair',
        'on_time_fair_operator',
        'on_time_poor',
        'on_time_poor_operator',

        // Maintenance Variance
        'maintenance_variance_fantastic_plus',
        'maintenance_variance_fantastic_plus_operator',
        'maintenance_variance_fantastic',
        'maintenance_variance_fantastic_operator',
        'maintenance_variance_good',
        'maintenance_variance_good_operator',
        'maintenance_variance_fair',
        'maintenance_variance_fair_operator',
        'maintenance_variance_poor',
        'maintenance_variance_poor_operator',

        // Open BOC
        'open_boc_fantastic_plus',
        'open_boc_fantastic_plus_operator',
        'open_boc_fantastic',
        'open_boc_fantastic_operator',
        'open_boc_good',
        'open_boc_good_operator',
        'open_boc_fair',
        'open_boc_fair_operator',
        'open_boc_poor',
        'open_boc_poor_operator',

        // Safety Bonus
        'safety_bonus_eligible_levels',

        // VCR Preventable
        'vcr_preventable_fantastic_plus',
        'vcr_preventable_fantastic_plus_operator',
        'vcr_preventable_fantastic',
        'vcr_preventable_fantastic_operator',
        'vcr_preventable_good',
        'vcr_preventable_good_operator',
        'vcr_preventable_fair',
        'vcr_preventable_fair_operator',
        'vcr_preventable_poor',
        'vcr_preventable_poor_operator',
    ];

}
