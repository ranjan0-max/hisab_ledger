<?php

namespace App\Http\Controllers;

use App\Models\DailyEntry;
use Illuminate\Http\Request;

class DailyEntryController extends Controller
{
    public function index(Request $request)
    {
        $query = $this->dailyEntriesQuery(
            DailyEntry::with(['createdBy', 'payments']),
            $request->get('payment_status'),
            $request->get('search')
        );

        $entries = $query->latest('entry_date')->latest('id')->paginate(10)->withQueryString();

        return view('daily.index', compact('entries'));
    }

    /**
     * Create a signed, 7-day share link to the public daily entries report with the current filters.
     */
    public function shareLink(Request $request)
    {
        return $this->shareLinkResponse('daily.shared', [
            'client' => $this->shareClientId(),
            'payment_status' => $request->get('payment_status'),
            'search' => $request->get('search'),
        ]);
    }

    /**
     * Public daily entries report opened from a share link (no login; the signed route guards it).
     */
    public function shared(Request $request)
    {
        // The signature fixes the client and filters, so skip the viewer's tenant scope
        $query = DailyEntry::withoutGlobalScopes()
            ->when($request->get('client'), fn($q, $clientId) => $q->where('daily_entries.client_id', $clientId));

        // 500 rows per page so the phone browser can preview/save the PDF quickly
        $entries = $this->dailyEntriesQuery($query, $request->get('payment_status'), $request->get('search'))
            ->latest('entry_date')
            ->latest('id')
            ->paginate(500)
            ->withQueryString();

        $client = $request->get('client') ? \App\Models\Client::find($request->get('client')) : null;

        return view('reports.daily_shared', compact('entries', 'client'));
    }

    /**
     * Posted daily entries with the screen's filters; used by the list screen and the shared report.
     */
    private function dailyEntriesQuery($query, ?string $paymentStatus, ?string $search)
    {
        $query->where('daily_entries.status', 'POSTED');

        if (trim((string) $paymentStatus) !== '') {
            $query->where('payment_status', $paymentStatus);
        }

        if (trim((string) $search) !== '') {
            $query->where(function($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                  ->orWhere('mobile_number', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        return $query;
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:150'],
            'mobile_number' => ['nullable', 'string', 'max:20'],
            'description'   => ['nullable', 'string'],
            'total_amount'  => ['required', 'numeric', 'min:0.01'],
            'entry_date'    => ['required', 'date'],
        ]);

        $clientId = $user->isSuperAdmin() ? $request->get('client_id') : $user->client_id;
        $totalAmount = (float) $validated['total_amount'];
        $paidAmount = 0.00;
        $remainingAmount = $totalAmount;
        
        $paymentStatus = DailyEntry::calcPaymentStatus($totalAmount, $paidAmount);

        DailyEntry::create([
            'client_id'        => $clientId,
            'customer_name'    => $validated['customer_name'],
            'mobile_number'    => $validated['mobile_number'] ?? null,
            'description'      => $validated['description'] ?? 'No description',
            'total_amount'     => $totalAmount,
            'paid_amount'      => $paidAmount,
            'remaining_amount' => $remainingAmount,
            'payment_mode'     => 'CASH',
            'entry_date'       => $validated['entry_date'],
            'payment_status'   => $paymentStatus,
            'status'           => 'POSTED',
            'created_by'       => $user->id,
            'updated_by'       => $user->id,
        ]);

        return redirect()->route('daily.index')->with('success', 'Daily entry recorded successfully.');
    }

    public function storePayment(Request $request, DailyEntry $dailyEntry)
    {
        $user = auth()->user();

        if (!$user->isSuperAdmin() && $dailyEntry->client_id !== $user->client_id) {
            abort(403);
        }

        $validated = $request->validate([
            'amount'       => ['required', 'numeric', 'min:0.01'],
            'payment_date' => ['required', 'date'],
            'payment_mode' => ['required', 'string', 'max:40'],
            'notes'        => ['nullable', 'string', 'max:255'],
        ]);

        $newPaymentAmount = (float) $validated['amount'];
        $newTotalPaid = $dailyEntry->paid_amount + $newPaymentAmount;
        $newRemaining = max(0, $dailyEntry->total_amount - $newTotalPaid);
        $newPaymentStatus = DailyEntry::calcPaymentStatus($dailyEntry->total_amount, $newTotalPaid);
        $newStatus = ($newRemaining <= 0) ? 'VOID' : 'POSTED';

        // 1. Save payment entry
        \App\Models\DailyEntryPayment::create([
            'daily_entry_id' => $dailyEntry->id,
            'amount'         => $newPaymentAmount,
            'payment_date'   => $validated['payment_date'],
            'payment_mode'   => $validated['payment_mode'],
            'notes'          => $validated['notes'] ?? 'Installment payment',
            'created_by'     => $user->id,
        ]);

        // 2. Update daily entry balance
        $dailyEntry->update([
            'paid_amount'      => $newTotalPaid,
            'remaining_amount' => $newRemaining,
            'payment_status'   => $newPaymentStatus,
            'status'           => $newStatus,
            'updated_by'       => $user->id,
        ]);

        $msg = ($newRemaining <= 0) 
            ? 'Payment recorded! Entry is now fully paid and auto-marked as VOID.' 
            : 'Payment of ₹' . number_format($newPaymentAmount, 2) . ' recorded successfully.';

        return redirect()->route('daily.index')->with('success', $msg);
    }

    public function update(Request $request, DailyEntry $dailyEntry)
    {
        $user = auth()->user();

        if (!$user->isSuperAdmin() && $dailyEntry->client_id !== $user->client_id) {
            abort(403);
        }

        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:150'],
            'mobile_number' => ['nullable', 'string', 'max:20'],
            'description'   => ['nullable', 'string'],
            'total_amount'  => ['required', 'numeric', 'min:0.01'],
            'entry_date'    => ['required', 'date'],
        ]);

        $totalAmount = (float) $validated['total_amount'];
        $paidAmount = (float) $dailyEntry->paid_amount;
        $remainingAmount = max(0, $totalAmount - $paidAmount);
        
        $paymentStatus = DailyEntry::calcPaymentStatus($totalAmount, $paidAmount);
        $entryStatus = ($remainingAmount <= 0) ? 'VOID' : 'POSTED';

        $dailyEntry->update([
            'customer_name'    => $validated['customer_name'],
            'mobile_number'    => $validated['mobile_number'] ?? null,
            'description'      => $validated['description'] ?? 'No description',
            'total_amount'     => $totalAmount,
            'remaining_amount' => $remainingAmount,
            'entry_date'       => $validated['entry_date'],
            'payment_status'   => $paymentStatus,
            'status'           => $entryStatus,
            'updated_by'       => $user->id,
        ]);

        $msg = ($remainingAmount <= 0) ? 'Daily entry updated and auto-marked as VOID (Fully Paid).' : 'Daily entry updated successfully.';
        return redirect()->route('daily.index')->with('success', $msg);
    }

    public function void(DailyEntry $dailyEntry)
    {
        $user = auth()->user();

        if (!$user->isSuperAdmin() && $dailyEntry->client_id !== $user->client_id) {
            abort(403);
        }

        $dailyEntry->update([
            'status'     => 'VOID',
            'updated_by' => $user->id,
        ]);

        return redirect()->route('daily.index')->with('success', 'Daily entry voided successfully.');
    }
}
