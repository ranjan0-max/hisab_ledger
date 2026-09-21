<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminOnly
{
    /**
     * Allow only SuperAdmin users to access the route.
     * All other users receive a 403 Forbidden response.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || !$request->user()->isSuperAdmin()) {
            abort(403, 'Only SuperAdmin can perform this action.');
        }

        return $next($request);
    }
}
