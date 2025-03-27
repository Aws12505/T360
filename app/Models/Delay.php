<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\TenantScope;
use Illuminate\Support\Facades\Auth;
class Delay extends Model
{
    protected $fillable = [
        'tenant_id',
        'date',
        'delay_type',
        'driver_name',
        'delay_category',
        'penalty',
        'delay_code_id',
        'disputed',
        'driver_controllable'
    ];

    public function delayCode()
    {
        return $this->belongsTo(DelayCode::class);
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
