<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;
use App\Models\Scopes\TenantScope;
use Illuminate\Support\Facades\Auth;

/**
 * Class Role
 *
 * Extends Spatie's Role model to support multi-tenancy.
 *
 * Global Scope:
 * - Filters roles based on the authenticated user's tenant_id.
 *
 * Overrides:
 * - The create method to automatically assign the tenant_id.
 */
class Role extends SpatieRole
{
    /**
     * Boot the model and apply the TenantScope global scope.
     *
     * @return void
     */
    protected static function booted()
    {
        if (Auth::check()) {
            static::addGlobalScope(new TenantScope);
        }
    }

    /**
     * Override the create method to automatically assign tenant_id.
     *
     * @param array $attributes
     * @return static
     */
    public static function create(array $attributes = [])
    {
        if (Auth::check() && Auth::user()->tenant_id !== null) {
            $attributes['tenant_id'] = Auth::user()->tenant_id;
        }
        return parent::create($attributes);
    }
}
