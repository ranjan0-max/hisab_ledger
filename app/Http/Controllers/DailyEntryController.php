<?php

namespace App\Http\Controllers;

use App\Models\DailyEntry;
use Illuminate\Http\Request;

class DailyEntryController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = DailyEntry::with(['createdBy', 'payments'])->where('status', 'POSTED');

        if (!$user->isSuperAdmin()) {
            $query->where('client_id', $user->client_id);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                  ->orWhere('mobile_number', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Check if PDF export is requested
        if ($request->get('export') === 'pdf') {
            $exportEntries = $query->latest('entry_date')->latest('id')->get();
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.daily_list', compact('exportEntries'));
            return $pdf->download("Daily_Entries_Report_" . date('Y-m-d') . ".pdf");
        }

        $entries = $query->latest('entry_date')->latest('id')->paginate(10)->withQueryString();

        return view('daily.index', compact('entries'));
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
