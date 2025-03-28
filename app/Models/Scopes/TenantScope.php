<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

/**
 * Class TenantScope
 *
 * A global query scope that restricts queries to only return records associated with the authenticated user's tenant.
 * If the user is a Super Admin (tenant_id is null), this scope is not applied.
 *
 * Usage:
 *   This scope is applied in models that belong to a tenant (e.g., User, SafetyData, Rejection, Performance, Delay).
 *
 * No artisan command is needed since this file already exists.
 */
class TenantScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param Builder $builder The query builder instance.
     * @param Model   $model   The Eloquent model.
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        // If the user is authenticated and has a tenant_id, filter results to only include that tenant's records.
        if (Auth::check() && Auth::user()->tenant_id !== null) {
            $builder->where('tenant_id', Auth::user()->tenant_id);
        }
    }
}
