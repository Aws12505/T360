<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display all users.
     */
    public function index()
    {
        $users = User::with('roles')->paginate(10);

        // Redirect to admin panel if the user has no tenant_id
        $route = Auth::user()->tenant_id === null ? 'admin.users.index' : 'users.index';

        return Inertia::render($route, [
            'users' => $users,
            'roles' => Role::all(['id', 'name']),
        ]);
    }

    /**
     * Show form to create user.
     */
    public function create()
    {
        return Inertia::render('Users/Create', [
            'roles' => Role::all(['id', 'name']),
        ]);
    }

    /**
     * Store a new user.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role_id'  => 'required|exists:roles,id',
        ]);

        $user = User::create([
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'password'  => Hash::make($validated['password']),
            'tenant_id' => Auth::user()->tenant_id, // Auto-assign based on logged-in user
        ]);

        $role = Role::findOrFail($validated['role_id']);
        $user->assignRole($role->name);

        $redirectRoute = Auth::user()->tenant_id === null ? 'admin.users.index' : 'users.index';

        return redirect()->route($redirectRoute)->with('success', 'User created successfully!');
    }

    /**
     * Show form to edit a user.
     */
    public function edit(User $user)
    {
        return Inertia::render(Auth::user()->tenant_id === null ? 'Admin/Users/Edit' : 'Users/Edit', [
            'user'  => $user->load('roles'),
            'roles' => Role::all(['id', 'name']),
        ]);
    }

    /**
     * Update the user.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|unique:users,email,' . $user->id,
            'role_id' => 'required|exists:roles,id',
        ]);

        $user->update([
            'name'  => $validated['name'],
            'email' => $validated['email'],
        ]);

        $role = Role::findOrFail($validated['role_id']);
        $user->syncRoles([$role->name]);

        $redirectRoute = Auth::user()->tenant_id === null ? 'admin.users.index' : 'users.index';

        return redirect()->route($redirectRoute)->with('success', 'User updated successfully!');
    }

    /**
     * Delete user.
     */
    public function destroy(User $user)
    {
        $user->delete();

        $redirectRoute = Auth::user()->tenant_id === null ? 'admin.users.index' : 'users.index';

        return redirect()->route($redirectRoute)->with('success', 'User deleted successfully!');
    }
}
