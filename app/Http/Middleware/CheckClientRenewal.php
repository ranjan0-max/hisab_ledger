<?php

namespace App\Http\Middleware;

use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class CheckClientRenewal
{
    private const TIMEZONE = 'Asia/Kolkata';

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || $user->isSuperAdmin() || !$user->client?->next_renewal_date) {
            return $next($request);
        }

        $dueDate = CarbonImmutable::parse(
            $user->client->next_renewal_date->toDateString(),
            self::TIMEZONE
        )->startOfDay();
        $today = CarbonImmutable::today(self::TIMEZONE);

        if ($today->gte($dueDate)) {
            $request->session()->forget('renewal_warning_dismissed_for');

            if (!$request->routeIs('renewal.blocked', 'logout')) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Server renewal payment is due. Access is temporarily blocked.',
                    ], 423);
                }

                return redirect()->route('renewal.blocked');
            }

            return $next($request);
        }

        $dismissedFor = $request->session()->get('renewal_warning_dismissed_for');

        if ($today->gte($dueDate->subDays(15)) && $dismissedFor !== $dueDate->toDateString()) {
            View::share('renewalWarning', [
                'dueDate' => $dueDate,
                'daysRemaining' => (int) $today->diffInDays($dueDate),
            ]);
        }

        return $next($request);
    }
}
