<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientRenewalPayment;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClientRenewalController extends Controller
{
    private const TIMEZONE = 'Asia/Kolkata';

    public function blocked()
    {
        $user = auth()->user();
        $client = $user->client;
        $today = CarbonImmutable::today(self::TIMEZONE);

        $dueDate = $client?->next_renewal_date
            ? CarbonImmutable::parse($client->next_renewal_date->toDateString(), self::TIMEZONE)->startOfDay()
            : null;

        if ($user->isSuperAdmin() || !$dueDate || $today->lt($dueDate)) {
            return redirect()->route('dashboard');
        }

        return response()
            ->view('renewal.blocked', compact('client'))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }

    public function dismissWarning(Request $request)
    {
        $user = $request->user();
        $client = $user->client;
        $today = CarbonImmutable::today(self::TIMEZONE);

        if (!$user->isSuperAdmin() && $client?->next_renewal_date) {
            $dueDate = CarbonImmutable::parse(
                $client->next_renewal_date->toDateString(),
                self::TIMEZONE
            )->startOfDay();

            if ($today->gte($dueDate->subDays(15)) && $today->lt($dueDate)) {
                $request->session()->put('renewal_warning_dismissed_for', $dueDate->toDateString());
            }
        }

        return redirect()->back();
    }

    public function markPaid(Request $request, Client $client)
    {
        $validated = $request->validate([
            'renewal_due_date' => ['required', 'date_format:Y-m-d'],
        ]);

        $result = DB::transaction(function () use ($client, $validated) {
            $lockedClient = Client::query()->lockForUpdate()->findOrFail($client->id);
            $dueDate = $lockedClient->next_renewal_date?->toDateString();

            if (!$dueDate || $dueDate !== $validated['renewal_due_date']) {
                return null;
            }

            $dueDateValue = CarbonImmutable::parse($dueDate, self::TIMEZONE)->startOfDay();
            $today = CarbonImmutable::today(self::TIMEZONE);

            if ($dueDateValue->gt($today->addDays(15))) {
                return 'TOO_EARLY';
            }

            ClientRenewalPayment::create([
                'client_id' => $lockedClient->id,
                'renewal_due_date' => $dueDate,
                'paid_at' => now(self::TIMEZONE),
                'marked_by' => auth()->id(),
            ]);

            $nextRenewalDate = $dueDateValue->addYear();

            while ($nextRenewalDate->lte($today)) {
                $nextRenewalDate = $nextRenewalDate->addYear();
            }

            $lockedClient->update(['next_renewal_date' => $nextRenewalDate->toDateString()]);

            return $nextRenewalDate;
        });

        if (!$result) {
            return redirect()->back()->with('error', 'Renewal status changed already. Please refresh and try again.');
        }

        if ($result === 'TOO_EARLY') {
            return redirect()->back()->with('error', 'Renewal payment can be marked during the final 15 days or after the due date.');
        }

        return redirect()->back()->with(
            'success',
            'Renewal payment marked successfully. Next renewal: '.$result->format('d M Y').'.'
        );
    }
}
