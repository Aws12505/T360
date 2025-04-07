<?php

namespace App\Http\Controllers\Web\UserManagement;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Requests\UserManagement\StoreUserRequest;
use App\Http\Requests\UserManagement\UpdateUserRequest;
use App\Http\Requests\UserManagement\StoreRoleRequest;
use App\Http\Requests\UserManagement\UpdateRoleRequest;
use App\Http\Requests\UserManagement\StoreTenantRequest;
use App\Http\Requests\UserManagement\UpdateTenantRequest;
use App\Services\Roles\RoleService;
use App\Services\Tenants\TenantService;
use App\Services\Users\UserService;

/**
 * Class UserController
 *
 * This controller handles all user, role, and tenant management operations.
 * It delegates business logic to the UserService and uses Form Request classes for validation.
 *
 * Commands used:
 *   php artisan make:controller Web/UserController
 */
class UserController extends Controller
{
    protected UserService $userService;
    protected TenantService $tenantService;
    protected RoleService $roleService;
    /**
     * Constructor.
     *
     * @param UserService $userService Injected service for user management.
     * @param TenantService $tenantService
     * @param RoleService $roleService
     */
    public function __construct(UserService $userService, TenantService $tenantService, RoleService $roleService)
    {
        $this->userService = $userService;
        $this->tenantService = $tenantService;
        $this->roleService = $roleService;
    }

    /**
     * Display the list of users, roles, tenants, and permissions.
     *
     * @param Request $request
     * @return \Inertia\Response
     */
    public function index(Request $request)
    {
        $data = $this->userService->getUserRolesData($request);
        return Inertia::render('UserRolesManagement', $data);
    }

    /**
     * Create a new user.
     *
     * @param StoreUserRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeUser(StoreUserRequest $request)
    {
        
        $this->userService->createUser($request->validated());
        return back()->with('success', 'User created successfully.');
    }

    /**
     * Update an existing user.
     *
     * @param UpdateUserRequest $request
     * @param string $tenantSlug
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateUser(UpdateUserRequest $request, $tenantSlug, $id)
    {
        
        $this->userService->updateUser($id, $request->validated());
        return back()->with('success', 'User updated successfully.');
    }

    /**
     * Delete a user.
     *
     * @param string $tenantSlug
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyUser($tenantSlug, $id)
    {
        $this->userService->deleteUser($id);
        return back()->with('success', 'User deleted successfully.');
    }

    // ADMIN-SPECIFIC METHODS

    /**
     * Update a user as Admin.
     *
     * @param UpdateUserRequest $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateUserAdmin(UpdateUserRequest $request, $id)
    {
        
        $this->userService->updateUserAdmin($id, $request->validated());
        return back()->with('success', 'User updated successfully.');
    }

    /**
     * Delete a user as Admin.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyUserAdmin($id)
    {
        $this->userService->deleteUser($id);
        return back()->with('success', 'User deleted successfully.');
    }

    /**
     * Create a new role.
     *
     * @param StoreRoleRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeRole(StoreRoleRequest $request)
    {
        
        $this->roleService->createRole($request->validated());
        return back()->with('success', 'Role created successfully.');
    }

    /**
     * Update an existing role.
     *
     * @param UpdateRoleRequest $request
     * @param string $tenantSlug
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateRole(UpdateRoleRequest $request, $tenantSlug, $id)
    {
        
        $this->roleService->updateRole($id, $request->validated());
        return back()->with('success', 'Role updated successfully.');
    }

    /**
     * Delete a role.
     *
     * @param string $tenantSlug
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyRole($tenantSlug, $id)
    {
        $this->roleService->deleteRole($id);
        return back()->with('success', 'Role deleted successfully.');
    }

    /**
     * Update a role as Admin.
     *
     * @param UpdateRoleRequest $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateRoleAdmin(UpdateRoleRequest $request, $id)
    {
        
        $this->roleService->updateRoleAdmin($id, $request->validated());
        return back()->with('success', 'Role updated successfully.');
    }

    /**
     * Delete a role as Admin.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyRoleAdmin($id)
    {
        $this->roleService->deleteRole($id);
        return back()->with('success', 'Role deleted successfully.');
    }

    /**
     * Create a new tenant.
     *
     * @param StoreTenantRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeTenant(StoreTenantRequest $request)
    {
        $this->tenantService->createTenant($request->validated());
        return back()->with('success', 'Tenant created successfully.');
    }

    /**
     * Update an existing tenant.
     *
     * @param UpdateTenantRequest $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateTenant(UpdateTenantRequest $request, $id)
    {
        $validatedData = $request->validated();
        
        // Handle file upload separately since it's not part of the validated array
        if ($request->hasFile('image')) {
            $validatedData['image'] = $request->file('image');
        }
        
        $this->tenantService->updateTenant($id, $validatedData);
        return back()->with('success', 'Tenant updated successfully.');
    }

    /**
     * Delete a tenant.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyTenant($id)
    {
        $this->tenantService->deleteTenant($id);
        return back()->with('success', 'Tenant deleted successfully.');
    }
}
