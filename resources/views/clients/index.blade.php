@extends('layouts.app')

@section('content')
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
                                <td class="text-end">
                                    @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('clients.manage'))
                                        <button class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal" data-bs-target="#editClientModal{{ $client->id }}">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No clients found.</td>
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
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h5 class="fw-bold mb-0">{{ $client->name }}</h5>
                        @if($client->is_active)
                            <span class="badge bg-success-subtle text-success border border-success-subtle">Active</span>
                        @else
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Inactive</span>
                        @endif
                    </div>
                    <div class="small text-muted mb-1"><i class="bi bi-telephone me-1"></i> {{ $client->mobile_number ?? 'N/A' }}</div>
                    <div class="small text-muted mb-1"><i class="bi bi-receipt me-1"></i> GST: {{ $client->gst_number ?? 'N/A' }}</div>
                    <div class="small text-muted mb-3"><i class="bi bi-geo-alt me-1"></i> {{ $client->address ?? 'N/A' }}</div>
                    
                    @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('clients.manage'))
                        <div class="border-top pt-2 text-end">
                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editClientModal{{ $client->id }}">
                                <i class="bi bi-pencil me-1"></i> Edit Client
                            </button>
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
<div class="modal fade" id="addClientModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('clients.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Add New Client</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Business / Shop Name *</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="row g-2 mb-3 bg-light p-2 rounded border">
                        <div class="col-12"><small class="fw-bold text-primary">Custom Dynamic Menu Names (Optional)</small></div>
                        <div class="col-4">
                            <label class="form-label small mb-1">Customer Label</label>
                            <input type="text" name="menu_labels[customers]" class="form-control form-control-sm" placeholder="Customers">
                        </div>
                        <div class="col-4">
                            <label class="form-label small mb-1">Supplier Label</label>
                            <input type="text" name="menu_labels[suppliers]" class="form-control form-control-sm" placeholder="Suppliers">
                        </div>
                        <div class="col-4">
                            <label class="form-label small mb-1">Daily Label</label>
                            <input type="text" name="menu_labels[daily]" class="form-control form-control-sm" placeholder="Daily Entries">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mobile Number</label>
                        <input type="text" name="mobile_number" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">GST Number</label>
                        <input type="text" name="gst_number" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="addActive" checked>
                        <label class="form-check-label" for="addActive">Active</label>
                    </div>
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
<div class="modal fade" id="editClientModal{{ $client->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('clients.update', $client->id) }}">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Edit Client</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Business / Shop Name *</label>
                        <input type="text" name="name" class="form-control" value="{{ $client->name }}" required>
                    </div>
                    <!-- Dynamic Menu Rename Builder -->
                    <div class="mb-3 bg-light p-3 rounded border">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <small class="fw-bold text-primary"><i class="bi bi-ui-checks me-1"></i> Dynamic Menu Custom Names</small>
                            <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2" onclick="addMenuRenameRow({{ $client->id }})" style="font-size: 12px;">
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
                                    <div class="col-5">
                                        <select name="menu_keys[]" class="form-select form-select-sm">
                                            @foreach($availableMenus as $mKey => $mName)
                                                <option value="{{ $mKey }}" {{ $key == $mKey ? 'selected' : '' }}>{{ $mName }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <input type="text" name="menu_values[]" class="form-control form-control-sm" value="{{ $val }}" placeholder="New Custom Name">
                                    </div>
                                    <div class="col-1 text-end">
                                        <button type="button" class="btn btn-sm btn-outline-danger py-0 px-1" onclick="this.closest('.menu-rename-row').remove()">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </div>
                                </div>
                            @empty
                                <div class="text-muted small text-center py-1 no-rules-text">No custom menu names set. Click "+ Add Rename Rule" to add one.</div>
                            @endforelse
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mobile Number</label>
                        <input type="text" name="mobile_number" class="form-control" value="{{ $client->mobile_number }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">GST Number</label>
                        <input type="text" name="gst_number" class="form-control" value="{{ $client->gst_number }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control" rows="2">{{ $client->address }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2">{{ $client->notes }}</textarea>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="editActive{{ $client->id }}" {{ $client->is_active ? 'checked' : '' }}>
                        <label class="form-check-label" for="editActive{{ $client->id }}">Active</label>
                    </div>
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
        <div class="col-5">
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
        <div class="col-6">
            <input type="text" name="menu_values[]" class="form-control form-control-sm" placeholder="New Custom Name" required>
        </div>
        <div class="col-1 text-end">
            <button type="button" class="btn btn-sm btn-outline-danger py-0 px-1" onclick="this.closest('.menu-rename-row').remove()">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    `;
    container.appendChild(row);
}
</script>
@endsection
