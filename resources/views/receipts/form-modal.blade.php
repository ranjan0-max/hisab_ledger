@php
    $isEdit = $receipt !== null;
    $initialLines = $lines->map(fn ($line) => [
        'warehouse_item_id' => $line->warehouse_item_id,
        'item_name_display' => $line->warehouseItem->item_name,
        'quantity' => $line->quantity,
    ])->all();
    $formLines = old('items', $initialLines ?: [[
        'warehouse_item_id' => '',
        'item_name_display' => '',
        'quantity' => '',
    ]]);
    $formClientId = old('client_id', $selectedClientId);
    $initialWarehouseIds = $lines->map(fn ($line) => $line->warehouseItem->warehouse_id)->unique();
    $originalWarehouseId = $initialWarehouseIds->count() === 1 ? $initialWarehouseIds->first() : '';
    $formWarehouseId = $isEdit ? $originalWarehouseId : old('warehouse_id', $originalWarehouseId);
    $formWarehouse = $warehouses->firstWhere('id', (int) $formWarehouseId);
@endphp

<style>
    .receipt-modal-lines-head, .receipt-modal-line { display: grid; grid-template-columns: 28px minmax(220px, 1fr) 120px 32px; gap: .75rem; align-items: start; }
    .receipt-modal-lines-head { padding: .35rem .25rem .5rem; color: #94a3b8; border-bottom: 1px solid #e2e8f0; font-size: .65rem; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; }
    .receipt-modal-line { padding: .75rem .25rem; border-bottom: 1px solid #eef2f7; }
    .receipt-modal-line:last-child { border-bottom: 0; }
    .receipt-modal-line-number { display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 34px; color: #94a3b8; font-size: .72rem; font-weight: 600; }
    .receipt-modal-item-wrap { position: relative; }
    .receipt-modal-results { position: absolute; z-index: 1080; top: calc(100% + 4px); left: 0; right: 0; overflow-y: auto; max-height: 220px; border: 1px solid #dbe3ee; border-radius: 9px; background: #fff; box-shadow: 0 12px 30px rgba(15, 23, 42, .14); }
    .receipt-modal-result { display: flex; align-items: center; justify-content: space-between; gap: 1rem; width: 100%; padding: .65rem .75rem; border: 0; border-bottom: 1px solid #f1f5f9; color: #334155; background: #fff; text-align: left; }
    .receipt-modal-result:last-child { border-bottom: 0; }
    .receipt-modal-result:hover { background: #f8fafc; }
    .receipt-modal-result-stock { color: #15803d; font-size: .7rem; font-weight: 700; white-space: nowrap; }
    .receipt-modal-remove { display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 34px; padding: 0; border: 0; border-radius: 6px; color: #94a3b8; background: transparent; }
    .receipt-modal-remove:hover { color: #dc2626; background: #fef2f2; }
    .receipt-modal-add { padding: .35rem .25rem; border: 0; color: #4f46e5; background: transparent; font-weight: 600; }
    .receipt-modal-line-top .receipt-modal-remove { display: none; }
    .receipt-modal-field label { display: none; }
    @media (max-width: 767.98px) {
        .receipt-modal-lines-head { grid-template-columns: minmax(0, 1fr) 88px 32px; gap: .4rem; padding: .3rem 0; }
        .receipt-modal-lines-head > div:first-child { display: none; }
        .receipt-modal-line { grid-template-columns: minmax(0, 1fr) 88px 32px; gap: .4rem; padding: .55rem 0; }
        .receipt-modal-line-top { display: none; }
        .receipt-modal-item-field { grid-column: 1; grid-row: 1; }
        .receipt-modal-quantity-field { grid-column: 2; grid-row: 1; }
        .receipt-modal-remove-cell { display: block; grid-column: 3; grid-row: 1; }
        .receipt-modal-field .form-control { min-height: 34px; padding-top: .3rem; padding-bottom: .3rem; font-size: .78rem; }
    }
    @media (min-width: 768px) {
        .receipt-modal-line-top { display: contents; }
    }
</style>

<div class="modal-header">
    <div>
        <h5 class="modal-title fw-bold">{{ $isEdit ? 'Edit '.$receipt->number : 'New Receipt' }}</h5>
        @if($isEdit)<div class="text-muted small">{{ $receipt->customer_name }}</div>@endif
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<form method="POST" action="{{ $isEdit ? route('receipts.update', $receipt) : route('receipts.store') }}" class="receipt-modal-form receipt-ajax-form" id="receiptModalForm">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div class="modal-body">
        <div class="alert alert-danger py-2 {{ $errors->any() ? '' : 'd-none' }}" id="receiptModalErrors" role="alert">
            <ul class="mb-0 ps-3 small">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>

        <div class="row g-3 mb-4">
            @if(auth()->user()->isSuperAdmin() && !$isEdit)
                <div class="col-12 col-md-6"><label class="form-label fw-semibold">Client / Shop <span class="text-danger">*</span></label><select name="client_id" id="receiptModalClient" class="form-select" required><option value="">Select client</option>@foreach($clients as $client)<option value="{{ $client->id }}" @selected((string) $formClientId === (string) $client->id)>{{ $client->name }}</option>@endforeach</select></div>
            @elseif(auth()->user()->isSuperAdmin())
                <div class="col-12 col-md-6"><label class="form-label fw-semibold">Client / Shop</label><input class="form-control bg-light" value="{{ $receipt->client->name }}" readonly></div>
            @endif
            <div class="col-12 col-md-6"><label class="form-label fw-semibold">Customer Name <span class="text-danger">*</span></label><input type="text" name="customer_name" class="form-control" maxlength="150" value="{{ old('customer_name', $receipt?->customer_name) }}" placeholder="Enter customer name" required></div>
            <div class="w-100"></div>
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold">Warehouse <span class="text-danger">*</span></label>
                @if($isEdit)
                    <input type="hidden" name="warehouse_id" id="receiptModalWarehouse" value="{{ $formWarehouseId }}">
                    <input type="text" class="form-control bg-light" value="{{ $formWarehouse?->name }}" readonly>
                @else
                    <select name="warehouse_id" id="receiptModalWarehouse" class="form-select" required><option value="">Select warehouse</option>@foreach($warehouses as $warehouse)<option value="{{ $warehouse->id }}" data-client-id="{{ $warehouse->client_id }}" @selected((string) $formWarehouseId === (string) $warehouse->id)>{{ $warehouse->name }}{{ auth()->user()->isSuperAdmin() ? ' — '.$warehouse->client->name : '' }}</option>@endforeach</select>
                @endif
            </div>
        </div>

        <h6 class="fw-semibold text-dark mb-2">Items</h6>
        <div class="receipt-modal-lines-head"><div>#</div><div>Item</div><div>Quantity</div><div></div></div>
        <div id="receiptModalLines">
            @foreach($formLines as $index => $line)
                <div class="receipt-modal-line">
                    <div class="receipt-modal-line-top"><span class="receipt-modal-line-number">{{ $index + 1 }}</span></div>
                    <div class="receipt-modal-field receipt-modal-item-wrap receipt-modal-item-field"><label>Item</label><input type="hidden" name="items[{{ $index }}][warehouse_item_id]" class="receipt-modal-item-id" value="{{ $line['warehouse_item_id'] ?? '' }}"><input type="hidden" name="items[{{ $index }}][item_name_display]" class="receipt-modal-item-name" value="{{ $line['item_name_display'] ?? '' }}"><input type="text" class="form-control form-control-sm receipt-modal-search" value="{{ $line['item_name_display'] ?? '' }}" placeholder="Search item..." autocomplete="off" required><div class="receipt-modal-results d-none"></div></div>
                    <div class="receipt-modal-field receipt-modal-quantity-field"><label>Quantity</label><input type="number" name="items[{{ $index }}][quantity]" class="form-control form-control-sm receipt-modal-quantity" min="0.001" max="999999999999.999" step="0.001" value="{{ $line['quantity'] ?? '' }}" placeholder="0.000" required></div>
                    <div class="receipt-modal-remove-cell"><button type="button" class="receipt-modal-remove" title="Remove row"><i class="bi bi-trash"></i></button></div>
                </div>
            @endforeach
        </div>
        <button type="button" class="btn btn-sm receipt-modal-add mt-1" id="addReceiptModalLine"><i class="bi bi-plus-lg me-1"></i>Add item</button>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>{{ $isEdit ? 'Update Receipt' : 'Save Receipt' }}</button>
    </div>
</form>

<template id="receiptModalLineTemplate">
    <div class="receipt-modal-line">
        <div class="receipt-modal-line-top"><span class="receipt-modal-line-number">__NUMBER__</span></div>
        <div class="receipt-modal-field receipt-modal-item-wrap receipt-modal-item-field"><label>Item</label><input type="hidden" name="items[__INDEX__][warehouse_item_id]" class="receipt-modal-item-id"><input type="hidden" name="items[__INDEX__][item_name_display]" class="receipt-modal-item-name"><input type="text" class="form-control form-control-sm receipt-modal-search" placeholder="Search item..." autocomplete="off" required><div class="receipt-modal-results d-none"></div></div>
        <div class="receipt-modal-field receipt-modal-quantity-field"><label>Quantity</label><input type="number" name="items[__INDEX__][quantity]" class="form-control form-control-sm receipt-modal-quantity" min="0.001" max="999999999999.999" step="0.001" placeholder="0.000" required></div>
        <div class="receipt-modal-remove-cell"><button type="button" class="receipt-modal-remove" title="Remove row"><i class="bi bi-trash"></i></button></div>
    </div>
</template>

<script>
(() => {
    const form = document.getElementById('receiptModalForm');
    const lines = document.getElementById('receiptModalLines');
    const template = document.getElementById('receiptModalLineTemplate');
    const client = document.getElementById('receiptModalClient');
    const warehouse = document.getElementById('receiptModalWarehouse');
    const searchUrl = @json(route('receipts.items.search'));
    let searchTimer;

    if (!form || form.dataset.initialized) return;
    form.dataset.initialized = 'true';

    const resetItem = row => {
        row.querySelector('.receipt-modal-item-id').value = '';
        row.querySelector('.receipt-modal-item-name').value = '';
        row.querySelector('.receipt-modal-search').value = '';
        row.querySelector('.receipt-modal-results').classList.add('d-none');
    };
    const resetAllItems = () => lines.querySelectorAll('.receipt-modal-line').forEach(resetItem);
    const renumber = () => lines.querySelectorAll('.receipt-modal-line').forEach((row, index) => {
        row.querySelector('.receipt-modal-line-number').textContent = index + 1;
        row.querySelectorAll('[name]').forEach(field => field.name = field.name.replace(/items\[\d+\]/, `items[${index}]`));
    });
    const filterWarehouses = () => {
        if (!client || !warehouse.options) return;
        let valid = !warehouse.value;
        Array.from(warehouse.options).forEach(option => {
            if (!option.value) return;
            const visible = client.value !== '' && option.dataset.clientId === client.value;
            option.hidden = !visible;
            option.disabled = !visible;
            if (option.selected && visible) valid = true;
        });
        if (!valid) warehouse.value = '';
    };
    const escapeHtml = value => {
        const div = document.createElement('div');
        div.textContent = String(value);
        return div.innerHTML;
    };
    const showResults = (row, items) => {
        const results = row.querySelector('.receipt-modal-results');
        results.innerHTML = items.length ? '' : '<div class="px-3 py-2 text-muted small">No in-stock items found.</div>';
        items.forEach(item => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'receipt-modal-result';
            button.innerHTML = `<span>${escapeHtml(item.text)}</span><span class="receipt-modal-result-stock">${escapeHtml(item.quantity)} available</span>`;
            button.addEventListener('click', () => {
                row.querySelector('.receipt-modal-item-id').value = item.id;
                row.querySelector('.receipt-modal-item-name').value = item.text;
                row.querySelector('.receipt-modal-search').value = item.text;
                results.classList.add('d-none');
            });
            results.appendChild(button);
        });
        results.classList.remove('d-none');
    };
    const searchItems = async row => {
        if (!warehouse.value) {
            resetItem(row);
            return;
        }
        const url = new URL(searchUrl, window.location.origin);
        url.searchParams.set('warehouse_id', warehouse.value);
        url.searchParams.set('search', row.querySelector('.receipt-modal-search').value.trim());
        const response = await fetch(url, { headers: { Accept: 'application/json' } });
        showResults(row, response.ok ? await response.json() : []);
    };

    document.getElementById('addReceiptModalLine').addEventListener('click', () => {
        renumber();
        const index = lines.querySelectorAll('.receipt-modal-line').length;
        lines.insertAdjacentHTML('beforeend', template.innerHTML.replaceAll('__INDEX__', index).replaceAll('__NUMBER__', index + 1));
        lines.lastElementChild.querySelector('.receipt-modal-search').focus();
    });
    lines.addEventListener('input', event => {
        if (!event.target.classList.contains('receipt-modal-search')) return;
        const row = event.target.closest('.receipt-modal-line');
        row.querySelector('.receipt-modal-item-id').value = '';
        row.querySelector('.receipt-modal-item-name').value = event.target.value;
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => searchItems(row), 250);
    });
    lines.addEventListener('focusin', event => {
        if (event.target.classList.contains('receipt-modal-search')) searchItems(event.target.closest('.receipt-modal-line'));
    });
    lines.addEventListener('click', event => {
        const remove = event.target.closest('.receipt-modal-remove');
        if (!remove) return;
        const row = remove.closest('.receipt-modal-line');
        if (lines.querySelectorAll('.receipt-modal-line').length === 1) {
            row.querySelector('.receipt-modal-quantity').value = '';
            resetItem(row);
        } else {
            row.remove();
            renumber();
        }
    });
    warehouse.addEventListener('change', resetAllItems);
    if (client) client.addEventListener('change', () => { filterWarehouses(); resetAllItems(); });
    filterWarehouses();
})();
</script>
