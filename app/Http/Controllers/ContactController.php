<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\ContactPhoneNumber;
use App\Models\LedgerTransaction;
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
        $query = Contact::with('phoneNumbers')->where('contacts.type', $type);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('contacts.name', 'like', "%{$search}%")
                  ->orWhere('contacts.khata_number', 'like', "%{$search}%")
                  ->orWhereHas('phoneNumbers', function($pq) use ($search) {
                      $pq->where('phone_number', 'like', "%{$search}%");
                  });
            });
        }

        $inactiveMonths = $request->get('inactive_months');

        // Check if PDF Export is requested
        if ($request->get('export') === 'pdf') {
            $exportQuery = (clone $query)->withCurrentBalance();
            if ($inactiveMonths) {
                $cutoffDate = \Carbon\Carbon::now()->subMonths((int)$inactiveMonths)->format('Y-m-d');
                $exportQuery->having('current_balance', '>', 0)
                    ->whereDoesntHave('transactions', function($tq) use ($cutoffDate) {
                        $tq->where('status', 'POSTED')
                           ->where('transaction_date', '>=', $cutoffDate);
                    });
            }
            $exportContacts = $exportQuery->latest('contacts.id')->get();

            $pdf = Pdf::loadView('reports.customers_list', compact('exportContacts', 'type', 'inactiveMonths'));
            $fileName = ($type === 'REGULAR_CUSTOMER' ? "Customers_List_" : "Suppliers_List_") . date('Y-m-d') . ".pdf";
            return $pdf->download($fileName);
        }

        $query->withCurrentBalance();

        if ($inactiveMonths) {
            $cutoffDate = \Carbon\Carbon::now()->subMonths((int)$inactiveMonths)->format('Y-m-d');
            $query->having('current_balance', '>', 0)
                ->whereDoesntHave('transactions', function($tq) use ($cutoffDate) {
                    $tq->where('status', 'POSTED')
                       ->where('transaction_date', '>=', $cutoffDate);
                });
        }

        $contacts = $query->latest('contacts.id')->paginate(10)->withQueryString();

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

        // Plain transaction fetch — no PHP loop, no running_balance per row
        $transactions = LedgerTransaction::where('contact_id', $contact->id)
            ->where('status', 'POSTED')
            ->orderBy('transaction_date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        // Current balance via single SQL aggregate — no PHP loop needed
        $openingBalance = $contact->opening_balance_type === 'ADVANCE'
            ? -(float) $contact->opening_balance
            : (float) $contact->opening_balance;

        if ($contact->type === 'REGULAR_CUSTOMER') {
            $txSum = LedgerTransaction::where('contact_id', $contact->id)
                ->where('status', 'POSTED')
                ->selectRaw("SUM(CASE WHEN transaction_type IN ('SALE','CASH_GIVEN','ADJUSTMENT') THEN amount WHEN transaction_type = 'CUSTOMER_PAYMENT' THEN -amount ELSE 0 END) as net")
                ->value('net') ?? 0;
        } else {
            $txSum = LedgerTransaction::where('contact_id', $contact->id)
                ->where('status', 'POSTED')
                ->selectRaw("SUM(CASE WHEN transaction_type IN ('PURCHASE','ADJUSTMENT') THEN amount WHEN transaction_type = 'SUPPLIER_PAYMENT' THEN -amount ELSE 0 END) as net")
                ->value('net') ?? 0;
        }

        $currentBalance = $openingBalance + (float) $txSum;

        $view = $contact->type === 'REGULAR_CUSTOMER' ? 'customers.show' : 'suppliers.show';

        return view($view, compact('contact', 'transactions', 'currentBalance'));
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

        // Custom Validation Rule: Active Party (Customer/Supplier) Khata Number uniqueness check
        $existingParty = Contact::where('contacts.type', $validated['type'])
            ->where('contacts.is_active', true)
            ->where('contacts.khata_number', trim($validated['khata_number']))
            ->first();

        if ($existingParty) {
            $partyLabel = $validated['type'] === 'REGULAR_CUSTOMER' ? 'customer' : 'supplier';
            return redirect()->back()
                ->withInput()
                ->with('error', "Khata Number {$validated['khata_number']} is already assigned to active {$partyLabel} \"{$existingParty->name}\". Deactivate that {$partyLabel} or use a different Khata Number.");
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

        // Custom Validation Rule: Active Party (Customer/Supplier) Khata Number uniqueness check
        if ($isActive) {
            $existingParty = Contact::where('contacts.type', $contact->type)
                ->where('contacts.is_active', true)
                ->where('contacts.khata_number', trim($validated['khata_number']))
                ->where('contacts.id', '!=', $contact->id)
                ->first();

            if ($existingParty) {
                $partyLabel = $contact->type === 'REGULAR_CUSTOMER' ? 'customer' : 'supplier';
                return redirect()->back()
                    ->withInput()
                    ->with('error', "Khata Number {$validated['khata_number']} is already assigned to active {$partyLabel} \"{$existingParty->name}\". Deactivate that {$partyLabel} or use a different Khata Number.");
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
        $toDate   = $request->get('to_date');

        // Opening balance base
        $openingBalance = $contact->opening_balance_type === 'ADVANCE'
            ? -(float) $contact->opening_balance
            : (float) $contact->opening_balance;

        // Prior period sum (before fromDate) — only if date filter set
        if ($fromDate) {
            $priorQuery = $contact->transactions()->where('status', 'POSTED')->where('transaction_date', '<', $fromDate);
            if ($contact->type === 'REGULAR_CUSTOMER') {
                $priorSum = $priorQuery->selectRaw("SUM(CASE WHEN transaction_type IN ('SALE','CASH_GIVEN','ADJUSTMENT') THEN amount WHEN transaction_type = 'CUSTOMER_PAYMENT' THEN -amount ELSE 0 END) as total")->value('total') ?? 0;
            } else {
                $priorSum = $priorQuery->selectRaw("SUM(CASE WHEN transaction_type IN ('PURCHASE','ADJUSTMENT') THEN amount WHEN transaction_type = 'SUPPLIER_PAYMENT' THEN -amount ELSE 0 END) as total")->value('total') ?? 0;
            }
            $openingBalance += (float) $priorSum;
        }

        // Plain transactions fetch — no running_balance loop needed
        $transactions = $contact->transactions()
            ->where('status', 'POSTED')
            ->when($fromDate, fn($q) => $q->where('transaction_date', '>=', $fromDate))
            ->when($toDate,   fn($q) => $q->where('transaction_date', '<=', $toDate))
            ->orderBy('transaction_date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        // Current balance via single SQL aggregate — no PHP loop at all
        if ($contact->type === 'REGULAR_CUSTOMER') {
            $rangeSum = $contact->transactions()
                ->where('status', 'POSTED')
                ->when($fromDate, fn($q) => $q->where('transaction_date', '>=', $fromDate))
                ->when($toDate,   fn($q) => $q->where('transaction_date', '<=', $toDate))
                ->selectRaw("SUM(CASE WHEN transaction_type IN ('SALE','CASH_GIVEN','ADJUSTMENT') THEN amount WHEN transaction_type = 'CUSTOMER_PAYMENT' THEN -amount ELSE 0 END) as net")
                ->value('net') ?? 0;
        } else {
            $rangeSum = $contact->transactions()
                ->where('status', 'POSTED')
                ->when($fromDate, fn($q) => $q->where('transaction_date', '>=', $fromDate))
                ->when($toDate,   fn($q) => $q->where('transaction_date', '<=', $toDate))
                ->selectRaw("SUM(CASE WHEN transaction_type IN ('PURCHASE','ADJUSTMENT') THEN amount WHEN transaction_type = 'SUPPLIER_PAYMENT' THEN -amount ELSE 0 END) as net")
                ->value('net') ?? 0;
        }

        $currentBalance = $openingBalance + (float) $rangeSum;

        $pdf = Pdf::loadView('reports.statement', compact('contact', 'transactions', 'fromDate', 'toDate', 'currentBalance', 'openingBalance'));

        return $pdf->download("Statement_{$contact->name}_{$contact->khata_number}.pdf");
    }
}
