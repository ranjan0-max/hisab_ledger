<?php

namespace App\Services;

use App\Models\Contact;
use App\Models\LedgerTransaction;
use Illuminate\Support\Collection;

class LedgerService
{
    /**
     * Calculates transactions with running balance for a given contact.
     */
    public function getTransactionsWithBalance(Contact $contact): Collection
    {
        $transactions = LedgerTransaction::where('contact_id', $contact->id)
            ->where('status', 'POSTED')
            ->orderBy('transaction_date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $runningBalance = $contact->opening_balance_type === 'ADVANCE' 
            ? -$contact->opening_balance 
            : $contact->opening_balance;

        $processed = collect();

        foreach ($transactions as $tx) {
            $amount = (float) $tx->amount;

            if ($contact->type === 'REGULAR_CUSTOMER') {
                // Customer: SALE / CASH_GIVEN increases due (+), CUSTOMER_PAYMENT decreases due (-)
                if (in_array($tx->transaction_type, ['SALE', 'CASH_GIVEN', 'ADJUSTMENT'])) {
                    $runningBalance += $amount;
                } else if ($tx->transaction_type === 'CUSTOMER_PAYMENT') {
                    $runningBalance -= $amount;
                }
            } else {
                // Supplier: PURCHASE increases due to supplier (+), SUPPLIER_PAYMENT decreases (-)
                if (in_array($tx->transaction_type, ['PURCHASE', 'ADJUSTMENT'])) {
                    $runningBalance += $amount;
                } else if ($tx->transaction_type === 'SUPPLIER_PAYMENT') {
                    $runningBalance -= $amount;
                }
            }

            $tx->running_balance = $runningBalance;
            $processed->push($tx);
        }

        return $processed;
    }
}
