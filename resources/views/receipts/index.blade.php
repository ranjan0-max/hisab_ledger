@extends('layouts.app')

@section('content')
<style>
    .receipt-filter-card { border: 1px solid #e2e8f0 !important; box-shadow: 0 5px 18px rgba(15, 23, 42, 0.04) !important; }
    .receipt-number { color: #4f46e5; font-size: 0.78rem; font-weight: 700; letter-spacing: 0.02em; }
    .receipt-icon-action { display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; padding: 0; border-radius: 8px !important; }
    @media (max-width: 767.98px) {
        .receipt-page-heading { font-size: 1.3rem; }
        .receipt-page-subtitle { font-size: 0.74rem; }
        .receipt-filter-card .card-body { padding: 0.8rem; }
        .receipt-mobile-card { border: 1px solid #e2e8f0 !important; border-left: 1px solid #e2e8f0 !important; border-radius: 14px !important; background: #fff; box-shadow: 0 4px 14px rgba(15, 23, 42, 0.05) !important; }
        .receipt-mobile-card:active { transform: none; }
        .receipt-mobile-card .card-body { padding: 1rem !important; }
        .receipt-customer { color: #0f172a; font-size: 0.96rem; line-height: 1.3; }
        .receipt-date { color: #94a3b8; font-size: 0.68rem; }
        .receipt-mobile-summary { display: flex; flex-wrap: wrap; align-items: center; gap: 0.45rem; margin-top: 0.75rem; color: #64748b; font-size: 0.72rem; }
        .receipt-mobile-summary span { display: inline-flex; align-items: center; gap: 0.3rem; }
        .receipt-mobile-summary i { color: #818cf8; font-size: 0.76rem; }
        .receipt-summary-separator { width: 3px; height: 3px; border-radius: 50%; background: #cbd5e1; }
        .receipt-summary-value { color: #334155; font-weight: 700; }
        .receipt-mobile-top-actions { display: flex; flex: 0 0 auto; gap: .3rem; }
        .receipt-mobile-top-actions .receipt-icon-action { width: 30px; height: 30px; font-size: .74rem; }
        .receipt-view-action { border-color: #dbeafe; color: #2563eb; background: #eff6ff; }
        .receipt-edit-action { border-color: #c7d2fe; color: #4338ca; background: #eef2ff; }
        .receipt-delete-action { border-color: #fecaca; color: #b91c1c; background: #fef2f2; }
    }
</style>

<div class="container-fluid">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 gap-3">
        <div>
            <h3 class="fw-bold text-dark mb-1 receipt-page-heading">Receipts</h3>
            <p class="text-muted small mb-0 receipt-page-subtitle">Create customer receipts and track issued stock</p>
        </div>
        @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('receipts.manage'))
            <a href="{{ route('receipts.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> New Receipt</a>
        @endif
    </div>

    <div class="card border-0 shadow-sm mb-4 receipt-filter-card">
        <div class="card-body">
            <form method="GET" action="{{ route('receipts.index') }}" class="row g-2 align-items-center">
                <div class="col-12 col-md-5"><input type="text" name="search" class="form-control" placeholder="Search receipt, customer{{ auth()->user()->isSuperAdmin() ? ' or client' : '' }}..." value="{{ request('search') }}"></div>
                <div class="col-6 col-md-auto"><button class="btn btn-secondary w-100" type="submit"><i class="bi bi-search me-1"></i> Search</button></div>
                @if(request('search'))<div class="col-6 col-md-auto"><a href="{{ route('receipts.index') }}" class="btn btn-outline-secondary w-100"><i class="bi bi-x-lg me-1"></i> Reset</a></div>@endif
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm d-none d-md-block">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light"><tr><th>Receipt</th><th>Customer</th>@if(auth()->user()->isSuperAdmin())<th>Client / Shop</th>@endif<th>Items</th><th>Total Qty</th><th>Created</th><th class="text-end">Actions</th></tr></thead>
                    <tbody>
                        @forelse($receipts as $receipt)
                            <tr>
                                <td><span class="receipt-number">{{ $receipt->number }}</span></td>
                                <td class="fw-semibold">{{ $receipt->customer_name }}</td>
                                @if(auth()->user()->isSuperAdmin())<td>{{ $receipt->client->name }}</td>@endif
                                <td>{{ $receipt->items_count }}</td>
                                <td>{{ rtrim(rtrim(number_format((float) ($receipt->items_sum_quantity ?? 0), 3, '.', ''), '0'), '.') }}</td>
                                <td><div class="small">{{ $receipt->created_at->format('d M Y') }}</div><div class="text-muted" style="font-size:.7rem">{{ $receipt->created_at->format('h:i A') }}</div></td>
                                <td class="text-end">
                                    <a href="{{ route('receipts.show', $receipt) }}" class="btn btn-sm btn-outline-secondary receipt-icon-action" title="View receipt" aria-label="View receipt"><i class="bi bi-eye"></i></a>
                                    @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('receipts.manage'))
                                        <a href="{{ route('receipts.edit', $receipt) }}" class="btn btn-sm btn-outline-primary receipt-icon-action" title="Edit receipt" aria-label="Edit receipt"><i class="bi bi-pencil"></i></a>
                                        <form method="POST" action="{{ route('receipts.destroy', $receipt) }}" class="d-inline" onsubmit="return confirm('Delete {{ $receipt->number }}? All issued quantities will be restored to stock.');">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger receipt-icon-action" type="submit" title="Delete receipt" aria-label="Delete receipt"><i class="bi bi-trash"></i></button></form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="{{ auth()->user()->isSuperAdmin() ? 7 : 6 }}" class="text-center text-muted py-5">No receipts found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="d-md-none">
        @forelse($receipts as $receipt)
            <div class="card border-0 shadow-sm mb-3 receipt-mobile-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between gap-3">
                        <div class="min-w-0"><div class="receipt-number">{{ $receipt->number }}</div><div class="fw-bold text-truncate receipt-customer mt-1">{{ $receipt->customer_name }}</div>@if(auth()->user()->isSuperAdmin())<div class="small text-muted text-truncate mt-1">{{ $receipt->client->name }}</div>@endif</div>
                        <div class="receipt-mobile-top-actions">
                            <a href="{{ route('receipts.show', $receipt) }}" class="btn receipt-icon-action receipt-view-action" title="View receipt" aria-label="View receipt"><i class="bi bi-eye"></i></a>
                            @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('receipts.manage'))
                                <a href="{{ route('receipts.edit', $receipt) }}" class="btn receipt-icon-action receipt-edit-action" title="Edit receipt" aria-label="Edit receipt"><i class="bi bi-pencil"></i></a>
                                <form method="POST" action="{{ route('receipts.destroy', $receipt) }}" onsubmit="return confirm('Delete {{ $receipt->number }}? All issued quantities will be restored to stock.');">@csrf @method('DELETE')<button class="btn receipt-icon-action receipt-delete-action" type="submit" title="Delete receipt" aria-label="Delete receipt"><i class="bi bi-trash"></i></button></form>
                            @endif
                        </div>
                    </div>
                    <div class="receipt-mobile-summary">
                        <span><i class="bi bi-calendar3"></i>{{ $receipt->created_at->format('d M Y') }}</span>
                        <span class="receipt-summary-separator" aria-hidden="true"></span>
                        <span><i class="bi bi-list-ul"></i><span class="receipt-summary-value">{{ $receipt->items_count }}</span> {{ $receipt->items_count === 1 ? 'item' : 'items' }}</span>
                        <span class="receipt-summary-separator" aria-hidden="true"></span>
                        <span><i class="bi bi-box-seam"></i>Qty <span class="receipt-summary-value">{{ rtrim(rtrim(number_format((float) ($receipt->items_sum_quantity ?? 0), 3, '.', ''), '0'), '.') }}</span></span>
                    </div>
                </div>
            </div>
        @empty
            <div class="card border-0 shadow-sm"><div class="card-body text-center text-muted py-5">No receipts found.</div></div>
        @endforelse
    </div>

    <div class="mt-4">{{ $receipts->links() }}</div>
</div>
@endsection
