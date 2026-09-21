@extends('layouts.app')

@section('content')
<style>
    @media (max-width: 767.98px) {
        .client-mobile-card {
            border: 1px solid #e2e8f0 !important;
            border-radius: 14px !important;
            background: #ffffff;
            box-shadow: 0 4px 14px rgba(15, 23, 42, 0.05) !important;
        }

        .client-mobile-card .card-body {
            padding: 1rem !important;
        }

        .client-card-name {
            min-width: 0;
            color: #0f172a;
            font-size: 1rem;
            line-height: 1.3;
        }

        .client-status {
            display: inline-flex;
            flex: 0 0 auto;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .client-status::before {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            content: '';
            background: currentColor;
        }

        .client-status-active {
            color: #15803d;
        }

        .client-status-inactive {
            color: #b91c1c;
        }

        .client-details {
            margin-top: 0.8rem;
            padding-top: 0.65rem;
            border-top: 1px solid #eef2f7;
        }

        .client-detail-row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            padding: 0.3rem 0;
            font-size: 0.78rem;
        }

        .client-detail-label {
            flex: 0 0 auto;
            color: #94a3b8;
        }

        .client-detail-value {
            overflow-wrap: anywhere;
            color: #334155;
            font-weight: 600;
            text-align: right;
        }

        .client-card-actions {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 0.5rem;
            margin-top: 0.7rem;
            padding-top: 0.7rem;
            border-top: 1px solid #eef2f7;
        }

        .client-card-action {
            border-color: #dbe3ee;
            border-radius: 8px;
            color: #475569;
            background: #ffffff;
            font-size: 0.76rem;
            font-weight: 600;
        }

        .client-card-paid-action {
            border-color: #bbf7d0;
            color: #15803d;
            background: #f0fdf4;
        }

        .client-form-modal .modal-dialog {
            width: auto;
            height: calc(100vh - 0.75rem) !important;
            height: calc(100dvh - 0.75rem) !important;
            max-height: calc(100vh - 0.75rem) !important;
            max-height: calc(100dvh - 0.75rem) !important;
            min-height: 0 !important;
        }

        .client-form-modal .modal-content,
        .client-form-modal .modal-content > form {
            height: 100% !important;
            max-height: 100% !important;
            min-height: 0 !important;
        }

        .client-form-modal .modal-content {
            overflow: hidden !important;
        }

        .client-form-modal .modal-content > form {
            display: flex !important;
            flex-direction: column;
            overflow: hidden !important;
        }

        .client-form-modal .modal-header,
        .client-form-modal .modal-footer {
            flex: 0 0 auto;
        }

        .client-form-modal .modal-body {
            flex: 1 1 auto;
            height: auto !important;
            max-height: none !important;
            min-height: 0 !important;
            overflow-x: hidden !important;
            overflow-y: auto !important;
            overscroll-behavior: contain;
            touch-action: pan-y;
            -webkit-overflow-scrolling: touch;
            padding: 1rem;
        }

        .client-form-modal .modal-footer {
            flex-wrap: nowrap;
            padding: 0.75rem 1rem;
        }

        .client-form-modal .modal-footer .btn {
            flex: 1 1 0;
        }

        .client-form-modal .menu-rename-heading {
            align-items: stretch !important;
            flex-direction: column;
        }

        .client-form-modal .menu-rename-add {
            width: 100%;
        }
    }

    @media (min-width: 768px) {
        .client-form-modal .modal-dialog {
            max-width: min(1050px, calc(100vw - 3rem));
        }

        .client-form-modal .modal-content {
            max-height: calc(100vh - 3.5rem);
            overflow: hidden;
        }

        .client-form-modal .modal-content > form {
            display: flex;
            flex-direction: column;
            min-height: 0;
            max-height: calc(100vh - 3.5rem);
        }

        .client-form-modal .modal-header,
        .client-form-modal .modal-footer {
            flex: 0 0 auto;
            padding-left: 2rem;
            padding-right: 2rem;
        }

        .client-form-modal .modal-body {
            flex: 1 1 auto;
            min-height: 0;
            overflow-x: hidden;
            overflow-y: auto;
            overscroll-behavior: contain;
            padding: 1.5rem 2rem;
        }
    }
