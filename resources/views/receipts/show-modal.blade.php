@php
    $totalQuantity = $receipt->items->sum(fn ($line) => (float) $line->quantity);
@endphp

<div class="modal-header">
    <div class="min-w-0">
        <div class="receipt-number">{{ $receipt->number }}</div>
        <h5 class="modal-title fw-bold text-truncate">{{ $receipt->customer_name }}</h5>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">
    <div class="row g-3 mb-4">
        @if(auth()->user()->isSuperAdmin())
            <div class="col-12 col-sm-6"><div class="text-muted small">Client / Shop</div><div class="fw-semibold">{{ $receipt->client->name }}</div></div>
        @endif
        <div class="col-6 col-sm-3"><div class="text-muted small">Created</div><div class="fw-semibold">{{ $receipt->created_at->format('d M Y') }}</div></div>
        <div class="col-6 col-sm-3"><div class="text-muted small">Total Quantity</div><div class="fw-semibold">{{ rtrim(rtrim(number_format($totalQuantity, 3, '.', ''), '0'), '.') }}</div></div>
        <div class="col-12"><div class="text-muted small">Warehouse</div><div class="fw-semibold">{{ $receipt->items->first()?->warehouseItem?->warehouse?->name }}</div></div>
    </div>

    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light"><tr><th>#</th><th>Item</th><th class="text-end">Quantity</th></tr></thead>
            <tbody>@foreach($receipt->items as $line)<tr><td>{{ $loop->iteration }}</td><td class="fw-semibold">{{ $line->warehouseItem->item_name }}</td><td class="text-end fw-semibold">{{ rtrim(rtrim(number_format((float) $line->quantity, 3, '.', ''), '0'), '.') }}</td></tr>@endforeach</tbody>
        </table>
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Close</button>
    <a href="{{ route('receipts.print', $receipt) }}" target="_blank" class="btn btn-primary"><i class="bi bi-printer me-1"></i>Print</a>
</div>
