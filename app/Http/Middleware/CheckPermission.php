<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string $permissionKey): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Super Admin (client_id = null) ko sab allow
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Inactive user logout
        if (!$user->is_active) {
            auth()->logout();
            return redirect()->route('login')->withErrors(['username' => 'Your account has been deactivated.']);
        }

        // DB se permission check (Gates/Policies nahi)
        if (!$user->hasPermission($permissionKey)) {
            abort(403, 'You do not have permission to access this page.');
        }

        return $next($request);
    }
}
