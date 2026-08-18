<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckSessionTimeout
{
    /**
     * Handle an incoming request.
     * Checks per-client session timeout and auto-logs out inactive users.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && !$request->is('login') && !$request->is('logout')) {
            $user = Auth::user();

            // Determine timeout in minutes based on client setting (default 120)
            $timeoutMinutes = 120;

            if ($user->client) {
                $timeoutMinutes = $user->client->session_timeout_minutes ?? 120;
            } elseif ($user->isSuperAdmin()) {
                // SuperAdmin: use a long session (8 hours default)
                $timeoutMinutes = 480;
            }

            $lastActivity = session('last_activity_at');

            if ($lastActivity) {
                $inactiveSeconds = time() - $lastActivity;
                $inactiveMinutes = $inactiveSeconds / 60;

                if ($inactiveMinutes >= $timeoutMinutes) {
                    // Session expired — logout user
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

                    return redirect()->route('login')
                        ->with('error', 'Aapka session expire ho gaya. Dobara login karein.');
                }
            }

            // Update last activity timestamp on every request
            session(['last_activity_at' => time()]);
        }

        return $next($request);
    }
}
