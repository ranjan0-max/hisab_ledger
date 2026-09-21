@extends('layouts.app')

@section('content')
    <style>
        .customer-form-modal .modal-dialog,
        .customer-form-modal .modal-content,
        .customer-form-modal .modal-content>form {
            max-height: calc(100dvh - 2rem);
        }

        .customer-form-modal .modal-content {
            overflow: hidden;
        }

        .customer-form-modal .modal-content>form {
            display: flex;
            flex-direction: column;
            min-height: 0;
        }

        .customer-form-modal .modal-header,
        .customer-form-modal .modal-footer {
            flex: 0 0 auto;
        }

        .customer-form-modal .modal-body {
            flex: 1 1 auto;
            min-height: 0;
            overflow-x: hidden;
            overflow-y: auto;
            overscroll-behavior: contain;
            -webkit-overflow-scrolling: touch;
        }

        .customer-filter-card {
            border: 1px solid #e0e7ff !important;
            background: #ffffff;
            box-shadow: 0 5px 18px rgba(79, 70, 229, 0.06) !important;
        }

        .customer-filter-heading {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            margin-bottom: 1rem;
        }

        .customer-filter-icon {
            display: inline-flex;
            flex: 0 0 34px;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            border: 1px solid #c7d2fe;
            border-radius: 9px;
            color: #4f46e5;
            background: #eef2ff;
        }

        .customer-filter-title {
            color: #334155;
            font-size: 0.84rem;
            font-weight: 700;
            line-height: 1.2;
        }

        .customer-filter-subtitle {
            margin-top: 0.12rem;
            color: #94a3b8;
            font-size: 0.68rem;
        }

        .customer-filter-label {
            margin-bottom: 0.35rem;
            color: #64748b;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .customer-filter-control .input-group-text {
            border-color: #e2e8f0;
            color: #6366f1;
            background: #f8fafc;
        }

        .customer-filter-control .form-control,
        .customer-filter-control .form-select {
            border-color: #e2e8f0;
            color: #334155;
            background-color: #ffffff;
            font-size: 0.82rem;
        }

        .customer-filter-control:focus-within .input-group-text,
        .customer-filter-control:focus-within .form-control,
        .customer-filter-control:focus-within .form-select {
            border-color: #a5b4fc;
            box-shadow: none;
        }

        .customer-filter-submit {
            border-color: #c7d2fe;
            color: #4338ca;
            background: #eef2ff;
        }

        .customer-filter-reset {
            border-color: #e2e8f0;
            color: #64748b;
            background: #f8fafc;
        }

        @media (max-width: 767.98px) {
            .customer-mobile-card {
                border: 1px solid #e2e8f0 !important;
                border-left: 1px solid #e2e8f0 !important;
                border-radius: 14px !important;
                background: #ffffff;
                box-shadow: 0 4px 14px rgba(15, 23, 42, 0.05) !important;
            }

            .customer-mobile-card:active {
                transform: none;
            }

            .customer-mobile-card .card-body {
                padding: 1rem !important;
            }

            .customer-mobile-card .min-w-0 {
                min-width: 0;
            }

            .customer-card-name {
                color: #0f172a;
                font-size: 0.95rem;
                line-height: 1.3;
            }

            .customer-khata {
                display: inline-flex;
                margin-top: 0.15rem;
                padding: 0.18rem 0.45rem;
                border: 1px solid #c7d2fe;
                border-radius: 6px;
                color: #4f46e5;
                background: #eef2ff;
                font-size: 0.7rem;
                font-weight: 600;
            }

            .customer-balance {
                flex: 0 0 auto;
                text-align: right;
            }

            .customer-balance-amount {
                font-size: 0.88rem;
                font-weight: 700;
                white-space: nowrap;
            }

            .customer-balance-label {
                display: inline-flex;
                margin-top: 0.1rem;
                padding: 0.15rem 0.4rem;
                border-radius: 999px;
                font-size: 0.62rem;
                font-weight: 700;
                letter-spacing: 0.04em;
            }

            .customer-balance-advance {
                color: #15803d;
                background: #f0fdf4;
            }

            .customer-balance-due {
                color: #b91c1c;
                background: #fef2f2;
            }

            .customer-balance-settled {
                color: #475569;
                background: #f1f5f9;
            }

            .customer-phone {
                margin-top: 0.75rem;
                padding-top: 0.65rem;
                border-top: 1px solid #eef2f7;
                color: #64748b;
                font-size: 0.76rem;
            }

            .customer-card-actions {
                display: flex;
                justify-content: flex-end;
                gap: 0.5rem;
                margin-top: 0.7rem;
            }

            .customer-card-action {
                border-radius: 8px;
                font-size: 0.76rem;
                font-weight: 600;
            }

            .customer-card-action-primary {
                border-color: #c7d2fe;
                color: #4338ca;
                background: #eef2ff;
            }

            .customer-card-action-secondary {
                border-color: #e2e8f0;
                color: #475569;
                background: #f8fafc;
            }

            .customer-form-modal .modal-dialog {
                height: 92dvh !important;
                max-height: 92dvh !important;
            }

            .customer-form-modal .modal-content {
                height: 100% !important;
                max-height: 100% !important;
                overflow: hidden !important;
            }

            .customer-form-modal .modal-content>form {
                height: 100%;
            }

            .customer-form-modal .modal-body {
                overflow-x: hidden !important;
                overflow-y: auto !important;
                touch-action: pan-y;
            }
        }

        @media (max-width: 575.98px) {
            .phone-number-row {
                position: relative;
            }

            .phone-number-row input[type="tel"] {
                padding-right: 5.25rem;
            }

            .phone-primary-button,
            .phone-remove-button {
                position: absolute;
                top: 0.5rem;
                z-index: 4;
                width: 2.15rem;
                height: 2.35rem;
                padding: 0 !important;
            }

            .phone-primary-button {
                right: 2.85rem;
            }

            .phone-remove-button {
                right: 0.5rem;
            }

            .phone-primary-button .form-check-input {
                position: absolute;
                opacity: 0;
                pointer-events: none;
            }

            .phone-primary-button:has(input:checked) {
                color: #b7791f;
                background-color: #fff3cd;
                border-color: #ffc107;
            }
        }
    </style>
    <div class="container-fluid">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-3 gap-2">
            <div>
                <h3 class="fw-bold text-dark mb-0">Customers</h3>
            </div>
            <div class="d-flex gap-2 justify-content-start justify-content-sm-end">
                <button class="btn btn-sm btn-outline-danger" id="customerPdfExportBtn"
                    onclick="handlePdfExport(this, '{{ route('customers.index', array_merge(request()->all(), ['export' => 'pdf'])) }}')">
                    <i class="bi bi-file-earmark-pdf me-1"></i> Export PDF
                </button>
                @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('customers.manage'))
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addPartyModal">
                        <i class="bi bi-person-plus me-1"></i> Add Customer
                    </button>
                @endif
            </div>
        </div>

        <!-- Search & Inactivity Filters -->
        <div class="card customer-filter-card mb-4">
            <div class="card-body p-3 p-md-4">
                <!-- <div class="customer-filter-heading">
                    <span class="customer-filter-icon"><i class="bi bi-funnel"></i></span>
                    <div>
                        <div class="customer-filter-title">Filter Customers</div>
                        <div class="customer-filter-subtitle">Search and narrow down the customer list</div>
                    </div>
                </div> -->
                <form method="GET" action="{{ route('customers.index') }}" class="row g-3 align-items-end">
                    <div class="col-12 col-lg-4">
                        <label class="customer-filter-label" for="customerSearch">Search</label>
                        <div class="input-group customer-filter-control">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" id="customerSearch" class="form-control"
                                placeholder="Name, khata no" value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <label class="customer-filter-label" for="customerActivity">Activity</label>
                        <div class="input-group customer-filter-control">
                            <span class="input-group-text"><i class="bi bi-clock-history"></i></span>
                            <select name="inactive_months" id="customerActivity" class="form-select" onchange="this.form.submit()">
                                <option value="">All customers</option>
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ request('inactive_months') == $m ? 'selected' : '' }}>
                                        Inactive for {{ $m }} {{ $m == 1 ? 'month' : 'months' }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <label class="customer-filter-label" for="customerBalance">Balance</label>
                        <div class="input-group customer-filter-control">
                            <span class="input-group-text"><i class="bi bi-wallet2"></i></span>
                            <select name="balance_filter" id="customerBalance" class="form-select" onchange="this.form.submit()">
                                <option value="">All balances</option>
                                <option value="non_zero" {{ request('balance_filter') === 'non_zero' ? 'selected' : '' }}>
                                    Hide zero balance
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="{{ request('search') || request('inactive_months') || request('balance_filter') ? 'col-6' : 'col-12' }} col-md-auto">
                        <button type="submit" class="btn customer-filter-submit w-100"><i class="bi bi-funnel me-1"></i>
                            Filter</button>
                    </div>
                    @if(request('search') || request('inactive_months') || request('balance_filter'))
                        <div class="col-6 col-md-auto">
                            <a href="{{ route('customers.index') }}" class="btn customer-filter-reset w-100"><i
                                    class="bi bi-x-lg me-1"></i> Reset</a>
                        </div>
                    @endif
                </form>
            </div>
        </div>

        <!-- Customers Table (Desktop) -->
        <div class="card border-0 shadow-sm d-none d-md-block">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Khata No</th>
                                <th>Customer Name</th>
                                <th>Mobile Number</th>
                                <th>Current Balance</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($contacts as $contact)
                                <tr>
                                    <td class="fw-bold text-primary">{{ $contact->khata_number }}</td>
                                    <td class="fw-semibold">{{ $contact->name }}</td>
                                    <td>{{ $contact->phoneNumbers->first()->phone_number ?? '—' }}</td>
                                    <td>
                                        @if($contact->current_balance < 0)
                                            <span
                                                class="fw-bold text-success">₹{{ number_format(abs($contact->current_balance), 2) }}
                                                (ADVANCE)</span>
                                        @elseif($contact->current_balance > 0)
                                            <span class="fw-bold text-danger">₹{{ number_format($contact->current_balance, 2) }}
                                                (DUE)</span>
                                        @else
                                            <span class="fw-bold text-secondary">₹0.00</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('customers.show', $contact->id) }}"
                                            class="btn btn-sm btn-outline-primary me-1" title="View Ledger">
                                            <i class="bi bi-journal-text"></i>
                                        </a>
                                        @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('customers.manage'))
                                            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal"
                                                data-bs-target="#editPartyModal{{ $contact->id }}" title="Edit Customer">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">No customers found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($contacts->hasPages())
                <div class="card-footer bg-white border-top py-3">
                    {{ $contacts->links() }}
                </div>
            @endif
        </div>

        <!-- Mobile View (Cards) -->
        <div class="d-md-none">
            @forelse($contacts as $contact)
                <div class="card mobile-card customer-mobile-card mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start gap-3">
                            <div class="min-w-0">
                                <h5 class="customer-card-name fw-bold mb-0 text-break">{{ $contact->name }}</h5>
                                <div class="customer-khata">Khata #{{ $contact->khata_number }}</div>
                            </div>
                            <div class="customer-balance">
                                @if($contact->current_balance < 0)
                                    <div class="customer-balance-amount text-success">₹{{ number_format(abs($contact->current_balance), 2) }}</div>
                                    <div class="customer-balance-label customer-balance-advance">ADVANCE</div>
                                @elseif($contact->current_balance > 0)
                                    <div class="customer-balance-amount text-danger">₹{{ number_format($contact->current_balance, 2) }}</div>
                                    <div class="customer-balance-label customer-balance-due">DUE</div>
                                @else
                                    <div class="customer-balance-amount text-secondary">₹0.00</div>
                                    <div class="customer-balance-label customer-balance-settled">SETTLED</div>
                                @endif
                            </div>
                        </div>
                        <div class="customer-phone">
                            <i class="bi bi-telephone me-1"></i>
                            {{ $contact->phoneNumbers->first()->phone_number ?? 'N/A' }}
                        </div>
                        <div class="customer-card-actions">
                            <a href="{{ route('customers.show', $contact->id) }}" class="btn btn-sm customer-card-action customer-card-action-primary" title="View Ledger">
                                <i class="bi bi-journal-text me-1"></i> View Ledger
                            </a>
                            @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('customers.manage'))
                                <button class="btn btn-sm customer-card-action customer-card-action-secondary" data-bs-toggle="modal"
                                    data-bs-target="#editPartyModal{{ $contact->id }}" title="Edit Customer">
                                    <i class="bi bi-pencil me-1"></i> Edit
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="card border-0 shadow-sm p-4 text-center text-muted">No customers found.</div>
            @endforelse

            @if($contacts->hasPages())
                <div class="py-3">
                    {{ $contacts->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Add Modal -->
    <div class="modal fade customer-form-modal" id="addPartyModal" tabindex="-1"
        data-khata-url="{{ route('khata-numbers.available', [], false) }}" data-party-type="REGULAR_CUSTOMER">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <form method="POST" action="{{ route('customers.store') }}">
                    @csrf
                    <input type="hidden" name="type" value="REGULAR_CUSTOMER">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Add Customer</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        @if(auth()->user()->isSuperAdmin())
                            <div class="mb-3">
                                <label class="form-label">Client / Shop *</label>
                                <select name="client_id" class="form-select" data-client-select required>
                                    <option value="">-- Select Client / Shop --</option>
                                    @foreach(\App\Models\Client::where('is_active', true)->get() as $client)
                                        <option value="{{ $client->id }}" @selected((string) old('client_id') === (string) $client->id)>{{ $client->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <div class="mb-3">
                            <label class="form-label">Customer Name *</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Khata Number *</label>
                            <select name="khata_number" class="form-select" data-khata-number-select
                                data-selected-number="{{ old('khata_number') }}" required disabled>
                                <option value="">Open the form to load available numbers...</option>
                            </select>
                            {{-- <div class="form-text">Available inactive numbers and the next 20 new numbers are shown.
                            </div> --}}
                        </div>
                        <div class="mb-3" data-phone-manager data-max-phones="5">
                            <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                                <div>
                                    <label class="form-label fw-semibold mb-0">Phone Numbers</label>
                                    <div class="form-text mt-0">Add up to 5 numbers and select the primary one.</div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary flex-shrink-0" data-add-phone>
                                    <i class="bi bi-plus-lg me-1"></i>Add
                                </button>
                            </div>
                            <div data-phone-list>
                                <div class="phone-number-row border rounded-3 bg-light p-2 mb-2">
                                    <div
                                        class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center gap-2">
                                        <div class="input-group flex-grow-1">
                                            <span class="input-group-text bg-white"><i class="bi bi-telephone"></i></span>
                                            <input type="tel" name="phone_numbers[]" class="form-control" maxlength="20"
                                                inputmode="tel" autocomplete="tel" placeholder="Enter phone number">
                                        </div>
                                        <label
                                            class="phone-primary-button btn btn-outline-secondary d-flex align-items-center justify-content-center gap-1 mb-0 flex-shrink-0"
                                            title="Set as primary phone number">
                                            <input type="radio" name="primary_phone_index" value="0"
                                                class="form-check-input mt-0" checked>
                                            <i class="bi bi-star-fill d-sm-none" aria-hidden="true"></i>
                                            <span class="small d-none d-sm-inline">Primary</span>
                                        </label>
                                        <button type="button"
                                            class="phone-remove-button btn btn-outline-danger flex-shrink-0"
                                            data-remove-phone aria-label="Remove phone number" title="Remove phone number">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="form-text" data-phone-count>1 of 5 numbers</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="2"
                                placeholder="Enter customer notes (optional)"></textarea>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-8">
                                <label class="form-label">Opening Balance</label>
                                <input type="number" step="0.01" name="opening_balance" class="form-control" value="0.00">
                            </div>
                            <div class="col-4">
                                <label class="form-label">Type</label>
                                <select name="opening_balance_type" class="form-select">
                                    <option value="DUE">DUE</option>
                                    <option value="ADVANCE">ADVANCE</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Customer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modals -->
    @foreach($contacts as $contact)
        <div class="modal fade customer-form-modal" id="editPartyModal{{ $contact->id }}" tabindex="-1">
            <div class="modal-dialog modal-dialog-scrollable">
                <div class="modal-content">
                    <form method="POST" action="{{ route('customers.update', $contact->id) }}">
                        @csrf
                        @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title fw-bold">Edit Customer</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Customer Name *</label>
                                <input type="text" name="name" class="form-control" value="{{ $contact->name }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Khata Number *</label>
                                <input type="number" name="khata_number" class="form-control"
                                    value="{{ $contact->khata_number }}" min="1" step="1" inputmode="numeric" required>
                            </div>
                            @php
                                $editablePhones = $contact->phoneNumbers->values();
                            @endphp
                            <div class="mb-3" data-phone-manager data-max-phones="5">
                                <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                                    <div>
                                        <label class="form-label fw-semibold mb-0">Phone Numbers</label>
                                        <div class="form-text mt-0">Add up to 5 numbers and select the primary one.</div>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-primary flex-shrink-0" data-add-phone>
                                        <i class="bi bi-plus-lg me-1"></i>Add
                                    </button>
                                </div>
                                <div data-phone-list>
                                    @forelse($editablePhones as $phoneIndex => $phone)
                                        <div class="phone-number-row border rounded-3 bg-light p-2 mb-2">
                                            <div
                                                class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center gap-2">
                                                <div class="input-group flex-grow-1">
                                                    <span class="input-group-text bg-white"><i class="bi bi-telephone"></i></span>
                                                    <input type="tel" name="phone_numbers[]" class="form-control"
                                                        value="{{ $phone->phone_number }}" maxlength="20" inputmode="tel"
                                                        autocomplete="tel" placeholder="Enter phone number">
                                                </div>
                                                <label
                                                    class="phone-primary-button btn btn-outline-secondary d-flex align-items-center justify-content-center gap-1 mb-0 flex-shrink-0"
                                                    title="Set as primary phone number">
                                                    <input type="radio" name="primary_phone_index" value="{{ $phoneIndex }}"
                                                        class="form-check-input mt-0" {{ $phone->is_primary || (!$editablePhones->contains('is_primary', true) && $phoneIndex === 0) ? 'checked' : '' }}>
                                                    <i class="bi bi-star-fill d-sm-none" aria-hidden="true"></i>
                                                    <span class="small d-none d-sm-inline">Primary</span>
                                                </label>
                                                <button type="button"
                                                    class="phone-remove-button btn btn-outline-danger flex-shrink-0"
                                                    data-remove-phone aria-label="Remove phone number" title="Remove phone number">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="phone-number-row border rounded-3 bg-light p-2 mb-2">
                                            <div
                                                class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center gap-2">
                                                <div class="input-group flex-grow-1">
                                                    <span class="input-group-text bg-white"><i class="bi bi-telephone"></i></span>
                                                    <input type="tel" name="phone_numbers[]" class="form-control" maxlength="20"
                                                        inputmode="tel" autocomplete="tel" placeholder="Enter phone number">
                                                </div>
                                                <label
                                                    class="phone-primary-button btn btn-outline-secondary d-flex align-items-center justify-content-center gap-1 mb-0 flex-shrink-0"
                                                    title="Set as primary phone number">
                                                    <input type="radio" name="primary_phone_index" value="0"
                                                        class="form-check-input mt-0" checked>
                                                    <i class="bi bi-star-fill d-sm-none" aria-hidden="true"></i>
                                                    <span class="small d-none d-sm-inline">Primary</span>
                                                </label>
                                                <button type="button"
                                                    class="phone-remove-button btn btn-outline-danger flex-shrink-0"
                                                    data-remove-phone aria-label="Remove phone number" title="Remove phone number">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @endforelse
                                </div>
                                <div class="form-text" data-phone-count></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Address</label>
                                <textarea name="address" class="form-control" rows="2">{{ $contact->address }}</textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" class="form-control" rows="2"
                                    placeholder="Enter customer notes (optional)">{{ $contact->notes }}</textarea>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                    id="editCustomerActive{{ $contact->id }}" {{ $contact->is_active ? 'checked' : '' }}>
                                <label class="form-check-label" for="editCustomerActive{{ $contact->id }}">Active
                                    Customer</label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Update Customer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const addCustomerModal = document.getElementById('addPartyModal');

            if (addCustomerModal) {
                const khataSelect = addCustomerModal.querySelector('[data-khata-number-select]');
                const clientSelect = addCustomerModal.querySelector('[data-client-select]');
                let activeRequest = null;

                function showKhataStatus(message, disabled = true) {
                    khataSelect.replaceChildren(new Option(message, ''));
                    khataSelect.disabled = disabled;
                }

                function appendKhataGroup(label, numbers) {
                    if (!numbers.length) return;

                    const group = document.createElement('optgroup');
                    group.label = label;

                    numbers.forEach(function (number) {
                        group.appendChild(new Option(String(number), String(number)));
                    });

                    khataSelect.appendChild(group);
                }

                async function loadAvailableKhataNumbers() {
                    const clientId = clientSelect ? clientSelect.value : '';

                    if (clientSelect && !clientId) {
                        showKhataStatus('Select a client / shop first');
                        return;
                    }

                    if (activeRequest) activeRequest.abort();
                    activeRequest = new AbortController();
                    showKhataStatus('Loading available numbers...');

                    const url = new URL(addCustomerModal.dataset.khataUrl, window.location.origin);
                    url.searchParams.set('type', addCustomerModal.dataset.partyType);
                    if (clientId) url.searchParams.set('client_id', clientId);

                    try {
                        const response = await fetch(url, {
                            headers: { 'Accept': 'application/json' },
                            signal: activeRequest.signal,
                        });

                        if (!response.ok) throw new Error('Unable to load available Khata Numbers.');

                        const data = await response.json();
                        const selectedNumber = khataSelect.dataset.selectedNumber;

                        khataSelect.replaceChildren(new Option('-- Select Khata Number --', ''));
                        appendKhataGroup('Reusable Numbers', data.reusable || []);
                        appendKhataGroup('New Numbers', data.new || []);
                        khataSelect.disabled = false;

                        if (selectedNumber && Array.from(khataSelect.options).some(option => option.value === selectedNumber)) {
                            khataSelect.value = selectedNumber;
                        }
                    } catch (error) {
                        if (error.name !== 'AbortError') {
                            showKhataStatus('Could not load numbers. Please try again.');
                        }
                    }
                }

                addCustomerModal.addEventListener('show.bs.modal', loadAvailableKhataNumbers);
                clientSelect?.addEventListener('change', loadAvailableKhataNumbers);
            }

            document.querySelectorAll('[data-phone-manager]').forEach(function (manager) {
                const list = manager.querySelector('[data-phone-list]');
                const addButton = manager.querySelector('[data-add-phone]');
                const countLabel = manager.querySelector('[data-phone-count]');
                const maxPhones = Number(manager.dataset.maxPhones || 5);

                function updatePhoneRows() {
                    const rows = Array.from(list.querySelectorAll('.phone-number-row'));

                    rows.forEach(function (row, index) {
                        const primaryRadio = row.querySelector('input[type="radio"]');
                        const removeButton = row.querySelector('[data-remove-phone]');

                        primaryRadio.value = index;
                        removeButton.disabled = rows.length === 1;
                    });

                    if (!rows.some(function (row) { return row.querySelector('input[type="radio"]').checked; })) {
                        rows[0].querySelector('input[type="radio"]').checked = true;
                    }

                    addButton.disabled = rows.length >= maxPhones;
                    countLabel.textContent = rows.length + ' of ' + maxPhones + ' numbers';
                }

                addButton.addEventListener('click', function () {
                    const rows = list.querySelectorAll('.phone-number-row');
                    if (rows.length >= maxPhones) return;

                    const newRow = rows[0].cloneNode(true);
                    newRow.querySelector('input[type="tel"]').value = '';
                    newRow.querySelector('input[type="radio"]').checked = false;
                    list.appendChild(newRow);
                    updatePhoneRows();
                    newRow.querySelector('input[type="tel"]').focus();
                });

                list.addEventListener('click', function (event) {
                    const removeButton = event.target.closest('[data-remove-phone]');
                    if (!removeButton || list.querySelectorAll('.phone-number-row').length === 1) return;

                    removeButton.closest('.phone-number-row').remove();
                    updatePhoneRows();
                });

                updatePhoneRows();
            });
        });
    </script>
@endsection
