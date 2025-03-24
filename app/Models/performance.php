<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\TenantScope;
use Illuminate\Support\Facades\Auth;
class Performance extends Model
{
    use HasFactory;

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
        'acceptance_rating',
        'on_time_rating',
        'maintenance_variance_to_spend_rating',
        'open_boc_rating',
        'meets_safety_bonus_criteria_rating',
        'vcr_preventable_rating',
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
