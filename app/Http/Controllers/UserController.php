<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use App\Models\User;
use App\Models\Role;       // Your extended Spatie Role model
use App\Models\Tenant;     // Assuming you have a Tenant model
use Spatie\Permission\Models\Permission;

class UserController extends Controller
{
    /**
     * Display the page with users and roles.
     */
    public function index(Request $request)
{
    // Get the search query (if any) from the request.
    $search = $request->input('search');
    if (is_null(Auth::user()->tenant_id)) {
        $tenantSlug = null;
    } else {
        $tenantSlug = Auth::user()->tenant->slug;
    }
    // Build the query for users. Global scope on the User model will handle filtering by tenant.
    $usersQuery = User::with(['roles', 'permissions']);
    if ($search) {
        $usersQuery->where(function ($query) use ($search) {
            $query->where('name', 'like', '%'.$search.'%')
                  ->orWhere('email', 'like', '%'.$search.'%');
        });
    }
    // Paginate users (10 per page)
    $users = $usersQuery->paginate(10);

    // Retrieve roles with their associated permissions.
    $roles = Role::with('permissions')->get();

    // For SuperAdmin (tenant_id === null), get all tenants.
    $tenants = [];
    if (Auth::check() && Auth::user()->tenant_id === null) {
        $tenants = Tenant::all();
    }

    // Get the permissions available to the current user.
    $permissions = Auth::user()->getAllPermissions();

    // Return the Inertia view with the data.
    return Inertia::render('UserRolesManagement', [
        'users'       => $users,
        'roles'       => $roles,
        'tenants'     => $tenants,
        'permissions' => $permissions,
        'search'      => $search,
        'tenantSlug' => $tenantSlug,
    ]);
}

    /**
     * Store a new user.
     */
    public function storeUser(Request $request)
{
    // Validate input data.
    $validated = $request->validate([
        'name'              => 'required|string|max:255',
        'email'             => 'required|email|unique:users,email',
        'password'          => 'required|string|min:8',
        'tenant_id'         => 'nullable|exists:tenants,id',
        'roles'             => 'nullable|array',
        'user_permissions'  => 'nullable|array',
    ]);

    // For non‑SuperAdmin users, force the tenant_id to their own tenant.
    if (Auth::check() && Auth::user()->tenant_id !== null) {
        $validated['tenant_id'] = Auth::user()->tenant_id;
    }
    
    // Hash the password.
    $validated['password'] = Hash::make($validated['password']);

    // Create the user.
    $user = User::create($validated);

    // Assign roles if provided.
    if (!empty($validated['roles'])) {
        $user->syncRoles($validated['roles']);
    }

    // Assign permissions if provided.
    if (!empty($validated['user_permissions'])) {
        $user->syncPermissions($validated['user_permissions']);
    }

    // Redirect back with a success message.
    return back()->with('success', 'User created successfully.');
}

    /**
     * Update an existing user.
     */
 public function updateUser(Request $request, $tenantSlug ,User $user)
{
    // Define base validation rules, including tenant_id as nullable.
    $rules = [
        'name'              => 'required|string|max:255',
        'email'             => 'required|email|unique:users,email,'.$user->id,
        'roles'             => 'nullable|array',
        'user_permissions'  => 'nullable|array',
        'tenant_id'         => 'nullable|exists:tenants,id',
    ];

    // Validate password only if provided.
    if ($request->filled('password')) {
        $rules['password'] = 'nullable|string|min:8';
    }

    $validated = $request->validate($rules);

    // Hash the password if provided.
    if (!empty($validated['password'])) {
        $validated['password'] = Hash::make($validated['password']);
    } else {
        unset($validated['password']);
    }

    // If the authenticated user is not a SuperAdmin, force tenant_id to match their tenant.
    if (Auth::check() && Auth::user()->tenant_id !== null) {
        $validated['tenant_id'] = Auth::user()->tenant_id;
    }

    // Update the user.
    $user->update($validated);

    // Sync roles if provided.
    if ($request->has('roles')) {
        $user->syncRoles($request->input('roles'));
    }

    // Sync permissions if provided.
    if ($request->has('user_permissions')) {
        $user->syncPermissions($request->input('user_permissions'));
    }

    return back()->with('success', 'User updated successfully.');
}

public function updateUserAdmin(Request $request ,User $user)
{
    // Define base validation rules, including tenant_id as nullable.
    $rules = [
        'name'              => 'required|string|max:255',
        'email'             => 'required|email|unique:users,email,'.$user->id,
        'roles'             => 'nullable|array',
        'user_permissions'  => 'nullable|array',
        'tenant_id'         => 'nullable|exists:tenants,id',
    ];

    // Validate password only if provided.
    if ($request->filled('password')) {
        $rules['password'] = 'nullable|string|min:8';
    }

    $validated = $request->validate($rules);

    // Hash the password if provided.
    if (!empty($validated['password'])) {
        $validated['password'] = Hash::make($validated['password']);
    } else {
        unset($validated['password']);
    }

    // If the authenticated user is not a SuperAdmin, force tenant_id to match their tenant.
    if (Auth::check() && Auth::user()->tenant_id !== null) {
        $validated['tenant_id'] = Auth::user()->tenant_id;
    }

    // Update the user.
    $user->update($validated);

    // Sync roles if provided.
    if ($request->has('roles')) {
        $user->syncRoles($request->input('roles'));
    }

    // Sync permissions if provided.
    if ($request->has('user_permissions')) {
        $user->syncPermissions($request->input('user_permissions'));
    }

    return back()->with('success', 'User updated successfully.');
}
    /**
     * Delete a user.
     */
    public function destroyUser($tenantSlug ,User $user)
    {
        // Delete the user
        $user->delete();

        return back()->with('success', 'User deleted successfully.');
    }
    public function destroyUserAdmin(User $user)
    {
        // Delete the user
        $user->delete();

        return back()->with('success', 'User deleted successfully.');
    }

