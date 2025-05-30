<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Models\Driver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Show the login page.
     */
    public function create(Request $request)
    {
        if (Auth::guard('driver')->check()) {
            return redirect()->route('driver.dashboard');
        }

        if (Auth::guard('web')->check()) {
            $user = Auth::guard('web')->user();

            if (is_null($user->tenant)) {
                return redirect()->route('admin.dashboard');
            } else {
                return redirect()->route('dashboard', ['tenantSlug' => $user->tenant->slug]);
            }
        }

        return Inertia::render('auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $email = $request->email;
        $password = $request->password;
        $credentials = $request->only('email', 'password');

        // Auto-detect guard
        $guard = 'web'; // default

        // First, check if it's a User
        $user = User::where('email', $email)->first();

        if ($user) {
            $guard = 'web';
        } else {
            // If not found in users, check drivers
            $driver = Driver::where('email', $email)->first();

            if ($driver) {
                $guard = 'driver';
            } else {
                // If not found in either → fail fast
                return back()->withErrors([
                    'email' => __('auth.failed'),
                ]);
            }
        }
        // Now attempt login with detected guard
        if (Auth::guard($guard)->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            if ($guard === 'driver') {
                return redirect()->route('driver.dashboard');
            }
            if ($guard === 'web') {
                $user = Auth::guard('web')->user();

                if (is_null($user->tenant)) {
                    return redirect()->route('admin.dashboard');
                }

                $tenantSlug = $user->tenant->slug;
                return redirect()->route('dashboard', ['tenantSlug' => $tenantSlug]);
            }
        }

        // If login failed
        return back()->withErrors([
            'email' => __('auth.failed'),
        ]);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        if (Auth::guard('driver')->check()) {
            Auth::guard('driver')->logout();
        }

        if (Auth::guard('web')->check()) {
            Auth::guard('web')->logout();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
