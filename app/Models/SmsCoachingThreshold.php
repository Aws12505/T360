<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsCoachingThreshold extends Model
{
    protected $fillable = [
        'metric_key',
        'good',
        'minor_improvement',
        'bad',
        'tenant_id',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
