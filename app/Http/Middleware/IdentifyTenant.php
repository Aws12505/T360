<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class IdentifyTenant
 *
 * This middleware verifies that the current authenticated user belongs to the correct tenant.
 * It performs the following:
 *   - If the user is not authenticated, it redirects to the login page.
 *   - If the user is a Super Admin (tenant_id is null), no further tenant checks are done.
 *   - If the user has a tenant, it checks if the tenantSlug in the route matches the user's tenant slug.
 *       - If it does not match, the request is aborted with a 403 error.
 *       - If no slug is present in the route, the user is redirected to the correct route with their tenantSlug.
 *
 * No artisan command is needed since this file already exists.
 */
class IdentifyTenant
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request  The current HTTP request instance.
     * @param  Closure  $next     The next middleware or request handler.
     * @return Response
     */
    public function handle(Request $request, Closure $next)
    {
        // Redirect to login if user is not authenticated.
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        
        $user = Auth::user();

        // For Super Admins (tenant_id is null), skip tenant slug checks.
        if (is_null($user->tenant_id)) {
            return $next($request);
        }

        // Retrieve the user's tenant slug (using optional() to avoid errors if tenant is null).
        $tenantSlug = optional($user->tenant)->slug;
        
        // Retrieve the tenantSlug parameter from the current route.
        $routeSlug = $request->route('tenantSlug');

        // If the route includes a slug that doesn't match the user's tenant slug, abort with a 403.
        if (!is_null($routeSlug) && $routeSlug !== $tenantSlug) {
            abort(403, 'Unauthorized: Slug does not match your tenant.');
        }

        // If no slug is provided in the route and the user has a tenant, redirect to the route with the correct slug.
        if (is_null($routeSlug) && !is_null($user->tenant_id)) {
            return redirect()->route(
                $request->route()->getName(),
                ['tenantSlug' => $tenantSlug]
            );
        }

        // All checks passed; proceed with the request.
        return $next($request);
    }
}
