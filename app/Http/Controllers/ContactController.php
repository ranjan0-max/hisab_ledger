<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Contact;
use App\Models\ContactPhoneNumber;
use App\Models\LedgerTransaction;
use App\Services\LedgerService;
use App\Services\KhataNumberService;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    protected LedgerService $ledgerService;
    protected KhataNumberService $khataNumberService;

    public function __construct(LedgerService $ledgerService, KhataNumberService $khataNumberService)
    {
        $this->ledgerService = $ledgerService;
        $this->khataNumberService = $khataNumberService;
    }

    public function availableKhataNumbers(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'type' => ['required', 'in:REGULAR_CUSTOMER,SUPPLIER'],
            'client_id' => [$user->isSuperAdmin() ? 'required' : 'nullable', 'integer', 'exists:clients,id'],
        ]);

        $requiredPermission = $validated['type'] === 'REGULAR_CUSTOMER'
            ? 'customers.manage'
            : 'suppliers.manage';

        abort_unless($user->isSuperAdmin() || $user->hasPermission($requiredPermission), 403);

        $clientId = $user->isSuperAdmin()
            ? (int) $validated['client_id']
            : (int) $user->client_id;

        abort_if($clientId < 1, 422, 'A client/shop must be selected.');

        $client = Client::findOrFail($clientId);
        abort_unless($client->khata_number_enabled, 422, 'Khata Number is disabled for this client/shop.');

        return response()
            ->json($this->khataNumberService->availableFor($clientId, $validated['type']))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate');
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
        $query = $this->contactListQuery(
            Contact::with(['phoneNumbers', 'client'])->where('contacts.type', $type),
            $type,
            $request->get('search'),
            $request->get('inactive_months'),
            $request->get('balance_filter')
        );

        $contacts = $query
            ->orderBy('contacts.khata_number')
            ->paginate(10)
            ->withQueryString();

        $view = $type === 'REGULAR_CUSTOMER' ? 'customers.index' : 'suppliers.index';

        return view($view, compact('contacts'));
    }

    public function show(Contact $contact)
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() && $contact->client_id !== $user->client_id) {
            abort(403);
        }

        $contact->load(['phoneNumbers', 'client']);

        // Plain transaction fetch — no PHP loop, no running_balance per row
        $transactions = LedgerTransaction::where('contact_id', $contact->id)
            ->where('status', 'POSTED')
            ->orderBy('transaction_date', 'asc')
            ->orderBy('id', 'asc')
            ->paginate(50)
            ->withQueryString();

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
        $khataNumberEnabled = $this->khataNumberEnabledForRequest($request, $user);

        $validated = $request->validate([
            'type' => ['required', 'in:REGULAR_CUSTOMER,SUPPLIER'],
            'name' => ['required', 'string', 'max:150'],
            'khata_number' => [$khataNumberEnabled ? 'required' : 'nullable', 'integer', 'min:1'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'phone_numbers' => ['nullable', 'array', 'max:5'],
            'phone_numbers.*' => ['nullable', 'string', 'max:20'],
            'primary_phone_index' => ['nullable', 'integer', 'min:0', 'max:4'],
            'address' => ['nullable', 'string'],
            'gst_number' => ['nullable', 'string', 'max:30'],
            'notes' => ['nullable', 'string'],
            'opening_balance' => ['nullable', 'numeric', 'min:0'],
            'opening_balance_type' => ['nullable', 'in:DUE,ADVANCE'],
            'client_id' => [$user->isSuperAdmin() ? 'required' : 'nullable', 'exists:clients,id'],
        ]);

        $clientId = (int) ($user->isSuperAdmin() ? $validated['client_id'] : $user->client_id);
        $khataNumber = $khataNumberEnabled ? (int) $validated['khata_number'] : null;

        // Custom Validation Rule: Active Party (Customer/Supplier) Khata Number uniqueness check
        if ($khataNumber !== null) {
            $existingParty = Contact::where('contacts.client_id', $clientId)
                ->where('contacts.type', $validated['type'])
                ->where('contacts.is_active', true)
                ->where('contacts.khata_number', $khataNumber)
                ->first();

            if ($existingParty) {
                $partyLabel = $validated['type'] === 'REGULAR_CUSTOMER' ? 'customer' : 'supplier';
                return redirect()->back()
                    ->withInput()
                    ->with('error', "Khata Number {$khataNumber} is already assigned to active {$partyLabel} \"{$existingParty->name}\". Deactivate that {$partyLabel} or use a different Khata Number.");
            }
        }

        $contact = Contact::create([
            'client_id' => $clientId,
            'type' => $validated['type'],
            'name' => $validated['name'],
            'khata_number' => $khataNumber,
            'address' => $validated['address'] ?? null,
            'gst_number' => $validated['gst_number'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'opening_balance' => $validated['opening_balance'] ?? 0,
            'opening_balance_type' => $validated['opening_balance_type'] ?? 'DUE',
            'is_active' => true,
        ]);

        if ($request->has('phone_numbers')) {
            $this->syncPhoneNumbers(
                $contact,
                $validated['phone_numbers'] ?? [],
                isset($validated['primary_phone_index']) ? (int) $validated['primary_phone_index'] : null
            );
        } elseif (!empty($validated['phone_number'])) {
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

        $contact->loadMissing('client');
        $khataNumberEnabled = (bool) ($contact->client?->khata_number_enabled ?? true);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'khata_number' => [$khataNumberEnabled ? 'required' : 'nullable', 'integer', 'min:1'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'phone_numbers' => ['nullable', 'array', 'max:5'],
            'phone_numbers.*' => ['nullable', 'string', 'max:20'],
            'primary_phone_index' => ['nullable', 'integer', 'min:0', 'max:4'],
            'address' => ['nullable', 'string'],
            'gst_number' => ['nullable', 'string', 'max:30'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        $isActive = $request->has('is_active');
        $khataNumber = $khataNumberEnabled
            ? (int) $validated['khata_number']
            : $contact->khata_number;

        // Custom Validation Rule: Active Party (Customer/Supplier) Khata Number uniqueness check
        if ($isActive && $khataNumber !== null) {
            $existingParty = Contact::where('contacts.client_id', $contact->client_id)
                ->where('contacts.type', $contact->type)
                ->where('contacts.is_active', true)
                ->where('contacts.khata_number', $khataNumber)
                ->where('contacts.id', '!=', $contact->id)
                ->first();

            if ($existingParty) {
                $partyLabel = $contact->type === 'REGULAR_CUSTOMER' ? 'customer' : 'supplier';
                return redirect()->back()
                    ->withInput()
                    ->with('error', "Khata Number {$khataNumber} is already assigned to active {$partyLabel} \"{$existingParty->name}\". Deactivate that {$partyLabel} or use a different Khata Number.");
            }
        }

        $contact->update([
            'name' => $validated['name'],
            'khata_number' => $khataNumber,
            'address' => $validated['address'] ?? null,
            'gst_number' => $validated['gst_number'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'is_active' => $request->has('is_active'),
        ]);

        if ($request->has('phone_numbers')) {
            $this->syncPhoneNumbers(
                $contact,
                $validated['phone_numbers'] ?? [],
                isset($validated['primary_phone_index']) ? (int) $validated['primary_phone_index'] : null
            );
        } elseif ($request->filled('phone_number')) {
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

    private function khataNumberEnabledForRequest(Request $request, $user): bool
    {
        if (!$user->isSuperAdmin()) {
            return (bool) ($user->client?->khata_number_enabled ?? true);
        }

        $clientId = (int) $request->input('client_id');
        if ($clientId < 1) {
            return true;
        }

        return (bool) (Client::whereKey($clientId)->value('khata_number_enabled') ?? true);
    }

    private function syncPhoneNumbers(Contact $contact, array $phoneNumbers, ?int $primaryIndex): void
    {
        $normalizedNumbers = [];
        $seenNumbers = [];

        foreach ($phoneNumbers as $index => $phoneNumber) {
            $phoneNumber = trim((string) $phoneNumber);

            if ($phoneNumber === '' || isset($seenNumbers[$phoneNumber])) {
                continue;
            }

            $seenNumbers[$phoneNumber] = true;
            $normalizedNumbers[] = [
                'number' => $phoneNumber,
                'original_index' => (int) $index,
            ];
        }

        $hasSelectedPrimary = collect($normalizedNumbers)
            ->contains(fn(array $phone) => $phone['original_index'] === $primaryIndex);

        $contact->phoneNumbers()->delete();

        foreach ($normalizedNumbers as $position => $phone) {
            ContactPhoneNumber::create([
                'contact_id' => $contact->id,
                'client_id' => $contact->client_id,
                'contact_type' => $contact->type,
                'phone_number' => $phone['number'],
                'is_primary' => $hasSelectedPrimary
                    ? $phone['original_index'] === $primaryIndex
                    : $position === 0,
            ]);
        }
    }

    /**
     * Apply the customers/suppliers list filters (search, inactive months, non-zero balance) with current balance.
     * Used by the list screen and the shared list page so both show the same rows.
     */
    private function contactListQuery($query, string $type, ?string $search, $inactiveMonths, ?string $balanceFilter)
    {
        if (trim((string) $search) !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('contacts.name', 'like', "%{$search}%")
                    ->when(ctype_digit(trim($search)), function ($searchQuery) use ($search) {
                        $searchQuery->orWhere('contacts.khata_number', (int) $search);
                    });
            });
        }

        $query->withCurrentBalance();

        if ($type === 'REGULAR_CUSTOMER' && $balanceFilter === 'non_zero') {
            $query->having('current_balance', '!=', 0);
        }

        if ($inactiveMonths) {
            $cutoffDate = \Carbon\Carbon::now()->subMonths((int) $inactiveMonths)->format('Y-m-d');
            $query->having('current_balance', '>', 0)
                ->whereDoesntHave('transactions', function ($tq) use ($cutoffDate) {
                    $tq->withoutGlobalScope(\App\Scopes\TenantScope::class)
                        ->where('status', 'POSTED')
                        ->where('transaction_date', '>=', $cutoffDate);
                });
        }

        return $query;
    }

    /**
     * Create a signed, 7-day share link to the public statement page.
     */
    public function statementShareLink(Request $request, Contact $contact)
    {
        $this->authorizeStatement($contact);

        return $this->shareLinkResponse('statement.shared', [
            'contact' => $contact->id,
            'from_date' => $request->get('from_date'),
            'to_date' => $request->get('to_date'),
        ]);
    }

    public function customersShareLink(Request $request)
    {
        return $this->listShareLink($request, 'customers');
    }

    public function suppliersShareLink(Request $request)
    {
        return $this->listShareLink($request, 'suppliers');
    }

    /**
     * Create a signed, 7-day share link to the public customers/suppliers list with the current filters.
     */
    private function listShareLink(Request $request, string $listType)
    {
        return $this->shareLinkResponse('contacts.shared', [
            'listType' => $listType,
            'client' => $this->shareClientId(),
            'search' => $request->get('search'),
            'inactive_months' => $request->get('inactive_months'),
            'balance_filter' => $listType === 'customers' ? $request->get('balance_filter') : null,
        ]);
    }

    /**
     * Public customers/suppliers list opened from a share link (no login; the signed route guards it).
     */
    public function sharedList(Request $request, string $listType)
    {
        $type = $listType === 'customers' ? 'REGULAR_CUSTOMER' : 'SUPPLIER';

        // The signature fixes the client and filters, so skip the viewer's tenant scope
        $query = Contact::withoutGlobalScopes()
            ->with(['phoneNumbers', 'client'])
            ->where('contacts.type', $type)
            ->when($request->get('client'), fn($q, $clientId) => $q->where('contacts.client_id', $clientId));

        // 500 rows per page so the phone browser can preview/save the PDF quickly
        $contacts = $this->contactListQuery($query, $type, $request->get('search'), $request->get('inactive_months'), $request->get('balance_filter'))
            ->orderBy('contacts.khata_number')
            ->paginate(500)
            ->withQueryString();

        $client = $request->get('client') ? \App\Models\Client::find($request->get('client')) : null;
        $inactiveMonths = $request->get('inactive_months');

        return view('reports.contacts_shared', compact('contacts', 'type', 'client', 'inactiveMonths'));
    }

    /**
     * Public statement page opened from a share link (no login; the signed route guards it).
     */
    public function sharedStatement(Request $request, int $contact)
    {
        // The signature already proves which contact this link was made for, so skip the tenant scope
        $contact = Contact::withoutGlobalScopes()->with(['client', 'phoneNumbers'])->findOrFail($contact);

        // 500 entries per page so the phone browser can preview/save the PDF quickly
        return view('reports.statement_shared', $this->statementData($contact, $request->get('from_date'), $request->get('to_date'), 500));
    }

    private function authorizeStatement(Contact $contact): void
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() && $contact->client_id !== $user->client_id) {
            abort(403);
        }
    }

    private function statementData(Contact $contact, ?string $fromDate, ?string $toDate, ?int $perPage = null): array
    {
        // Caller has already authorized the contact; entries must follow the contact, not the viewer's tenant
        $entries = fn() => $contact->transactions()->withoutGlobalScope(\App\Scopes\TenantScope::class);

        // Opening balance base
        $openingBalance = $contact->opening_balance_type === 'ADVANCE'
            ? -(float) $contact->opening_balance
            : (float) $contact->opening_balance;
        $baseOpeningBalance = $openingBalance;

        // Prior period sum (before fromDate) — only if date filter set
        if ($fromDate) {
            $priorQuery = $entries()->where('status', 'POSTED')->where('transaction_date', '<', $fromDate);
            if ($contact->type === 'REGULAR_CUSTOMER') {
                $priorSum = $priorQuery->selectRaw("SUM(CASE WHEN transaction_type IN ('SALE','CASH_GIVEN','ADJUSTMENT') THEN amount WHEN transaction_type = 'CUSTOMER_PAYMENT' THEN -amount ELSE 0 END) as total")->value('total') ?? 0;
            } else {
                $priorSum = $priorQuery->selectRaw("SUM(CASE WHEN transaction_type IN ('PURCHASE','ADJUSTMENT') THEN amount WHEN transaction_type = 'SUPPLIER_PAYMENT' THEN -amount ELSE 0 END) as total")->value('total') ?? 0;
            }
            $openingBalance += (float) $priorSum;
        }

        // Plain transactions fetch — no running_balance loop needed
        $transactions = $entries()
            ->where('status', 'POSTED')
            ->when($fromDate, fn($q) => $q->where('transaction_date', '>=', $fromDate))
            ->when($toDate, fn($q) => $q->where('transaction_date', '<=', $toDate))
            ->orderBy('transaction_date', 'asc')
            ->orderBy('id', 'asc');
        $transactions = $perPage
            ? $transactions->paginate($perPage)->withQueryString()
            : $transactions->get();

        // Current balance: opening + ALL posted entries (incl. future-dated), ignoring the date filter — same as the screen
        if ($contact->type === 'REGULAR_CUSTOMER') {
            $txSum = $entries()
                ->where('status', 'POSTED')
                ->selectRaw("SUM(CASE WHEN transaction_type IN ('SALE','CASH_GIVEN','ADJUSTMENT') THEN amount WHEN transaction_type = 'CUSTOMER_PAYMENT' THEN -amount ELSE 0 END) as net")
                ->value('net') ?? 0;
        } else {
            $txSum = $entries()
                ->where('status', 'POSTED')
                ->selectRaw("SUM(CASE WHEN transaction_type IN ('PURCHASE','ADJUSTMENT') THEN amount WHEN transaction_type = 'SUPPLIER_PAYMENT' THEN -amount ELSE 0 END) as net")
                ->value('net') ?? 0;
        }

        $currentBalance = $baseOpeningBalance + (float) $txSum;

        return compact('contact', 'transactions', 'fromDate', 'toDate', 'currentBalance', 'openingBalance');
    }
}
