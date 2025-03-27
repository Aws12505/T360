<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\TenantScope;
use Illuminate\Support\Facades\Auth;
class Rejection extends Model
{
    protected $fillable = [
        'tenant_id',
        'date',
        'rejection_type',
        'driver_name',
        'rejection_category',
        'penalty',
        'reason_code_id',
        'disputed',
        'driver_controllable'
    ];

    public function reasonCode()
    {
        return $this->belongsTo(RejectionReasonCode::class);
    }
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