    /**
     * Store a new role.
     */
    public function storeRole(Request $request)
    {
        // Validate the input. 'name' is required and permissions should be an array.
        $validated = $request->validate([
            'name'        => 'required|string|max:255|unique:roles,name',
            'permissions' => 'nullable|array',
        ]);

        // Create the role.
        $role = Role::create(['name' => $validated['name']]);

        // Assign permissions if provided.
        if (!empty($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return back()->with('success', 'Role created successfully.');
    }

    /**
     * Update an existing role.
     */
    public function updateRole(Request $request, $tenantSlug , Role $role)
    {
        // Validate input.
        $validated = $request->validate([
            'name'        => 'required|string|max:255|unique:roles,name,'.$role->id,
            'permissions' => 'nullable|array',
        ]);

        // Update role name.
        $role->update(['name' => $validated['name']]);

        // Sync permissions.
        if (isset($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return back()->with('success', 'Role updated successfully.');
    }

    /**
     * Delete a role.
     */
    public function destroyRole($tenantSlug ,Role $role)
    {
        $role->delete();

        return back()->with('success', 'Role deleted successfully.');
    }
    public function updateRoleAdmin(Request $request, Role $role)
    {
        // Validate input.
        $validated = $request->validate([
            'name'        => 'required|string|max:255|unique:roles,name,'.$role->id,
            'permissions' => 'nullable|array',
        ]);

        // Update role name.
        $role->update(['name' => $validated['name']]);

        // Sync permissions.
        if (isset($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return back()->with('success', 'Role updated successfully.');
    }

    /**
     * Delete a role.
     */
    public function destroyRoleAdmin(Role $role)
    {
        $role->delete();

        return back()->with('success', 'Role deleted successfully.');
    }

    public function storeTenant(Request $request)
{
    // Validate the input. Both 'name' and 'slug' are required.
    $validated = $request->validate([
        'name' => 'required|string|max:255|unique:tenants,name',
        'slug' => 'required|string|max:255|unique:tenants,slug',
    ]);

    // Create the tenant with the validated data.
    $tenant = Tenant::create([
        'name' => $validated['name'],
        'slug' => $validated['slug'],
    ]);

    return back()->with('success', 'Tenant created successfully.');
}

/**
 * Update an existing tenant.
 */
public function updateTenant(Request $request, Tenant $tenant)
{
    // Validate the input.
    $validated = $request->validate([
        'name' => 'required|string|max:255|unique:tenants,name,' . $tenant->id,
        'slug' => 'required|string|max:255|unique:tenants,slug,' . $tenant->id,
    ]);

    // Update tenant's fields.
    $tenant->update($validated);

    return back()->with('success', 'Tenant updated successfully.');
}

/**
 * Delete a tenant.
 */
public function destroyTenant( Tenant $tenant)
{
    $tenant->delete();

    return back()->with('success', 'Tenant deleted successfully.');
}

}
