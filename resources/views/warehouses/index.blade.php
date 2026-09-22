@extends('layouts.app')

@section('content')
<style>
    .warehouse-filter-card {
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 5px 18px rgba(15, 23, 42, 0.04) !important;
    }
    .warehouse-edit-button { display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; padding: 0; border-color: #c7d2fe; border-radius: 8px; color: #4338ca; background: #eef2ff; }

    @media (max-width: 767.98px) {
        .warehouse-page-heading { font-size: 1.3rem; }
        .warehouse-page-subtitle { font-size: 0.74rem; }
        .warehouse-filter-card .card-body { padding: 0.8rem; }
        .warehouse-mobile-card {
            border: 1px solid #e2e8f0 !important;
            border-left: 1px solid #e2e8f0 !important;
            border-radius: 14px !important;
            background: #ffffff;
            box-shadow: 0 4px 14px rgba(15, 23, 42, 0.05) !important;
        }
        .warehouse-mobile-card:active { transform: none; }
        .warehouse-mobile-card .card-body { padding: 1rem !important; }
        .warehouse-mobile-icon {
            display: inline-flex;
            flex: 0 0 38px;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            border: 1px solid #e0e7ff;
            border-radius: 10px;
            color: #4f46e5;
            background: #f5f3ff;
        }
        .warehouse-mobile-name { color: #0f172a; font-size: 0.94rem; line-height: 1.25; }
        .warehouse-mobile-client { margin-top: 0.18rem; color: #64748b; font-size: 0.72rem; }
        .warehouse-item-count {
            display: inline-flex;
            flex: 0 0 auto;
            padding: 0.22rem 0.5rem;
            border: 1px solid #e2e8f0;
            border-radius: 999px;
            color: #475569;
            background: #f8fafc;
            font-size: 0.68rem;
            font-weight: 700;
        }
        .warehouse-mobile-top-actions { display: flex; flex: 0 0 auto; align-items: flex-start; gap: 0.4rem; }
        .warehouse-edit-button { width: 30px; height: 30px; font-size: 0.74rem; }
    }
</style>
<div class="container-fluid">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 gap-3">
        <div>
            <h3 class="fw-bold text-dark mb-1 warehouse-page-heading">Warehouses</h3>
            <p class="text-muted small mb-0 warehouse-page-subtitle">Manage client warehouses and their inventory locations</p>
        </div>
        @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('warehouses.manage'))
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addWarehouseModal">
                <i class="bi bi-plus-lg me-1"></i> Add Warehouse
            </button>
        @endif
    </div>

    <div class="card border-0 shadow-sm mb-4 warehouse-filter-card">
        <div class="card-body">
            <form method="GET" action="{{ route('warehouses.index') }}" class="row g-2 align-items-center">
                <div class="col-12 col-md-5">
                    <input type="text" name="search" class="form-control" placeholder="Search warehouse{{ auth()->user()->isSuperAdmin() ? ' or client' : '' }}..." value="{{ request('search') }}">
                </div>
                <div class="col-6 col-md-auto">
                    <button class="btn btn-secondary w-100" type="submit"><i class="bi bi-search me-1"></i> Search</button>
                </div>
                @if(request('search'))
                    <div class="col-6 col-md-auto">
                        <a href="{{ route('warehouses.index') }}" class="btn btn-outline-secondary w-100"><i class="bi bi-x-lg me-1"></i> Reset</a>
                    </div>
                @endif
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm d-none d-md-block">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Warehouse</th>
                            @if(auth()->user()->isSuperAdmin())<th>Client / Shop</th>@endif
                            <th>Items</th>
                            <th>Updated</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($warehouses as $warehouse)
                            <tr>
                                <td class="fw-semibold"><i class="bi bi-building text-primary me-2"></i>{{ $warehouse->name }}</td>
                                @if(auth()->user()->isSuperAdmin())<td>{{ $warehouse->client->name }}</td>@endif
                                <td><span class="badge bg-primary-subtle text-primary border border-primary-subtle">{{ $warehouse->items_count }}</span></td>
                                <td class="text-muted small">{{ $warehouse->updated_at->format('d M Y') }}</td>
                                <td class="text-end">
                                    @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('warehouses.manage'))
                                        <button class="btn btn-sm warehouse-edit-button" data-bs-toggle="modal" data-bs-target="#editWarehouseModal{{ $warehouse->id }}" title="Edit warehouse" aria-label="Edit warehouse">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="{{ auth()->user()->isSuperAdmin() ? 5 : 4 }}" class="text-center text-muted py-5">No warehouses found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="d-md-none">
        @forelse($warehouses as $warehouse)
            <div class="card border-0 shadow-sm mb-3 warehouse-mobile-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between gap-3">
                        <div class="d-flex align-items-center gap-2 min-w-0">
                            <span class="warehouse-mobile-icon"><i class="bi bi-building"></i></span>
                            <div class="min-w-0">
                                <div class="fw-bold text-truncate warehouse-mobile-name">{{ $warehouse->name }}</div>
                                @if(auth()->user()->isSuperAdmin())<div class="warehouse-mobile-client text-truncate">{{ $warehouse->client->name }}</div>@endif
                            </div>
                        </div>
                        <div class="warehouse-mobile-top-actions">
                            <span class="warehouse-item-count">{{ $warehouse->items_count }} items</span>
                            @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('warehouses.manage'))
                                <button class="btn btn-sm warehouse-edit-button" data-bs-toggle="modal" data-bs-target="#editWarehouseModal{{ $warehouse->id }}" title="Edit warehouse" aria-label="Edit warehouse"><i class="bi bi-pencil"></i></button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="card border-0 shadow-sm"><div class="card-body text-center text-muted py-5">No warehouses found.</div></div>
        @endforelse
    </div>

    <div class="mt-4">{{ $warehouses->links() }}</div>
