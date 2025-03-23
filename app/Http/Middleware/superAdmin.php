<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class superAdmin
{
    /**
     * Handle an incoming request.
     *
     */
    public function handle(Request $request, Closure $next) 
    {
        $user = Auth::user();

        // If superadmin (tenant_id is null), skip slug checks:
        if (is_null($user->tenant_id)) {
            return $next($request);
        }
        else {
            abort(403, 'Unauthorized: You are not a Super Admin.');
        }
    }
}
