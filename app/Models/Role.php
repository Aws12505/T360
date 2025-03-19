<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    /**
     * Apply a global scope to filter roles by tenant_id.
     */
    protected static function booted()
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (Auth::check()) {
                $builder->where('tenant_id', Auth::user()->tenant_id);
            }
        });
    }

    /**
     * Override the create method to ensure tenant_id is assigned.
     */
    public static function create(array $attributes = [])
    {
        if (Auth::check() && Auth::user()->tenant_id !== null) {
            $attributes['tenant_id'] = Auth::user()->tenant_id;
        }

        return parent::create($attributes);
    }
}
