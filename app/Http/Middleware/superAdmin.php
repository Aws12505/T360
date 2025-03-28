<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class superAdmin
 *
 * This middleware ensures that only Super Admin users (users with a null tenant_id)
 * can access routes guarded by this middleware.
 *
 * When the user is authenticated:
 *   - If tenant_id is null, the user is considered a Super Admin and is allowed.
 *   - Otherwise, the request is aborted with a 403 Unauthorized response.
 *
 * No artisan command is needed since this file already exists.
 */
class superAdmin
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
        $user = Auth::user();

        // If the user is a Super Admin (tenant_id is null), allow the request.
        if (is_null($user->tenant_id)) {
            return $next($request);
        } else {
            // Otherwise, abort the request with a 403 Unauthorized error.
            abort(403, 'Unauthorized: You are not a Super Admin.');
        }
    }
}
