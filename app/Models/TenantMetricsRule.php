<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\Models\Scopes\TenantScope;

class TenantMetricsRule extends Model
{
    use HasFactory;

    protected $fillable = ['tenant_id', 'mvts_divisor'];

     public function tenant()
     {
         return $this->belongsTo(Tenant::class);
     }

    /**
     * Boot the model and apply TenantScope if a user is authenticated.
     *
     * @return void
     */
    protected static function booted()
    {
        if(Auth::check()) {
            static::addGlobalScope(new TenantScope);
        }
    } 
}
