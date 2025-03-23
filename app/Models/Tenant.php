<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\PerformanceMetricRule;
class Tenant extends Model
{
    protected $fillable = ['name','slug'];

    public function users()
{
    return $this->hasMany(User::class);
}
public function performanceMetricRule()
{
    return $this->hasOne(PerformanceMetricRule::class);
}
}
