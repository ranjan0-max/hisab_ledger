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
            $stats['totalCustomers'] = Contact::where('contacts.type', 'REGULAR_CUSTOMER')->count();
            $stats['totalSuppliers'] = Contact::where('contacts.type', 'SUPPLIER')->count();
            
            // Calculate Customer Total Receivables using fast SQL aggregation
            $customerBalances = Contact::where('contacts.type', 'REGULAR_CUSTOMER')
                ->withCurrentBalance()
                ->get();
            $stats['totalCustomerDue'] = (float) $customerBalances->where('current_balance', '>', 0)->sum('current_balance');

            // Calculate Supplier Total Payables using fast SQL aggregation
            $supplierBalances = Contact::where('contacts.type', 'SUPPLIER')
                ->withCurrentBalance()
                ->get();
            $stats['totalSupplierDue'] = (float) $supplierBalances->where('current_balance', '>', 0)->sum('current_balance');

            // Daily Entries Analytics (Unpaid/Partial active bills)
            $pendingEntries = DailyEntry::where('daily_entries.status', 'POSTED');

            $stats['pendingDailyCount']  = (clone $pendingEntries)->count();
            $stats['pendingDailyAmount'] = (clone $pendingEntries)->sum('remaining_amount');

            // Total overall money to collect (Customer Ledger Due + Daily Entries Due)
            $stats['totalReceivable'] = $stats['totalCustomerDue'] + $stats['pendingDailyAmount'];

            // Today's total counter sales
            $stats['todaysSalesAmount'] = DailyEntry::whereDate('entry_date', today())->sum('total_amount');

            $recentTransactions = LedgerTransaction::with('contact')
                ->where('ledger_transactions.status', 'POSTED')
                ->latest()
                ->take(8)
                ->get();
        }

        return view('dashboard.index', compact('stats', 'recentTransactions', 'clients', 'clientId'));
    }

    public function switchClient(Request $request)
    {
        session(['active_client_id' => $request->get('client_id')]);
        return redirect()->back()->with('success', 'Active shop switched successfully.');
    }
}