</style>
<div class="container-fluid">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h3 class="fw-bold text-dark mb-1">Clients / Shops</h3>
            <p class="text-muted small mb-0">Manage and keep each business data securely separated</p>
        </div>
        @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('clients.manage'))
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addClientModal">
                <i class="bi bi-plus-lg me-1"></i> Add Client
            </button>
        @endif
    </div>

    <!-- Search & Filter Bar -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('clients.index') }}" class="row g-2">
                <div class="col-12 col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search business, mobile or GST..." value="{{ request('search') }}">
                </div>
                <div class="col-12 col-md-auto">
                    <button type="submit" class="btn btn-secondary w-100"><i class="bi bi-search me-1"></i> Search</button>
                </div>
                @if(request('search'))
                    <div class="col-12 col-md-auto">
                        <a href="{{ route('clients.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
                    </div>
                @endif
            </form>
        </div>
    </div>

    <!-- Desktop View (Table) -->
    <div class="card border-0 shadow-sm d-none d-md-block">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Business Name</th>
                            <th>Mobile</th>
                            <th>GST Number</th>
                            <th>Address</th>
                            <th>Status</th>
                            @if(auth()->user()->isSuperAdmin())
                                <th class="text-center">Session Timeout</th>
                                <th>Next Renewal</th>
                            @endif
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($clients as $client)
                            <tr>
                                <td class="fw-bold">{{ $client->name }}</td>
                                <td>{{ $client->mobile_number ?? '—' }}</td>
                                <td>{{ $client->gst_number ?? '—' }}</td>
                                <td>{{ Str::limit($client->address, 30) ?? '—' }}</td>
                                <td>
                                    @if($client->is_active)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">Active</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Inactive</span>
                                    @endif
                                </td>
                                @if(auth()->user()->isSuperAdmin())
                                    <td class="text-center">
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                            <i class="bi bi-clock me-1"></i>
                                            {{ $client->session_timeout_minutes ?? 120 }} min
                                        </span>
                                    </td>
                                    <td>
                                        @if($client->next_renewal_date)
                                            @php
                                                $renewalIsDue = now('Asia/Kolkata')->toDateString() >= $client->next_renewal_date->toDateString();
                                                $renewalIsNear = !$renewalIsDue
                                                    && now('Asia/Kolkata')->addDays(15)->toDateString() >= $client->next_renewal_date->toDateString();
                                                $renewalCanBePaid = $renewalIsDue || $renewalIsNear;
                                            @endphp
                                            <div class="fw-semibold">{{ $client->next_renewal_date->format('d M Y') }}</div>
                                            @if($renewalIsDue)
                                                <span class="badge bg-danger">PAYMENT DUE</span>
                                            @elseif($renewalIsNear)
                                                <span class="badge bg-warning text-dark">DUE SOON</span>
                                            @else
                                                <span class="badge bg-success-subtle text-success border border-success-subtle">PAID / ACTIVE</span>
                                            @endif
                                        @else
                                            <span class="text-muted">Not configured</span>
                                        @endif
                                    </td>
                                @endif
                                <td class="text-end">
                                    @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('clients.manage'))
                                        <button class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal" data-bs-target="#editClientModal{{ $client->id }}">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                    @endif
                                    @if(auth()->user()->isSuperAdmin() && $client->next_renewal_date && ($renewalCanBePaid ?? false))
                                        <form method="POST" action="{{ route('clients.renewal.mark-paid', $client->id) }}" class="d-inline" onsubmit="return confirm('Mark renewal payment received for {{ $client->name }}?')">
                                            @csrf
                                            <input type="hidden" name="renewal_due_date" value="{{ $client->next_renewal_date->toDateString() }}">
                                            <button type="submit" class="btn btn-sm btn-outline-success" title="Mark renewal payment received">
                                                <i class="bi bi-check2-circle"></i> Mark Paid
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ auth()->user()->isSuperAdmin() ? 8 : 6 }}" class="text-center text-muted py-4">No clients found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($clients->hasPages())
            <div class="card-footer bg-white border-top py-3">
                {{ $clients->links() }}
            </div>
        @endif
    </div>

    <!-- Mobile View (Card List) -->
    <div class="d-md-none">
        @forelse($clients as $client)
            <div class="card client-mobile-card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start gap-3">
                        <h5 class="client-card-name fw-bold mb-0 text-break">{{ $client->name }}</h5>
                        @if($client->is_active)
                            <span class="client-status client-status-active">Active</span>
                        @else
                            <span class="client-status client-status-inactive">Inactive</span>
                        @endif
                    </div>

                    @if(auth()->user()->isSuperAdmin())
                        @php
                            $mobileRenewalCanBePaid = $client->next_renewal_date
                                && now('Asia/Kolkata')->addDays(15)->toDateString() >= $client->next_renewal_date->toDateString();
                        @endphp
                    @endif

                    <div class="client-details">
                        <div class="client-detail-row">
                            <span class="client-detail-label"><i class="bi bi-telephone me-1"></i> Mobile</span>
                            <span class="client-detail-value">{{ $client->mobile_number ?? 'N/A' }}</span>
                        </div>
                        <div class="client-detail-row">
                            <span class="client-detail-label"><i class="bi bi-receipt me-1"></i> GST</span>
                            <span class="client-detail-value">{{ $client->gst_number ?? 'N/A' }}</span>
                        </div>
                        <div class="client-detail-row">
                            <span class="client-detail-label"><i class="bi bi-geo-alt me-1"></i> Address</span>
                            <span class="client-detail-value">{{ $client->address ?? 'N/A' }}</span>
                        </div>
                        @if(auth()->user()->isSuperAdmin())
                            <div class="client-detail-row">
                                <span class="client-detail-label"><i class="bi bi-calendar-event me-1"></i> Renewal</span>
                                <span class="client-detail-value">{{ $client->next_renewal_date?->format('d M Y') ?? 'Not configured' }}</span>
                            </div>
                        @endif
                    </div>

                    @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('clients.manage'))
                        <div class="client-card-actions">
                            <button class="btn btn-sm client-card-action" data-bs-toggle="modal" data-bs-target="#editClientModal{{ $client->id }}">
                                <i class="bi bi-pencil me-1"></i> Edit Client
                            </button>
                            @if(auth()->user()->isSuperAdmin() && $client->next_renewal_date && $mobileRenewalCanBePaid)
                                <form method="POST" action="{{ route('clients.renewal.mark-paid', $client->id) }}" onsubmit="return confirm('Mark renewal payment received for {{ $client->name }}?')">
                                    @csrf
                                    <input type="hidden" name="renewal_due_date" value="{{ $client->next_renewal_date->toDateString() }}">
                                    <button type="submit" class="btn btn-sm client-card-action client-card-paid-action">
                                        <i class="bi bi-check2-circle me-1"></i> Mark Paid
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="card border-0 shadow-sm p-4 text-center text-muted">No clients found.</div>
        @endforelse

        @if($clients->hasPages())
            <div class="mt-3">
                {{ $clients->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Add Client Modal -->
<div class="modal fade client-form-modal" id="addClientModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <form method="POST" action="{{ route('clients.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Add New Client</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-lg-6">
                            <label class="form-label">Business / Shop Name *</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-12 col-lg-6">
                            <label class="form-label">Mobile Number</label>
                            <input type="text" name="mobile_number" class="form-control">
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-lg-6">
                            <label class="form-label">GST Number</label>
                            <input type="text" name="gst_number" class="form-control">
                        </div>
                        <div class="col-12 col-lg-6 d-flex align-items-end">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="addActive" checked>
                                <label class="form-check-label" for="addActive">Active</label>
                            </div>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-12 col-lg-6">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="col-12 col-lg-6">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="3"></textarea>
                        </div>
                    </div>

                    @if(auth()->user()->isSuperAdmin())
                    <div class="row g-3 mt-0">
                        <div class="col-12 col-lg-6">
                            <div class="h-100 p-3 rounded border border-primary-subtle bg-primary-subtle">
                                <label class="form-label fw-bold text-primary-emphasis mb-1">
                                    <i class="bi bi-calendar-event me-1"></i> Annual Renewal Month *
                                </label>
                                <select name="renewal_month" class="form-select" required>
                                    <option value="">-- Select renewal month --</option>
                                    @foreach(range(1, 12) as $month)
                                        <option value="{{ $month }}">{{ \Carbon\Carbon::create()->month($month)->format('F') }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Renewal is due on the 1st of this month every year.</small>
                            </div>
                        </div>
                        <div class="col-12 col-lg-6">
                            <div class="h-100 p-3 rounded border border-warning-subtle bg-warning-subtle">
                                <label class="form-label fw-bold text-warning-emphasis mb-1">
                                    <i class="bi bi-shield-lock me-1"></i> Session Timeout (SuperAdmin Only)
                                </label>
                                <div class="input-group">
                                    <input type="number" name="session_timeout_minutes" class="form-control"
                                           value="120" min="5" max="1440" placeholder="120">
                                    <span class="input-group-text">minutes</span>
                                </div>
                                <small class="text-muted">Min: 5 min &bull; Max: 1440 min (24 hours) &bull; Default: 120 min</small>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Client</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Client Modals -->
@foreach($clients as $client)
<div class="modal fade client-form-modal" id="editClientModal{{ $client->id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <form method="POST" action="{{ route('clients.update', $client->id) }}">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Edit Client</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-lg-6">
                            <label class="form-label">Business / Shop Name *</label>
                            <input type="text" name="name" class="form-control" value="{{ $client->name }}" required>
                        </div>
                        <div class="col-12 col-lg-6">
                            <label class="form-label">Mobile Number</label>
                            <input type="text" name="mobile_number" class="form-control" value="{{ $client->mobile_number }}">
                        </div>
                    </div>
                    <!-- Dynamic Menu Rename Builder -->
                    <div class="mb-3 bg-light p-3 rounded border">
                        <div class="menu-rename-heading d-flex justify-content-between align-items-center gap-2 mb-2">
                            <small class="fw-bold text-primary"><i class="bi bi-ui-checks me-1"></i> Dynamic Menu Custom Names</small>
                            <button type="button" class="menu-rename-add btn btn-sm btn-outline-primary py-1 px-2" onclick="addMenuRenameRow({{ $client->id }})" style="font-size: 12px;">
                                <i class="bi bi-plus-lg me-1"></i> Add Rename Rule
                            </button>
                        </div>
                        <div id="menuRenameContainer{{ $client->id }}">
                            @php
                                $availableMenus = [
                                    'customers' => 'Customers',
                                    'suppliers' => 'Suppliers',
                                    'daily' => 'Daily Entries',
                                    'clients' => 'Clients / Shops',
                                    'users' => 'Users',
                                    'roles' => 'Roles & Permissions',
                                    'audit-logs' => 'Audit Logs',
                                    'slow-queries' => 'Slow Queries',
                                    'database-backups' => 'Database Backups'
                                ];
                                $clientLabels = $client->menu_labels ?? [];
                            @endphp
                            
                            @forelse($clientLabels as $key => $val)
                                <div class="row g-2 mb-2 align-items-center menu-rename-row">
                                    <div class="col-12 col-sm-5">
                                        <select name="menu_keys[]" class="form-select form-select-sm">
                                            @foreach($availableMenus as $mKey => $mName)
                                                <option value="{{ $mKey }}" {{ $key == $mKey ? 'selected' : '' }}>{{ $mName }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-10 col-sm-6">
                                        <input type="text" name="menu_values[]" class="form-control form-control-sm" value="{{ $val }}" placeholder="New Custom Name">
                                    </div>
                                    <div class="col-2 col-sm-1 text-end">
                                        <button type="button" class="btn btn-sm btn-outline-danger w-100 py-1 px-1" onclick="this.closest('.menu-rename-row').remove()">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </div>
                                </div>
                            @empty
                                <div class="text-muted small text-center py-1 no-rules-text">No custom menu names set. Click "+ Add Rename Rule" to add one.</div>
                            @endforelse
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-lg-6">
                            <label class="form-label">GST Number</label>
                            <input type="text" name="gst_number" class="form-control" value="{{ $client->gst_number }}">
                        </div>
                        <div class="col-12 col-lg-6 d-flex align-items-end">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="editActive{{ $client->id }}" {{ $client->is_active ? 'checked' : '' }}>
                                <label class="form-check-label" for="editActive{{ $client->id }}">Active</label>
                            </div>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-12 col-lg-6">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control" rows="3">{{ $client->address }}</textarea>
                        </div>
                        <div class="col-12 col-lg-6">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="3">{{ $client->notes }}</textarea>
                        </div>
                    </div>

                    @if(auth()->user()->isSuperAdmin())
                    <div class="row g-3 mt-0">
                        <div class="col-12 col-lg-6">
                            <div class="h-100 p-3 rounded border border-primary-subtle bg-primary-subtle">
                                <label class="form-label fw-bold text-primary-emphasis mb-1">
                                    <i class="bi bi-calendar-event me-1"></i> Annual Renewal Month *
                                </label>
                                <select name="renewal_month" class="form-select" required>
                                    @foreach(range(1, 12) as $month)
                                        <option value="{{ $month }}" {{ $client->next_renewal_date?->month === $month ? 'selected' : '' }}>
                                            {{ \Carbon\Carbon::create()->month($month)->format('F') }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">
                                    Current due date: {{ $client->next_renewal_date?->format('d M Y') ?? 'Not configured' }}.
                                    Changing the month recalculates the next due date.
                                </small>
                            </div>
                        </div>
                        <div class="col-12 col-lg-6">
                            <div class="h-100 p-3 rounded border border-warning-subtle bg-warning-subtle">
                                <label class="form-label fw-bold text-warning-emphasis mb-1">
                                    <i class="bi bi-shield-lock me-1"></i> Session Timeout (SuperAdmin Only)
                                </label>
                                <div class="input-group">
                                    <input type="number" name="session_timeout_minutes" class="form-control"
                                           value="{{ $client->session_timeout_minutes ?? 120 }}"
                                           min="5" max="1440">
                                    <span class="input-group-text">minutes</span>
                                </div>
                                <small class="text-muted">Min: 5 min &bull; Max: 1440 min (24 hours) &bull; Current: {{ $client->session_timeout_minutes ?? 120 }} min</small>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Client</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

<script>
function addMenuRenameRow(clientId) {
    const container = document.getElementById('menuRenameContainer' + clientId);
    const noRulesText = container.querySelector('.no-rules-text');
    if (noRulesText) {
        noRulesText.remove();
    }
    
    const row = document.createElement('div');
    row.className = 'row g-2 mb-2 align-items-center menu-rename-row';
    row.innerHTML = `
        <div class="col-12 col-sm-5">
            <select name="menu_keys[]" class="form-select form-select-sm">
                <option value="customers">Customers</option>
                <option value="suppliers">Suppliers</option>
                <option value="daily">Daily Entries</option>
                <option value="clients">Clients / Shops</option>
                <option value="users">Users</option>
                <option value="roles">Roles & Permissions</option>
                <option value="audit-logs">Audit Logs</option>
                <option value="slow-queries">Slow Queries</option>
                <option value="database-backups">Database Backups</option>
            </select>
        </div>
        <div class="col-10 col-sm-6">
            <input type="text" name="menu_values[]" class="form-control form-control-sm" placeholder="New Custom Name" required>
        </div>
        <div class="col-2 col-sm-1 text-end">
            <button type="button" class="btn btn-sm btn-outline-danger w-100 py-1 px-1" onclick="this.closest('.menu-rename-row').remove()">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    `;
    container.appendChild(row);
}
</script>
@endsection
