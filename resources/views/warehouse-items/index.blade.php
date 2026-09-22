@extends('layouts.app')

@section('content')
<style>
    .inventory-filter-card {
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 5px 18px rgba(15, 23, 42, 0.04) !important;
    }
    .inventory-edit-button { display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; padding: 0; border-color: #c7d2fe; border-radius: 8px; color: #4338ca; background: #eef2ff; }

    @media (max-width: 767.98px) {
        .inventory-page-heading { font-size: 1.3rem; }
        .inventory-page-subtitle { font-size: 0.74rem; }
        .inventory-filter-card .card-body { padding: 0.8rem; }
        .inventory-mobile-card {
            border: 1px solid #e2e8f0 !important;
            border-left: 1px solid #e2e8f0 !important;
            border-radius: 14px !important;
            background: #ffffff;
            box-shadow: 0 4px 14px rgba(15, 23, 42, 0.05) !important;
        }
        .inventory-mobile-card:active { transform: none; }
        .inventory-mobile-card .card-body { padding: 1rem !important; }
        .inventory-mobile-icon {
            display: inline-flex;
            flex: 0 0 38px;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            border: 1px solid #dbeafe;
            border-radius: 10px;
            color: #2563eb;
            background: #eff6ff;
        }
        .inventory-mobile-name { color: #0f172a; font-size: 0.94rem; line-height: 1.25; }
        .inventory-mobile-meta { margin-top: 0.18rem; color: #64748b; font-size: 0.71rem; }
        .inventory-quantity {
            display: inline-flex;
            flex: 0 0 auto;
            padding: 0.24rem 0.52rem;
            border: 1px solid #bbf7d0;
            border-radius: 999px;
            color: #15803d;
            background: #f0fdf4;
            font-size: 0.72rem;
            font-weight: 700;
        }
        .inventory-mobile-top-actions { display: flex; flex: 0 0 auto; align-items: flex-start; gap: 0.4rem; }
        .inventory-edit-button { width: 30px; height: 30px; font-size: 0.74rem; }
    }
</style>
<div class="container-fluid">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 gap-3">
        <div>
            <h3 class="fw-bold text-dark mb-1 inventory-page-heading">Warehouse Items</h3>
            <p class="text-muted small mb-0 inventory-page-subtitle">Track item quantities across warehouses</p>
        </div>
        @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('warehouse_items.manage'))
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addItemModal" @disabled($warehouses->isEmpty())>
                <i class="bi bi-plus-lg me-1"></i> Add Item
            </button>
        @endif
    </div>

    @if($warehouses->isEmpty())
        <div class="alert alert-warning border-0 shadow-sm">
            <i class="bi bi-exclamation-triangle me-2"></i>Create a warehouse before adding items.
        </div>
    @endif

    <div class="card border-0 shadow-sm mb-4 inventory-filter-card">
        <div class="card-body">
            <form method="GET" action="{{ route('warehouse-items.index') }}" class="row g-2 align-items-center">
                <div class="col-12 col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search item or warehouse..." value="{{ request('search') }}">
                </div>
                <div class="col-12 col-md-4">
                    <select name="warehouse_id" class="form-select">
                        <option value="">All warehouses</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" @selected((string) request('warehouse_id') === (string) $warehouse->id)>
                                {{ $warehouse->name }}{{ auth()->user()->isSuperAdmin() ? ' — '.$warehouse->client->name : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-auto">
                    <button class="btn btn-secondary w-100" type="submit"><i class="bi bi-search me-1"></i> Filter</button>
                </div>
                @if(request('search') || request('warehouse_id'))
                    <div class="col-6 col-md-auto"><a href="{{ route('warehouse-items.index') }}" class="btn btn-outline-secondary w-100"><i class="bi bi-x-lg me-1"></i> Reset</a></div>
                @endif
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm d-none d-md-block">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light"><tr><th>Item</th><th>Warehouse</th>@if(auth()->user()->isSuperAdmin())<th>Client / Shop</th>@endif<th>Quantity</th><th>Updated</th><th class="text-end">Actions</th></tr></thead>
                    <tbody>
                        @forelse($items as $item)
                            <tr>
                                <td class="fw-semibold"><i class="bi bi-box-seam text-primary me-2"></i>{{ $item->item_name }}</td>
                                <td>{{ $item->warehouse->name }}</td>
                                @if(auth()->user()->isSuperAdmin())<td>{{ $item->warehouse->client->name }}</td>@endif
                                <td><span class="badge bg-success-subtle text-success border border-success-subtle fs-6">{{ rtrim(rtrim(number_format((float) $item->quantity, 3, '.', ''), '0'), '.') }}</span></td>
                                <td class="text-muted small">{{ $item->updated_at->format('d M Y') }}</td>
                                <td class="text-end">
                                    @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('warehouse_items.manage'))
                                        <button class="btn btn-sm inventory-edit-button" data-bs-toggle="modal" data-bs-target="#editItemModal{{ $item->id }}" title="Edit item" aria-label="Edit item"><i class="bi bi-pencil"></i></button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="{{ auth()->user()->isSuperAdmin() ? 6 : 5 }}" class="text-center text-muted py-5">No items found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="d-md-none">
        @forelse($items as $item)
            <div class="card border-0 shadow-sm mb-3 inventory-mobile-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between gap-3">
                        <div class="d-flex align-items-center gap-2 min-w-0">
                            <span class="inventory-mobile-icon"><i class="bi bi-box-seam"></i></span>
                            <div class="min-w-0">
                                <div class="fw-bold text-truncate inventory-mobile-name">{{ $item->item_name }}</div>
                                <div class="inventory-mobile-meta text-truncate"><i class="bi bi-building me-1"></i>{{ $item->warehouse->name }}</div>
                                @if(auth()->user()->isSuperAdmin())<div class="inventory-mobile-meta text-truncate">{{ $item->warehouse->client->name }}</div>@endif
                            </div>
                        </div>
                        <div class="inventory-mobile-top-actions">
                            <span class="inventory-quantity">{{ rtrim(rtrim(number_format((float) $item->quantity, 3, '.', ''), '0'), '.') }}</span>
                            @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('warehouse_items.manage'))
                                <button class="btn btn-sm inventory-edit-button" data-bs-toggle="modal" data-bs-target="#editItemModal{{ $item->id }}" title="Edit item" aria-label="Edit item"><i class="bi bi-pencil"></i></button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="card border-0 shadow-sm"><div class="card-body text-center text-muted py-5">No items found.</div></div>
        @endforelse
    </div>

    <div class="mt-4">{{ $items->links() }}</div>
</div>

@if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('warehouse_items.manage'))
    <div class="modal fade inventory-form-modal" id="addItemModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('warehouse-items.store') }}">
                    @csrf
                    <div class="modal-header"><h5 class="modal-title fw-bold">Add Warehouse Item</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                    <div class="modal-body">
                        <div class="mb-3"><label class="form-label">Warehouse <span class="text-danger">*</span></label><select name="warehouse_id" class="form-select" required><option value="">Select warehouse</option>@foreach($warehouses as $warehouse)<option value="{{ $warehouse->id }}" @selected(old('warehouse_id') == $warehouse->id)>{{ $warehouse->name }}{{ auth()->user()->isSuperAdmin() ? ' — '.$warehouse->client->name : '' }}</option>@endforeach</select></div>
                        <div class="mb-3"><label class="form-label">Item Name <span class="text-danger">*</span></label><input type="text" name="item_name" class="form-control" maxlength="150" value="{{ old('item_name') }}" required></div>
                        <div><label class="form-label">Quantity <span class="text-danger">*</span></label><input type="number" name="quantity" class="form-control" min="0" max="999999999999.999" step="0.001" value="{{ old('quantity', 0) }}" required></div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary" type="submit">Save Item</button></div>
                </form>
            </div>
        </div>
    </div>

    @foreach($items as $item)
        <div class="modal fade inventory-form-modal" id="editItemModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" action="{{ route('warehouse-items.update', $item) }}">
                        @csrf @method('PUT')
                        <div class="modal-header"><h5 class="modal-title fw-bold">Edit Warehouse Item</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                        <div class="modal-body">
                            <div class="mb-3"><label class="form-label">Warehouse <span class="text-danger">*</span></label><select name="warehouse_id" class="form-select" required>@foreach($warehouses as $warehouse)<option value="{{ $warehouse->id }}" @selected($item->warehouse_id === $warehouse->id)>{{ $warehouse->name }}{{ auth()->user()->isSuperAdmin() ? ' — '.$warehouse->client->name : '' }}</option>@endforeach</select></div>
                            <div class="mb-3"><label class="form-label">Item Name <span class="text-danger">*</span></label><input type="text" name="item_name" class="form-control" maxlength="150" value="{{ $item->item_name }}" required></div>
                            <div><label class="form-label">Quantity <span class="text-danger">*</span></label><input type="number" name="quantity" class="form-control" min="0" max="999999999999.999" step="0.001" value="{{ $item->quantity }}" required></div>
                        </div>
                        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary" type="submit">Update Item</button></div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
@endif
@endsection
