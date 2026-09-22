@extends('layouts.app')

@section('content')
@php
    $totalQuantity = $receipt->items->sum(fn ($line) => (float) $line->quantity);
@endphp
<style>
    .receipt-detail-shell { max-width: 1050px; margin: 0 auto; }
    .receipt-detail-card { border: 1px solid #e2e8f0 !important; box-shadow: 0 7px 22px rgba(15, 23, 42, .05) !important; }
    .receipt-detail-number { color: #4f46e5; font-size: .78rem; font-weight: 700; letter-spacing: .03em; }
    @media (max-width: 767.98px) {
        .receipt-detail-heading { font-size: 1.3rem; }
        .receipt-detail-card .card-body { padding: .9rem !important; }
        .receipt-detail-meta { display: grid; grid-template-columns: 1fr 1fr; gap: .45rem; }
        .receipt-detail-meta-item { min-width: 0; }
        .receipt-detail-meta-label { display: block; color: #94a3b8; font-size: .62rem; font-weight: 700; text-transform: uppercase; }
        .receipt-detail-meta-value { display: block; overflow: hidden; margin-top: .12rem; color: #334155; font-size: .76rem; font-weight: 600; text-overflow: ellipsis; white-space: nowrap; }
        .receipt-detail-line { padding: .65rem 0; border-bottom: 1px solid #f1f5f9; }
        .receipt-detail-line:last-child { border-bottom: 0; }
        .receipt-detail-item-name { color: #0f172a; font-size: .84rem; font-weight: 700; }
        .receipt-detail-warehouse { margin-top: .15rem; color: #64748b; font-size: .68rem; }
        .receipt-detail-quantity { color: #15803d; font-size: .78rem; font-weight: 700; white-space: nowrap; }
    }
</style>

<div class="container-fluid receipt-detail-shell">
    <div class="d-flex align-items-center justify-content-between gap-3 mb-4">
        <div class="d-flex align-items-center gap-3 min-w-0">
            <a href="{{ route('receipts.index') }}" class="btn btn-light border px-3"><i class="bi bi-arrow-left"></i></a>
            <div class="min-w-0"><div class="receipt-detail-number">{{ $receipt->number }}</div><h3 class="fw-bold text-dark mb-0 receipt-detail-heading text-truncate">{{ $receipt->customer_name }}</h3></div>
        </div>
        @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('receipts.manage'))
            <a href="{{ route('receipts.edit', $receipt) }}" class="btn btn-outline-primary"><i class="bi bi-pencil me-1"></i><span class="d-none d-sm-inline">Edit</span></a>
        @endif
    </div>

    @if(auth()->user()->isSuperAdmin())
        <div class="card border-0 receipt-detail-card mb-4">
            <div class="card-body p-4">
                <div class="receipt-detail-meta-item">
                    <span class="receipt-detail-meta-label text-muted small">Client / Shop</span>
                    <span class="receipt-detail-meta-value fw-semibold">{{ $receipt->client->name }}</span>
                </div>
            </div>
        </div>
    @endif

    <div class="card border-0 receipt-detail-card">
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3"><span class="fw-bold">Issued Items</span><span class="small text-muted">Total Qty: <strong class="text-dark">{{ rtrim(rtrim(number_format($totalQuantity, 3, '.', ''), '0'), '.') }}</strong></span></div>
        <div class="card-body p-0 d-none d-md-block">
            <div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead class="table-light"><tr><th>#</th><th>Item</th><th>Warehouse</th><th class="text-end">Quantity</th></tr></thead><tbody>@foreach($receipt->items as $line)<tr><td>{{ $loop->iteration }}</td><td class="fw-semibold">{{ $line->warehouseItem->item_name }}</td><td>{{ $line->warehouseItem->warehouse->name }}</td><td class="text-end fw-bold text-success">{{ rtrim(rtrim(number_format((float) $line->quantity, 3, '.', ''), '0'), '.') }}</td></tr>@endforeach</tbody></table></div>
        </div>
        <div class="card-body py-1 d-md-none">
            @foreach($receipt->items as $line)
                <div class="receipt-detail-line d-flex justify-content-between gap-3"><div class="min-w-0"><div class="receipt-detail-item-name text-truncate">{{ $line->warehouseItem->item_name }}</div><div class="receipt-detail-warehouse text-truncate"><i class="bi bi-building me-1"></i>{{ $line->warehouseItem->warehouse->name }}</div></div><span class="receipt-detail-quantity">{{ rtrim(rtrim(number_format((float) $line->quantity, 3, '.', ''), '0'), '.') }}</span></div>
            @endforeach
        </div>
    </div>
</div>
@endsection
