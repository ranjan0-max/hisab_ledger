@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 gap-2">
        <div>
            <h3 class="fw-bold text-dark mb-1">Suppliers</h3>
            <p class="text-muted small mb-0 d-none d-sm-block">Manage supplier accounts and purchase ledgers</p>
        </div>
        @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('suppliers.manage'))
            <button class="btn btn-primary w-100 w-sm-auto" data-bs-toggle="modal" data-bs-target="#addPartyModal">
                <i class="bi bi-truck me-1"></i> Add Supplier
            </button>
        @endif
    </div>

    <!-- Search Bar -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('suppliers.index') }}" class="row g-2">
                <div class="col-12 col-md-5">
                    <input type="text" name="search" class="form-control" placeholder="Search by name, khata no or phone..." value="{{ request('search') }}">
                </div>
                <div class="col-6 col-md-auto">
                    <button type="submit" class="btn btn-secondary w-100"><i class="bi bi-search me-1"></i> Search</button>
                </div>
                @if(request('search'))
                    <div class="col-6 col-md-auto">
                        <a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
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
            <div class="card mobile-card mobile-card-supplier border-0 shadow-sm mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <span class="badge bg-info-subtle text-info border border-info-subtle mb-1">Khata #{{ $contact->khata_number }}</span>
                            <h5 class="fw-bold mb-0">{{ $contact->name }}</h5>
                        </div>
                        <div class="d-flex gap-1">
                            <a href="{{ route('suppliers.show', $contact->id) }}" class="btn btn-sm btn-outline-info" title="View Ledger">
                                <i class="bi bi-journal-text"></i>
                            </a>
                            @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('suppliers.manage'))
                                <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editPartyModal{{ $contact->id }}" title="Edit Supplier">
                                    <i class="bi bi-pencil"></i>
                                </button>
                            @endif
                        </div>
                    </div>
                    <div class="small text-muted mb-1"><i class="bi bi-telephone me-1"></i> {{ $contact->phoneNumbers->first()->phone_number ?? 'N/A' }}</div>
                    <div class="small mb-0">
                        Current Balance: 
                        @if($contact->current_balance < 0)
                            <strong class="text-success">₹{{ number_format(abs($contact->current_balance), 2) }} (ADVANCE)</strong>
                        @elseif($contact->current_balance > 0)
                            <strong class="text-danger">₹{{ number_format($contact->current_balance, 2) }} (DUE)</strong>
                        @else
                            <strong class="text-secondary">₹0.00</strong>
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
                        <input type="text" name="khata_number" class="form-control" required>
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
                        <input type="text" name="khata_number" class="form-control" value="{{ $contact->khata_number }}" required>
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
