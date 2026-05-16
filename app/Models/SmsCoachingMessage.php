<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsCoachingMessage extends Model
{
    protected $fillable = [
        'metric_key',
        'status',
        'message',
        'tenant_id',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
