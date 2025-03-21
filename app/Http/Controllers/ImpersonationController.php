<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class ImpersonationController extends Controller
{
    /**
     * Start impersonating a user.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function impersonate($id)
    {
        // Check if the current user is allowed to impersonate (e.g., is an admin)
        if (is_null(Auth::user()->tenant_id)) {
            // Optionally, store the original user's ID for later use
            session(['original_user' => Auth::id()]);
            // Store the impersonated user's ID in the session
            session(['impersonate' => $id]);
    
            // Retrieve the impersonated user to get the tenant slug
            $impersonatedUser = User::find(session('impersonate'));
                $tenantSlug = $impersonatedUser->tenant->slug;
                Auth::login(user: $impersonatedUser);
                // Redirect to dashboard with tenantSlug as a route parameter (or query parameter, if preferred)
                return redirect()->route('dashboard', $tenantSlug);
        }    
    }

    /**
     * Stop impersonating and revert to the original user.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function stopImpersonation()
    {
        // Remove the impersonation session variable
        session()->forget('impersonate');
        // Optionally, you can also clear the original user's ID from the session
        Auth::loginUsingId(session('original_user'));
        session()->forget('original_user');
        return redirect()->route('admin.dashboard');
    }
}
