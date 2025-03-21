<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Tenant;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IdentifyTenant
{
    public function handle(Request $request, Closure $next)
    {
        // If user not logged in, just continue (or redirect to login, up to you).
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        
        $user = Auth::user();

        // If superadmin (tenant_id is null), skip slug checks:
        if (is_null($user->tenant_id)) {
            // No restrictions
            return $next($request);
        }

        // Otherwise, user has a tenant -> get the tenant's slug
        // Adjust this to however you access the tenant slug, e.g. $user->tenant->slug
        $tenantSlug = optional($user->tenant)->slug;
        
        // The slug in the current route (if any)
        $routeSlug = $request->route('tenantSlug');

        // If the route includes a slug that's not user's slug -> Unauthorized
        if (!is_null($routeSlug) && $routeSlug !== $tenantSlug) {
            abort(403, 'Unauthorized: Slug does not match your tenant.');
        }

        // If there's no slug in the route, redirect to the correct slug
        if (is_null($routeSlug) && !is_null($user->tenant_id)) {
            // If your route is named, you can redirect via route name
            // e.g., for a named route "dashboard" or "your-route-name"
            return redirect()->route(
                $request->route()->getName(),
                ['tenantSlug' => $tenantSlug]
            );
        }

        // If everything looks good, continue
        return $next($request);
    }
}

