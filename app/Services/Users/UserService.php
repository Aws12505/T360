<?php

namespace App\Services\Users;

use App\Models\User;
use App\Models\Tenant;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\Role;
/**
 * Class UserService
 *
 * Contains business logic for user, role, tenant, and impersonation management.
 *
 * Created manually: touch app/Services/UserService.php
 */
class UserService
{
    /**
     * Retrieve data required for user roles management view.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function getUserRolesData($request): array
    {
        $search = $request->input('search');
        $tenantSlug = Auth::user()->tenant ? Auth::user()->tenant->slug : null;
        $usersQuery = User::with(['roles', 'permissions']);

        if ($search) {
            $usersQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', '%'.$search.'%')
                      ->orWhere('email', 'like', '%'.$search.'%');
            });
        }
        $users = $usersQuery->paginate(10);
        $roles = Role::with('permissions')->paginate(10);
        $tenants = Auth::check() && is_null(Auth::user()->tenant_id) ? Tenant::paginate(10) : [];
        $permissions = Auth::user()->getAllPermissions();
        $isSuperAdmin = is_null(Auth::user()->tenant_id);
        return [
            'users'       => $users,
            'roles'       => $roles,
            'tenants'     => $tenants,
            'permissions' => $permissions,
            'search'      => $search,
            'tenantSlug'  => $tenantSlug,
            'SuperAdmin' => $isSuperAdmin,
        ];
    }

    /**
     * Create a new user.
     *
     * @param array $data
     * @return User
     */
    public function createUser(array $data)
    {
        if (Auth::check() && Auth::user()->tenant_id !== null) {
            $data['tenant_id'] = Auth::user()->tenant_id;
        }
        $data['password'] = Hash::make($data['password']);
        $user = User::create($data);
        if (!empty($data['roles'])) {
            $user->syncRoles($data['roles']);
        }
        if (!empty($data['user_permissions'])) {
            $user->syncPermissions($data['user_permissions']);
        }
        return $user;
    }

    /**
     * Update an existing user.
     *
     * @param int $userId
     * @param array $data
     * @return User
     */
    public function updateUser($userId, array $data)
    {
        $user = User::findOrFail($userId);
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }
        if (Auth::check() && Auth::user()->tenant_id !== null) {
            $data['tenant_id'] = Auth::user()->tenant_id;
        }
        $user->update($data);
        if (isset($data['roles'])) {
            $user->syncRoles($data['roles']);
        }
        if (isset($data['user_permissions'])) {
            $user->syncPermissions($data['user_permissions']);
        }
        return $user;
    }

    /**
     * Update a user as Admin.
     *
     * @param int $userId
     * @param array $data
     * @return User
     */
    public function updateUserAdmin($userId, array $data)
    {
        return $this->updateUser($userId, $data);
    }

    /**
     * Delete a user.
     *
     * @param int $userId
     * @return void
     */
    public function deleteUser($userId)
    {
        $user = User::findOrFail($userId);
        $user->delete();
    }

}
