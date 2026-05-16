<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsCoachingTenantSetting extends Model
{
    protected $fillable = [
        'tenant_id',
        'metric_key',
        'enabled',
    ];

    protected $casts = [
        'enabled' => 'bool',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
