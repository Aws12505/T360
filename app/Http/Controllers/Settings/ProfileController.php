<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use App\Services\Tenants\TenantService;
class ProfileController extends Controller
{
    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request): Response
    {
        $isSuperAdmin = is_null(Auth::user()->tenant_id);
        $tenantSlug = $isSuperAdmin ? null : Auth::user()->tenant->slug;
        $permissions = Auth::user()->getAllPermissions();

        return Inertia::render('settings/Profile', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
            'tenantSlug' => $tenantSlug,
            'isSuperAdmin' => $isSuperAdmin,
            'permissions' => $permissions,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return to_route('profile.edit');
    }

    /**
     * Delete the user's profile.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();
        $tenantId = $user->tenant_id;
        
        // Check if this is the last user in the tenant
        $isLastUserInTenant = false;
        if ($tenantId && $user->tenant->users()->count() === 1) {
            $isLastUserInTenant = true;
        }

        Auth::logout();
        
        if ($isLastUserInTenant) {
            // This is the last user, so delete the tenant too
            app(TenantService::class)->deleteTenant($tenantId);
        } else {
            // Just delete the user
            $user->delete();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
