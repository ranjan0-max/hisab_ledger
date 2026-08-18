@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-3 gap-2">
        <div>
            <h3 class="fw-bold text-dark mb-0">Customers</h3>
        </div>
        <div class="d-flex gap-2 justify-content-start justify-content-sm-end">
            <button class="btn btn-sm btn-outline-danger" id="customerPdfExportBtn" onclick="handlePdfExport(this, '{{ route('customers.index', array_merge(request()->all(), ['export' => 'pdf'])) }}')">
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
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('customers.index') }}" class="row g-2 align-items-center">
                <div class="col-12 col-md-5">
                    <input type="text" name="search" class="form-control" placeholder="Search by name, khata no or phone..." value="{{ request('search') }}">
                </div>
                <div class="col-12 col-md-4">
                    <select name="inactive_months" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Filter Inactive Customers (All) --</option>
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ request('inactive_months') == $m ? 'selected' : '' }}>
                            Inactive for {{ $m }} {{ $m == 1 ? 'Month' : 'Months' }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="col-6 col-md-auto">
                    <button type="submit" class="btn btn-secondary w-100"><i class="bi bi-filter me-1"></i> Filter</button>
                </div>
                @if(request('search') || request('inactive_months'))
                <div class="col-6 col-md-auto">
                    <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary w-100"><i class="bi bi-x-lg me-1"></i> Reset</a>
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
                                    <span class="fw-bold text-success">₹{{ number_format(abs($contact->current_balance), 2) }} (ADVANCE)</span>
                                    @elseif($contact->current_balance > 0)
                                    <span class="fw-bold text-danger">₹{{ number_format($contact->current_balance, 2) }} (DUE)</span>
                                    @else
                                    <span class="fw-bold text-secondary">₹0.00</span>
                                    @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('customers.show', $contact->id) }}" class="btn btn-sm btn-outline-primary me-1" title="View Ledger">
                                    <i class="bi bi-journal-text"></i>
                                </a>
                                @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('customers.manage'))
                                <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editPartyModal{{ $contact->id }}" title="Edit Customer">
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
        <div class="card mobile-card border-0 shadow-sm mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle mb-1">Khata #{{ $contact->khata_number }}</span>
                        <h5 class="fw-bold mb-0">{{ $contact->name }}</h5>
                    </div>
                    <div class="d-flex gap-1">
                        <a href="{{ route('customers.show', $contact->id) }}" class="btn btn-sm btn-outline-primary" title="View Ledger">
                            <i class="bi bi-journal-text"></i>
                        </a>
                        @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('customers.manage'))
                        <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editPartyModal{{ $contact->id }}" title="Edit Customer">
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
<div class="modal fade" id="addPartyModal" tabindex="-1">
    <div class="modal-dialog">
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
                        <select name="client_id" class="form-select" required>
                            <option value="">-- Select Client / Shop --</option>
                            @foreach(\App\Models\Client::where('is_active', true)->get() as $client)
                            <option value="{{ $client->id }}">{{ $client->name }}</option>
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
                    <button type="submit" class="btn btn-primary">Save Customer</button>
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
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="editCustomerActive{{ $contact->id }}" {{ $contact->is_active ? 'checked' : '' }}>
                        <label class="form-check-label" for="editCustomerActive{{ $contact->id }}">Active Customer</label>
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
@endsection