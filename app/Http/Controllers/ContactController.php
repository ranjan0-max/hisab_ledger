<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\ContactPhoneNumber;
use App\Services\LedgerService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ContactController extends Controller
{
    protected LedgerService $ledgerService;

    public function __construct(LedgerService $ledgerService)
    {
        $this->ledgerService = $ledgerService;
    }

    public function customers(Request $request)
    {
        return $this->index($request, 'REGULAR_CUSTOMER');
    }

    public function suppliers(Request $request)
    {
        return $this->index($request, 'SUPPLIER');
    }

    private function index(Request $request, string $type)
    {
        $user = auth()->user();
        $query = Contact::with('phoneNumbers')->where('type', $type);

        if (!$user->isSuperAdmin()) {
            $query->where('client_id', $user->client_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('khata_number', 'like', "%{$search}%")
                  ->orWhereHas('phoneNumbers', function($pq) use ($search) {
                      $pq->where('phone_number', 'like', "%{$search}%");
                  });
            });
        }

        $inactiveMonths = $request->get('inactive_months');

        // Check if PDF Export is requested
        if ($request->get('export') === 'pdf') {
            $exportContacts = $query->latest()->get();
            $exportContacts = $exportContacts->filter(function($contact) use ($inactiveMonths) {
                $transactions = $this->ledgerService->getTransactionsWithBalance($contact);
                $contact->current_balance = $transactions->isNotEmpty() 
                    ? $transactions->last()->running_balance 
                    : ($contact->opening_balance_type === 'ADVANCE' ? -$contact->opening_balance : $contact->opening_balance);

                // Filter condition: Must have DUE balance (> 0)
                if ($contact->current_balance <= 0) {
                    return false;
                }

                // Inactive Months condition: No transaction in the last X months
                if ($inactiveMonths) {
                    $cutoffDate = \Carbon\Carbon::now()->subMonths((int)$inactiveMonths);
                    $hasRecentTx = $contact->transactions()
                        ->where('status', 'POSTED')
                        ->where('transaction_date', '>=', $cutoffDate->format('Y-m-d'))
                        ->exists();

                    if ($hasRecentTx) {
                        return false;
                    }
                }

                return true;
            });

            $pdf = Pdf::loadView('reports.customers_list', compact('exportContacts', 'type', 'inactiveMonths'));
            return $pdf->download("Inactive_Due_Customers_" . date('Y-m-d') . ".pdf");
        }

        $allContacts = $query->latest()->get();
        $filteredContacts = $allContacts->filter(function($contact) use ($inactiveMonths) {
            $transactions = $this->ledgerService->getTransactionsWithBalance($contact);
            $contact->current_balance = $transactions->isNotEmpty() 
                ? $transactions->last()->running_balance 
                : ($contact->opening_balance_type === 'ADVANCE' ? -$contact->opening_balance : $contact->opening_balance);

            if ($inactiveMonths) {
                // Must have DUE balance (> 0)
                if ($contact->current_balance <= 0) {
                    return false;
                }

                $cutoffDate = \Carbon\Carbon::now()->subMonths((int)$inactiveMonths);
                $hasRecentTx = $contact->transactions()
                    ->where('status', 'POSTED')
                    ->where('transaction_date', '>=', $cutoffDate->format('Y-m-d'))
                    ->exists();

                if ($hasRecentTx) {
                    return false;
                }
            }

            return true;
        });

        $page = \Illuminate\Pagination\Paginator::resolveCurrentPage() ?: 1;
        $perPage = 10;
        $contacts = new \Illuminate\Pagination\LengthAwarePaginator(
            $filteredContacts->forPage($page, $perPage)->values(),
            $filteredContacts->count(),
            $perPage,
            $page,
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(), 'query' => $request->query()]
        );

        // Calculate current running balance for each contact
        foreach ($contacts as $contact) {
            $transactions = $this->ledgerService->getTransactionsWithBalance($contact);
            $contact->current_balance = $transactions->isNotEmpty() 
                ? $transactions->last()->running_balance 
                : ($contact->opening_balance_type === 'ADVANCE' ? -$contact->opening_balance : $contact->opening_balance);
        }

        $view = $type === 'REGULAR_CUSTOMER' ? 'customers.index' : 'suppliers.index';

        return view($view, compact('contacts'));
    }

    public function show(Contact $contact)
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() && $contact->client_id !== $user->client_id) {
            abort(403);
        }

        $contact->load('phoneNumbers');
        $transactions = $this->ledgerService->getTransactionsWithBalance($contact);
        
        $view = $contact->type === 'REGULAR_CUSTOMER' ? 'customers.show' : 'suppliers.show';

        return view($view, compact('contact', 'transactions'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'type' => ['required', 'in:REGULAR_CUSTOMER,SUPPLIER'],
            'name' => ['required', 'string', 'max:150'],
            'khata_number' => ['required', 'string', 'max:50'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
            'gst_number' => ['nullable', 'string', 'max:30'],
            'notes' => ['nullable', 'string'],
            'opening_balance' => ['nullable', 'numeric', 'min:0'],
            'opening_balance_type' => ['nullable', 'in:DUE,ADVANCE'],
            'client_id' => [$user->isSuperAdmin() ? 'required' : 'nullable', 'exists:clients,id'],
        ]);

        $clientId = $user->isSuperAdmin() ? $validated['client_id'] : $user->client_id;

        // Custom Validation Rule from Next.js: Active Customer Khata Number uniqueness check
        if ($validated['type'] === 'REGULAR_CUSTOMER') {
            $existingCustomer = Contact::where('client_id', $clientId)
                ->where('type', 'REGULAR_CUSTOMER')
                ->where('is_active', true)
                ->where('khata_number', trim($validated['khata_number']))
                ->first();

            if ($existingCustomer) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', "Khata Number {$validated['khata_number']} is already assigned to active customer \"{$existingCustomer->name}\". Deactivate that customer or use a different Khata Number.");
            }
        }

        $contact = Contact::create([
            'client_id' => $clientId,
            'type' => $validated['type'],
            'name' => $validated['name'],
            'khata_number' => $validated['khata_number'],
            'address' => $validated['address'] ?? null,
            'gst_number' => $validated['gst_number'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'opening_balance' => $validated['opening_balance'] ?? 0,
            'opening_balance_type' => $validated['opening_balance_type'] ?? 'DUE',
            'is_active' => true,
        ]);

        if (!empty($validated['phone_number'])) {
            ContactPhoneNumber::create([
                'contact_id' => $contact->id,
                'client_id' => $clientId,
                'contact_type' => $validated['type'],
                'phone_number' => $validated['phone_number'],
                'is_primary' => true,
            ]);
        }

        $redirectRoute = $validated['type'] === 'REGULAR_CUSTOMER' ? 'customers.index' : 'suppliers.index';
        return redirect()->route($redirectRoute)->with('success', 'Party created successfully.');
    }

    public function update(Request $request, Contact $contact)
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() && $contact->client_id !== $user->client_id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'khata_number' => ['required', 'string', 'max:50'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
            'gst_number' => ['nullable', 'string', 'max:30'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        $isActive = $request->has('is_active');

        // Custom Validation Rule from Next.js: Active Customer Khata Number uniqueness check
        if ($contact->type === 'REGULAR_CUSTOMER' && $isActive) {
            $existingCustomer = Contact::where('client_id', $contact->client_id)
                ->where('type', 'REGULAR_CUSTOMER')
                ->where('is_active', true)
                ->where('khata_number', trim($validated['khata_number']))
                ->where('id', '!=', $contact->id)
                ->first();

            if ($existingCustomer) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', "Khata Number {$validated['khata_number']} is already assigned to active customer \"{$existingCustomer->name}\". Deactivate that customer or use a different Khata Number.");
            }
        }

        $contact->update([
            'name' => $validated['name'],
            'khata_number' => $validated['khata_number'],
            'address' => $validated['address'] ?? null,
            'gst_number' => $validated['gst_number'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'is_active' => $request->has('is_active'),
        ]);

        if ($request->filled('phone_number')) {
            $primaryPhone = $contact->phoneNumbers()->where('is_primary', true)->first();
            if ($primaryPhone) {
                $primaryPhone->update(['phone_number' => $validated['phone_number']]);
            } else {
                ContactPhoneNumber::create([
                    'contact_id' => $contact->id,
                    'client_id' => $contact->client_id,
                    'contact_type' => $contact->type,
                    'phone_number' => $validated['phone_number'],
                    'is_primary' => true,
                ]);
            }
        }

        return redirect()->back()->with('success', 'Party updated successfully.');
    }

    public function statementPdf(Request $request, Contact $contact)
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() && $contact->client_id !== $user->client_id) {
            abort(403);
        }

        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');

        $allTransactions = $this->ledgerService->getTransactionsWithBalance($contact);
        
        $currentBalance = $allTransactions->isNotEmpty() 
            ? $allTransactions->last()->running_balance 
            : ($contact->opening_balance_type === 'ADVANCE' ? -$contact->opening_balance : $contact->opening_balance);

        $transactions = $allTransactions;

        if ($fromDate) {
            $transactions = $transactions->filter(function($tx) use ($fromDate) {
                return \Carbon\Carbon::parse($tx->transaction_date)->format('Y-m-d') >= $fromDate;
            });
        }

        if ($toDate) {
            $transactions = $transactions->filter(function($tx) use ($toDate) {
                return \Carbon\Carbon::parse($tx->transaction_date)->format('Y-m-d') <= $toDate;
            });
        }

        $pdf = Pdf::loadView('reports.statement', compact('contact', 'transactions', 'fromDate', 'toDate', 'currentBalance'));

        return $pdf->download("Statement_{$contact->name}_{$contact->khata_number}.pdf");
    }
}
