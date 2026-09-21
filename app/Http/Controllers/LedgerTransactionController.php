<?php

namespace App\Http\Controllers;

use App\Models\LedgerTransaction;
use App\Models\Contact;
use Illuminate\Http\Request;

class LedgerTransactionController extends Controller
{
    public function store(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'contact_id' => ['required', 'exists:contacts,id'],
            'transaction_type' => ['required', 'in:SALE,CASH_GIVEN,CUSTOMER_PAYMENT,PURCHASE,SUPPLIER_PAYMENT,ADJUSTMENT'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'transaction_date' => ['required', 'date'],
            'description' => ['nullable', 'string'],
            'payment_mode' => ['nullable', 'string', 'max:40'],
        ]);

        $contact = Contact::findOrFail($validated['contact_id']);

        if (!$user->isSuperAdmin() && $contact->client_id !== $user->client_id) {
            abort(403);
        }

        $paymentMode = in_array($validated['transaction_type'], ['CUSTOMER_PAYMENT', 'SUPPLIER_PAYMENT']) 
            ? ($validated['payment_mode'] ?? 'CASH') 
            : null;

        LedgerTransaction::create([
            'client_id' => $contact->client_id,
            'contact_id' => $contact->id,
            'transaction_type' => $validated['transaction_type'],
            'amount' => $validated['amount'],
            'transaction_date' => $validated['transaction_date'],
            'description' => $validated['description'] ?? 'No description',
            'payment_mode' => $paymentMode,
            'status' => 'POSTED',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        return redirect()->back()->with('success', 'Transaction recorded successfully.');
    }

    public function void(Request $request, LedgerTransaction $transaction)
    {
        $user = auth()->user();

        if (!$user->isSuperAdmin() && $transaction->client_id !== $user->client_id) {
            abort(403);
        }

        if ($transaction->status === 'VOID') {
            return redirect()->back()->with('error', 'Transaction is already voided.');
        }

        $transaction->update([
            'status' => 'VOID',
            'voided_at' => now(),
            'voided_by' => $user->id,
        ]);

        return redirect()->back()->with('success', 'Transaction voided successfully.');
    }
}
