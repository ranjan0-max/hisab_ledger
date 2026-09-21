@extends('layouts.app')

@section('content')
<style>
    .supplier-filter-card {
        border: 1px solid #cffafe !important;
        background: #ffffff;
        box-shadow: 0 5px 18px rgba(8, 145, 178, 0.06) !important;
    }

    .supplier-filter-control .input-group-text {
        border-color: #e2e8f0;
        color: #0891b2;
        background: #ecfeff;
    }

    .supplier-filter-control .form-control {
        border-color: #e2e8f0;
        color: #334155;
        font-size: 0.82rem;
    }

    .supplier-filter-control:focus-within .input-group-text,
    .supplier-filter-control:focus-within .form-control {
        border-color: #67e8f9;
        box-shadow: none;
    }

    .supplier-filter-submit {
        border-color: #a5f3fc;
        color: #0e7490;
        background: #ecfeff;
    }

    .supplier-filter-reset {
        border-color: #e2e8f0;
        color: #64748b;
        background: #f8fafc;
    }

    @media (max-width: 767.98px) {
        .supplier-mobile-card {
            border: 1px solid #e2e8f0 !important;
            border-left: 1px solid #e2e8f0 !important;
            border-radius: 14px !important;
            background: #ffffff;
            box-shadow: 0 4px 14px rgba(15, 23, 42, 0.05) !important;
        }

        .supplier-mobile-card:active {
            transform: none;
        }

        .supplier-mobile-card .card-body {
            padding: 1rem !important;
        }

        .supplier-mobile-card .min-w-0 {
            min-width: 0;
        }

        .supplier-card-name {
            color: #0f172a;
            font-size: 0.95rem;
            line-height: 1.3;
        }

        .supplier-khata {
            display: inline-flex;
            margin-top: 0.15rem;
            padding: 0.18rem 0.45rem;
            border: 1px solid #a5f3fc;
            border-radius: 6px;
            color: #0e7490;
            background: #ecfeff;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .supplier-balance {
            flex: 0 0 auto;
            text-align: right;
        }

        .supplier-balance-amount {
            font-size: 0.88rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .supplier-balance-label {
            display: inline-flex;
            margin-top: 0.1rem;
            padding: 0.15rem 0.4rem;
            border-radius: 999px;
            font-size: 0.62rem;
            font-weight: 700;
            letter-spacing: 0.04em;
        }

        .supplier-balance-advance {
            color: #15803d;
            background: #f0fdf4;
        }

        .supplier-balance-due {
            color: #b91c1c;
            background: #fef2f2;
        }

        .supplier-balance-settled {
            color: #475569;
            background: #f1f5f9;
        }

        .supplier-phone {
            margin-top: 0.75rem;
            padding-top: 0.65rem;
            border-top: 1px solid #eef2f7;
            color: #64748b;
            font-size: 0.76rem;
        }

        .supplier-card-actions {
            display: flex;
            justify-content: flex-end;
            gap: 0.5rem;
            margin-top: 0.7rem;
        }

        .supplier-card-action {
            border-radius: 8px;
            font-size: 0.76rem;
            font-weight: 600;
        }

        .supplier-card-action-primary {
            border-color: #a5f3fc;
            color: #0e7490;
            background: #ecfeff;
        }

        .supplier-card-action-secondary {
            border-color: #e2e8f0;
            color: #475569;
            background: #f8fafc;
        }
    }
</style>
<div class="container-fluid">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-3 gap-2">
        <div>
            <h3 class="fw-bold text-dark mb-0">Suppliers</h3>
        </div>
        <div class="d-flex gap-2 justify-content-start justify-content-sm-end">
            <button class="btn btn-sm btn-outline-danger" id="supplierPdfExportBtn" onclick="handlePdfExport(this, '{{ route('suppliers.index', array_merge(request()->all(), ['export' => 'pdf'])) }}')">
                <i class="bi bi-file-earmark-pdf me-1"></i> Export PDF
            </button>
            @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('suppliers.manage'))
            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addPartyModal">
                <i class="bi bi-truck me-1"></i> Add Supplier
            </button>
            @endif
        </div>
    </div>

    <!-- Search Bar -->
    <div class="card supplier-filter-card mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('suppliers.index') }}" class="row g-2 align-items-center">
                <div class="col-12 col-md-5">
                    <div class="input-group supplier-filter-control">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Search by name, khata no or phone..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="{{ request('search') ? 'col-6' : 'col-12' }} col-md-auto">
                    <button type="submit" class="btn supplier-filter-submit w-100"><i class="bi bi-search me-1"></i> Search</button>
                </div>
                @if(request('search'))
                <div class="col-6 col-md-auto">
                    <a href="{{ route('suppliers.index') }}" class="btn supplier-filter-reset w-100"><i class="bi bi-x-lg me-1"></i> Reset</a>
                </div>
                @endif
            </form>
        </div>
    </div>

    <!-- Suppliers Table (Desktop) -->
    <div class="card border-0 shadow-sm d-none d-md-block">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Khata No</th>
                            <th>Supplier Name</th>
                            <th>Mobile Number</th>
                            <th>Current Balance</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($contacts as $contact)
                            <tr>
                                <td class="fw-bold text-info">{{ $contact->khata_number }}</td>
                                <td class="fw-semibold">{{ $contact->name }}</td>
                                <td>{{ $contact->phoneNumbers->first()->phone_number ?? '—' }}</td>
                                <td>
                                    @if($contact->current_balance < 0)
                                        <span class="fw-bold text-success">₹{{ number_format(abs($contact->current_balance), 2) }} (ADVANCE)</span>
                                    @elseif($contact->current_balance > 0)
                                        <span class="fw-bold text-danger">₹{{ number_format($contact->current_balance, 2) }} (DUE)</span>
                                    @else
                                        <span class="fw-bold text-secondary">₹0.00</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('suppliers.show', $contact->id) }}" class="btn btn-sm btn-outline-info me-1" title="View Ledger">
                                        <i class="bi bi-journal-text"></i>
                                    </a>
                                    @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('suppliers.manage'))
                                        <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editPartyModal{{ $contact->id }}" title="Edit Supplier">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No suppliers found.</td>
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

    <!-- Mobile View -->
    <div class="d-md-none">
        @forelse($contacts as $contact)
            <div class="card mobile-card supplier-mobile-card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start gap-3">
                        <div class="min-w-0">
                            <h5 class="supplier-card-name fw-bold mb-0 text-break">{{ $contact->name }}</h5>
                            <div class="supplier-khata">Khata #{{ $contact->khata_number }}</div>
                        </div>
                        <div class="supplier-balance">
                            @if($contact->current_balance < 0)
                                <div class="supplier-balance-amount text-success">₹{{ number_format(abs($contact->current_balance), 2) }}</div>
                                <div class="supplier-balance-label supplier-balance-advance">ADVANCE</div>
                            @elseif($contact->current_balance > 0)
                                <div class="supplier-balance-amount text-danger">₹{{ number_format($contact->current_balance, 2) }}</div>
                                <div class="supplier-balance-label supplier-balance-due">DUE</div>
                            @else
                                <div class="supplier-balance-amount text-secondary">₹0.00</div>
                                <div class="supplier-balance-label supplier-balance-settled">SETTLED</div>
                            @endif
                        </div>
                    </div>
                    <div class="supplier-phone">
                        <i class="bi bi-telephone me-1"></i>
                        {{ $contact->phoneNumbers->first()->phone_number ?? 'N/A' }}
                    </div>
                    <div class="supplier-card-actions">
                        <a href="{{ route('suppliers.show', $contact->id) }}" class="btn btn-sm supplier-card-action supplier-card-action-primary" title="View Ledger">
                            <i class="bi bi-journal-text me-1"></i> View Ledger
                        </a>
                        @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('suppliers.manage'))
                            <button class="btn btn-sm supplier-card-action supplier-card-action-secondary" data-bs-toggle="modal" data-bs-target="#editPartyModal{{ $contact->id }}" title="Edit Supplier">
                                <i class="bi bi-pencil me-1"></i> Edit
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="card border-0 shadow-sm p-4 text-center text-muted">No suppliers found.</div>
        @endforelse

        @if($contacts->hasPages())
            <div class="py-3">
                {{ $contacts->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addPartyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('suppliers.store') }}">
                @csrf
                <input type="hidden" name="type" value="SUPPLIER">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Add Supplier</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @if(auth()->user()->isSuperAdmin())
                        <div class="mb-3">
                            <label class="form-label">Client / Shop *</label>
                            <select name="client_id" class="form-select" required>
                                <option value="">-- Select Client / Shop --</option>
                                @foreach(\App\Models\Client::where('is_active', true)->get() as $client)
                                    <option value="{{ $client->id }}">{{ $client->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div class="mb-3">
                        <label class="form-label">Supplier Name *</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Khata Number *</label>
                        <input type="number" name="khata_number" class="form-control" min="1" step="1" inputmode="numeric" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="phone_number" class="form-control">
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
                    <button type="submit" class="btn btn-primary">Save Supplier</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modals -->
@foreach($contacts as $contact)
<div class="modal fade" id="editPartyModal{{ $contact->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('suppliers.update', $contact->id) }}">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Edit Supplier</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Supplier Name *</label>
                        <input type="text" name="name" class="form-control" value="{{ $contact->name }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Khata Number *</label>
                        <input type="number" name="khata_number" class="form-control" value="{{ $contact->khata_number }}" min="1" step="1" inputmode="numeric" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="phone_number" class="form-control" value="{{ $contact->phoneNumbers->first()->phone_number ?? '' }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control" rows="2">{{ $contact->address }}</textarea>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="editSupplierActive{{ $contact->id }}" {{ $contact->is_active ? 'checked' : '' }}>
                        <label class="form-check-label" for="editSupplierActive{{ $contact->id }}">Active Supplier</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Supplier</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endsection
