<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Traits\HasRoles;
use App\Models\Scopes\TenantScope;
use Illuminate\Support\Facades\Auth;

/**
 * Class User
 *
 * Represents an application user.
 *
 * Properties:
 * - id: The primary key.
 * - name: The user's name.
 * - email: The user's email.
 * - password: The hashed password.
 * - tenant_id: The ID of the tenant the user belongs to (nullable for SuperAdmin).
 *
 * Relationships:
 * - Belongs to a Tenant.
 *
 * Traits: HasFactory, Notifiable, HasRoles.
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'tenant_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
    ];

    /**
     * Boot the model and apply the TenantScope global scope if a user is authenticated.
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
     * Get the tenant that the user belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Determine if the user can impersonate another user.
     * Only SuperAdmin (tenant_id is null) can impersonate.
     *
     * @return bool
     */
    public function canImpersonate()
    {
        return is_null($this->tenant_id);
    }

    /**
     * Determine if the user can be impersonated.
     * Only users with a tenant can be impersonated.
     *
     * @return bool
     */
    public function canBeImpersonated()
    {
        return !is_null($this->tenant_id);
    }
}
