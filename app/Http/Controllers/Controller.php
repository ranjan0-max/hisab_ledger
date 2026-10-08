<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\URL;

abstract class Controller
{
    /**
     * Days a share link stays valid.
     */
    protected const SHARE_LINK_DAYS = 7;

    /**
     * Client whose data a share link should show: the user's own client, or the
     * SuperAdmin's selected shop (null = all shops, same as the screen).
     */
    protected function shareClientId(): ?int
    {
        $user = auth()->user();

        if (!$user->isSuperAdmin()) {
            return (int) $user->client_id;
        }

        return session('active_client_id') ? (int) session('active_client_id') : null;
    }

    /**
     * Signed, expiring share link response for a public report page.
     */
    protected function shareLinkResponse(string $routeName, array $parameters)
    {
        $expiresAt = now()->addDays(self::SHARE_LINK_DAYS);

        $url = URL::temporarySignedRoute(
            $routeName,
            $expiresAt,
            array_filter($parameters, fn($value) => $value !== null && $value !== '')
        );

        return response()->json(['url' => $url, 'expires_on' => $expiresAt->format('d M Y')]);
    }
}
