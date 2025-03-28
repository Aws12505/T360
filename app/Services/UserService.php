<?php

namespace App\Services;

use App\Models\User;
use App\Models\Tenant;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

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
        $roles = \App\Models\Role::with('permissions')->get();
        $tenants = Auth::check() && is_null(Auth::user()->tenant_id) ? Tenant::all() : [];
        $permissions = Auth::user()->getAllPermissions();

        return [
            'users'       => $users,
            'roles'       => $roles,
            'tenants'     => $tenants,
            'permissions' => $permissions,
            'search'      => $search,
            'tenantSlug'  => $tenantSlug,
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

    /**
     * Create a new role.
     *
     * @param array $data
     * @return \Spatie\Permission\Models\Role
     */
    public function createRole(array $data)
    {
        $role = \App\Models\Role::create(['name' => $data['name']]);
        if (!empty($data['permissions'])) {
            $role->syncPermissions($data['permissions']);
        }
        return $role;
    }

    /**
     * Update an existing role.
     *
     * @param int $roleId
     * @param array $data
     * @return \Spatie\Permission\Models\Role
     */
    public function updateRole($roleId, array $data)
    {
        $role = \App\Models\Role::findOrFail($roleId);
        $role->update(['name' => $data['name']]);
        if (isset($data['permissions'])) {
            $role->syncPermissions($data['permissions']);
        }
        return $role;
    }

    /**
     * Update a role as Admin.
     *
     * @param int $roleId
     * @param array $data
     * @return \Spatie\Permission\Models\Role
     */
    public function updateRoleAdmin($roleId, array $data)
    {
        return $this->updateRole($roleId, $data);
    }

    /**
     * Delete a role.
     *
     * @param int $roleId
     * @return void
     */
    public function deleteRole($roleId)
    {
        $role = \App\Models\Role::findOrFail($roleId);
        $role->delete();
    }

    /**
     * Create a new tenant.
     *
     * @param array $data
     * @return \App\Models\Tenant
     */
    public function createTenant(array $data)
    {
        $name = trim($data['name']);
        $words = preg_split('/\s+/', $name);
        $slug = count($words) === 1 ? strtolower($words[0]) : strtolower(implode('', array_map(fn($w) => substr($w, 0, 1), $words)));
        $originalSlug = $slug;
        $count = 1;
        while (Tenant::where('slug', $slug)->exists()) {
            $slug = $originalSlug . $count;
            $count++;
        }
        $uniqueCompanyName = $name;
        $companyCount = 1;
        while (Tenant::where('name', $uniqueCompanyName)->exists()) {
            $uniqueCompanyName = $name . ' ' . $companyCount;
            $companyCount++;
        }
        return Tenant::create(['name' => $uniqueCompanyName, 'slug' => $slug]);
    }

    /**
     * Update an existing tenant.
     *
     * @param int $tenantId
     * @param array $data
     * @return \App\Models\Tenant
     */
    public function updateTenant($tenantId, array $data)
    {
        $tenant = Tenant::findOrFail($tenantId);
        $tenant->update($data);
        return $tenant;
    }

    /**
     * Delete a tenant.
     *
     * @param int $tenantId
     * @return void
     */
    public function deleteTenant($tenantId)
    {
        $tenant = Tenant::findOrFail($tenantId);
        $tenant->delete();
    }

    /**
     * Start impersonating a user.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function startImpersonation($id)
    {
        if (is_null(Auth::user()->tenant_id)) {
            session(['original_user' => Auth::id(), 'impersonate' => $id]);
            $impersonatedUser = User::findOrFail($id);
            Auth::login($impersonatedUser);
            return redirect()->route('dashboard', $impersonatedUser->tenant->slug);
        }
        abort(403, 'Unauthorized.');
    }

    /**
     * Stop impersonating and revert to the original user.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function stopImpersonation()
    {
        session()->forget('impersonate');
        $originalUser = User::withoutGlobalScopes()->find(session('original_user'));
        if ($originalUser) {
            Auth::login($originalUser);
            session()->forget('original_user');
            return redirect()->route('admin.users.roles.index');
        }
        abort(403, 'Unable to revert impersonation.');
    }
}
