<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contact;
use App\Models\DailyEntry;
use App\Models\LedgerTransaction;
use App\Models\Client;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        
        if ($user->isSuperAdmin()) {
            if ($request->has('client_id')) {
                session(['active_client_id' => $request->get('client_id')]);
            }
            $clientId = session('active_client_id');
        } else {
            $clientId = $user->client_id;
        }

        $clients = $user->isSuperAdmin() ? Client::where('is_active', true)->get() : collect();

        $stats = [
            'totalCustomers'       => 0,
            'totalSuppliers'       => 0,
            'totalCustomerDue'     => 0, // Total money dukandar will receive from customers
            'totalSupplierDue'     => 0, // Total money dukandar owes to suppliers
            'pendingDailyCount'    => 0, // Unpaid/Partial Daily entries count
            'pendingDailyAmount'   => 0, // Total remaining due in daily entries
            'todaysSalesAmount'    => 0, // Total sales made today
        ];

        $recentTransactions = collect();

        if ($clientId || !$user->isSuperAdmin()) {
            $stats['totalCustomers'] = Contact::where('client_id', $clientId)->where('type', 'REGULAR_CUSTOMER')->count();
            $stats['totalSuppliers'] = Contact::where('client_id', $clientId)->where('type', 'SUPPLIER')->count();
            
            // Calculate Customer Total Receivables (Opening Balances + SALE/CASH_GIVEN - CUSTOMER_PAYMENT)
            $customerContacts = Contact::where('client_id', $clientId)->where('type', 'REGULAR_CUSTOMER')->get();
            $ledgerService = app(\App\Services\LedgerService::class);
            
            $totalCustomerDue = 0;
            foreach ($customerContacts as $c) {
                $txs = $ledgerService->getTransactionsWithBalance($c);
                $bal = $txs->isNotEmpty() 
                    ? $txs->last()->running_balance 
                    : ($c->opening_balance_type === 'ADVANCE' ? -$c->opening_balance : $c->opening_balance);
                
                if ($bal > 0) {
                    $totalCustomerDue += $bal;
                }
            }
            $stats['totalCustomerDue'] = $totalCustomerDue;

            // Calculate Supplier Total Payables
            $supplierContacts = Contact::where('client_id', $clientId)->where('type', 'SUPPLIER')->get();
            $totalSupplierDue = 0;
            foreach ($supplierContacts as $s) {
                $txs = $ledgerService->getTransactionsWithBalance($s);
                $bal = $txs->isNotEmpty() 
                    ? $txs->last()->running_balance 
                    : ($s->opening_balance_type === 'ADVANCE' ? -$s->opening_balance : $s->opening_balance);
                
                if ($bal > 0) {
                    $totalSupplierDue += $bal;
                }
            }
            $stats['totalSupplierDue'] = $totalSupplierDue;

            // Daily Entries Analytics (Unpaid/Partial active bills)
            $pendingEntries = DailyEntry::where('client_id', $clientId)
                ->where('status', 'POSTED');

            $stats['pendingDailyCount']  = (clone $pendingEntries)->count();
            $stats['pendingDailyAmount'] = (clone $pendingEntries)->sum('remaining_amount');

            // Total overall money to collect (Customer Ledger Due + Daily Entries Due)
            $stats['totalReceivable'] = $stats['totalCustomerDue'] + $stats['pendingDailyAmount'];

            // Today's total counter sales
            $stats['todaysSalesAmount'] = DailyEntry::where('client_id', $clientId)
                ->whereDate('entry_date', today())
                ->sum('total_amount');

            $recentTransactions = LedgerTransaction::with('contact')
                ->where('client_id', $clientId)
                ->latest()
                ->take(8)
                ->get();
        }

        return view('dashboard.index', compact('stats', 'recentTransactions', 'clients', 'clientId'));
    }

    public function switchClient(Request $request)
    {
        if (auth()->user()->isSuperAdmin()) {
            session(['active_client_id' => $request->get('client_id')]);
        }
        return redirect()->back()->with('success', 'Active shop switched successfully.');
    }
}