</div>

@if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('warehouses.manage'))
    <div class="modal fade warehouse-form-modal" id="addWarehouseModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('warehouses.store') }}">
                    @csrf
                    <div class="modal-header"><h5 class="modal-title fw-bold">Add Warehouse</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                    <div class="modal-body">
                        @if(auth()->user()->isSuperAdmin())
                            <div class="mb-3">
                                <label class="form-label">Client / Shop <span class="text-danger">*</span></label>
                                <select name="client_id" class="form-select" required>
                                    <option value="">Select client</option>
                                    @foreach($clients as $client)<option value="{{ $client->id }}" @selected(old('client_id') == $client->id)>{{ $client->name }}</option>@endforeach
                                </select>
                            </div>
                        @endif
                        <div>
                            <label class="form-label">Warehouse Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" maxlength="150" value="{{ old('name') }}" required>
                        </div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary" type="submit">Save Warehouse</button></div>
                </form>
            </div>
        </div>
    </div>

    @foreach($warehouses as $warehouse)
        <div class="modal fade warehouse-form-modal" id="editWarehouseModal{{ $warehouse->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" action="{{ route('warehouses.update', $warehouse) }}">
                        @csrf @method('PUT')
                        <div class="modal-header"><h5 class="modal-title fw-bold">Edit Warehouse</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                        <div class="modal-body">
                            @if(auth()->user()->isSuperAdmin())
                                <div class="mb-3">
                                    <label class="form-label">Client / Shop <span class="text-danger">*</span></label>
                                    <select name="client_id" class="form-select" required>
                                        @foreach($clients as $client)<option value="{{ $client->id }}" @selected($warehouse->client_id === $client->id)>{{ $client->name }}</option>@endforeach
                                    </select>
                                </div>
                            @endif
                            <div>
                                <label class="form-label">Warehouse Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" maxlength="150" value="{{ $warehouse->name }}" required>
                            </div>
                        </div>
                        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary" type="submit">Update Warehouse</button></div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
@endif
@endsection
