@extends('layouts.app')

@section('content')
@php
    $isEdit = $receipt !== null;
    $initialLines = $lines->map(fn ($line) => [
        'warehouse_id' => $line->warehouseItem->warehouse_id,
        'warehouse_item_id' => $line->warehouse_item_id,
        'item_name_display' => $line->warehouseItem->item_name,
        'quantity' => $line->quantity,
        'available_quantity' => $line->warehouseItem->quantity,
    ])->all();
    $formLines = old('items', $initialLines ?: [[
        'warehouse_id' => '',
        'warehouse_item_id' => '',
        'item_name_display' => '',
        'quantity' => '',
        'available_quantity' => '',
    ]]);
    $formClientId = old('client_id', $selectedClientId);
@endphp

<style>
    .receipt-form-shell { max-width: 1180px; margin: 0 auto; }
    .receipt-form-card { border: 1px solid #e2e8f0 !important; box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05) !important; }
    .receipt-lines-head, .receipt-line { display: grid; grid-template-columns: 32px minmax(160px, .75fr) minmax(220px, 1.25fr) 115px 34px; gap: .5rem; align-items: start; }
    .receipt-lines-head { padding: .52rem .7rem; color: #64748b; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 9px; font-size: .66rem; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; }
    .receipt-line { margin-top: .45rem; padding: .55rem .7rem; border: 1px solid #e2e8f0; border-radius: 9px; background: #fff; }
    .receipt-line-number { display: inline-flex; align-items: center; justify-content: center; width: 26px; height: 26px; border-radius: 7px; color: #4f46e5; background: #eef2ff; font-size: .7rem; font-weight: 700; }
    .item-search-wrap { position: relative; }
    .item-results { position: absolute; z-index: 1080; top: calc(100% + 4px); left: 0; right: 0; overflow-y: auto; max-height: 230px; border: 1px solid #dbe3ee; border-radius: 10px; background: #fff; box-shadow: 0 12px 30px rgba(15, 23, 42, .14); }
    .item-result { display: flex; align-items: center; justify-content: space-between; gap: 1rem; width: 100%; padding: .65rem .75rem; border: 0; border-bottom: 1px solid #f1f5f9; color: #334155; background: #fff; text-align: left; }
    .item-result:last-child { border-bottom: 0; }
    .item-result:hover { background: #f8fafc; }
    .item-result-stock { color: #15803d; font-size: .7rem; font-weight: 700; white-space: nowrap; }
    .stock-hint { min-height: 14px; margin-top: .15rem; color: #64748b; font-size: .63rem; line-height: 1.2; }
    .remove-line { display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; padding: 0; border-color: #fecaca; color: #b91c1c; background: #fef2f2; }
    .receipt-add-row-bar { display: flex; justify-content: center; margin-top: .7rem; }
    .receipt-add-row-button { width: min(280px, 100%); border-style: dashed; border-color: #a5b4fc; color: #4338ca; background: #f8faff; }
    .receipt-form-actions { position: sticky; bottom: 0; z-index: 10; margin: 1rem -1.25rem -1.25rem; padding: 1rem 1.25rem; border-top: 1px solid #e2e8f0; background: rgba(255, 255, 255, .96); backdrop-filter: blur(10px); }
    @media (max-width: 767.98px) {
        .receipt-form-heading { font-size: 1.3rem; }
        .receipt-form-subtitle { font-size: .74rem; }
        .receipt-form-card .card-body { padding: .9rem !important; }
        .receipt-lines-head { display: none; }
        .receipt-line { display: grid; grid-template-columns: minmax(0, 1fr) 96px; gap: .5rem; margin-top: .55rem; padding: .65rem; border-radius: 11px; box-shadow: 0 3px 10px rgba(15, 23, 42, .035); }
        .receipt-line-top { display: flex; grid-column: 1 / -1; grid-row: 1; align-items: center; justify-content: space-between; margin-bottom: 0; }
        .receipt-line-field { min-width: 0; margin-bottom: 0; }
        .receipt-line-field label { display: block !important; margin-bottom: .2rem; color: #64748b; font-size: .62rem; font-weight: 700; line-height: 1.1; }
        .receipt-line-field .form-control,
        .receipt-line-field .form-select { min-height: 34px; padding-top: .3rem; padding-bottom: .3rem; font-size: .78rem; }
        .warehouse-field { grid-column: 1; grid-row: 2; }
        .quantity-field { grid-column: 2; grid-row: 2; }
        .item-field { grid-column: 1 / -1; grid-row: 3; }
        .receipt-line .remove-cell { display: none; }
        .receipt-line-top .remove-line { display: inline-flex !important; }
        .receipt-add-row-bar { margin-top: .55rem; }
        .receipt-add-row-button { font-size: .76rem; }
        .receipt-form-actions { margin: 1rem -.9rem -.9rem; padding: .8rem .9rem; }
        .receipt-form-actions .btn { flex: 1 1 0; }
    }
    @media (min-width: 768px) {
        .receipt-line-top { display: contents; }
        .receipt-line-field label { display: none; }
        .receipt-line-top .remove-line { display: none; }
    }
</style>

<div class="container-fluid receipt-form-shell">
    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="{{ route('receipts.index') }}" class="btn btn-light border px-3"><i class="bi bi-arrow-left"></i></a>
        <div><h3 class="fw-bold text-dark mb-1 receipt-form-heading">{{ $isEdit ? 'Edit '.$receipt->number : 'New Receipt' }}</h3><p class="text-muted small mb-0 receipt-form-subtitle">{{ $isEdit ? 'Update lines and automatically reconcile stock' : 'Issue warehouse items to a customer' }}</p></div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm" role="alert">
            <div class="fw-semibold mb-1"><i class="bi bi-exclamation-circle me-1"></i>Please fix the following:</div>
            <ul class="mb-0 ps-3 small">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ $isEdit ? route('receipts.update', $receipt) : route('receipts.store') }}" id="receiptForm">
        @csrf
        @if($isEdit) @method('PUT') @endif

        <div class="card border-0 receipt-form-card">
            <div class="card-body p-4">
                <div class="row g-3 mb-4">
                    @if(auth()->user()->isSuperAdmin() && !$isEdit)
                        <div class="col-12 col-md-6"><label class="form-label fw-semibold">Client / Shop <span class="text-danger">*</span></label><select name="client_id" id="receiptClient" class="form-select" required><option value="">Select client</option>@foreach($clients as $client)<option value="{{ $client->id }}" @selected((string) $formClientId === (string) $client->id)>{{ $client->name }}</option>@endforeach</select></div>
                    @elseif(auth()->user()->isSuperAdmin())
                        <div class="col-12 col-md-6"><label class="form-label fw-semibold">Client / Shop</label><input class="form-control bg-light" value="{{ $receipt->client->name }}" readonly></div>
                    @endif
                    <div class="col-12 col-md-6"><label class="form-label fw-semibold">Customer Name <span class="text-danger">*</span></label><input type="text" name="customer_name" class="form-control" maxlength="150" value="{{ old('customer_name', $receipt?->customer_name) }}" placeholder="Enter customer name" required></div>
                </div>

                <div class="mb-2"><h6 class="fw-bold text-dark mb-1">Receipt Items</h6><p class="text-muted mb-0" style="font-size:.72rem">Select a warehouse, search its item, then enter quantity.</p></div>

                <div class="receipt-lines-head"><div>#</div><div>Warehouse</div><div>Item</div><div>Quantity</div><div></div></div>
                <div id="receiptLines">
                    @foreach($formLines as $index => $line)
                        <div class="receipt-line">
                            <div class="receipt-line-top"><span class="receipt-line-number">{{ $index + 1 }}</span><button type="button" class="btn btn-sm remove-line" title="Remove row"><i class="bi bi-trash"></i></button></div>
                            <div class="receipt-line-field warehouse-field"><label>Warehouse</label><select name="items[{{ $index }}][warehouse_id]" class="form-select form-select-sm warehouse-select" required><option value="">Select warehouse</option>@foreach($warehouses as $warehouse)<option value="{{ $warehouse->id }}" data-client-id="{{ $warehouse->client_id }}" @selected((string) ($line['warehouse_id'] ?? '') === (string) $warehouse->id)>{{ $warehouse->name }}{{ auth()->user()->isSuperAdmin() && !$isEdit ? ' — '.$warehouse->client->name : '' }}</option>@endforeach</select></div>
                            <div class="receipt-line-field item-search-wrap item-field"><label>Item</label><input type="hidden" name="items[{{ $index }}][warehouse_item_id]" class="warehouse-item-id" value="{{ $line['warehouse_item_id'] ?? '' }}"><input type="hidden" name="items[{{ $index }}][item_name_display]" class="item-name-display" value="{{ $line['item_name_display'] ?? '' }}"><input type="text" class="form-control form-control-sm item-search" value="{{ $line['item_name_display'] ?? '' }}" placeholder="Search item..." autocomplete="off" required><div class="item-results d-none"></div><div class="stock-hint">@if(($line['available_quantity'] ?? '') !== '')In stock now: {{ $line['available_quantity'] }}@endif</div></div>
                            <div class="receipt-line-field quantity-field"><label>Quantity</label><input type="number" name="items[{{ $index }}][quantity]" class="form-control form-control-sm quantity-input" min="0.001" max="999999999999.999" step="0.001" value="{{ $line['quantity'] ?? '' }}" placeholder="0.000" required></div>
                            <div class="remove-cell"><button type="button" class="btn btn-sm remove-line" title="Remove row"><i class="bi bi-trash"></i></button></div>
                        </div>
                    @endforeach
                </div>

                <div class="receipt-add-row-bar"><button type="button" class="btn btn-sm receipt-add-row-button" id="addReceiptLine"><i class="bi bi-plus-lg me-1"></i>Add Another Row</button></div>

                <div class="receipt-form-actions d-flex justify-content-end gap-2"><a href="{{ route('receipts.index') }}" class="btn btn-light border">Cancel</a><button type="submit" class="btn btn-primary" id="saveReceipt"><i class="bi bi-check-lg me-1"></i>{{ $isEdit ? 'Update Receipt' : 'Save Receipt' }}</button></div>
            </div>
        </div>
    </form>
</div>

<template id="receiptLineTemplate">
    <div class="receipt-line">
        <div class="receipt-line-top"><span class="receipt-line-number">__NUMBER__</span><button type="button" class="btn btn-sm remove-line" title="Remove row"><i class="bi bi-trash"></i></button></div>
        <div class="receipt-line-field warehouse-field"><label>Warehouse</label><select name="items[__INDEX__][warehouse_id]" class="form-select form-select-sm warehouse-select" required><option value="">Select warehouse</option>@foreach($warehouses as $warehouse)<option value="{{ $warehouse->id }}" data-client-id="{{ $warehouse->client_id }}">{{ $warehouse->name }}{{ auth()->user()->isSuperAdmin() && !$isEdit ? ' — '.$warehouse->client->name : '' }}</option>@endforeach</select></div>
        <div class="receipt-line-field item-search-wrap item-field"><label>Item</label><input type="hidden" name="items[__INDEX__][warehouse_item_id]" class="warehouse-item-id"><input type="hidden" name="items[__INDEX__][item_name_display]" class="item-name-display"><input type="text" class="form-control form-control-sm item-search" placeholder="Search item..." autocomplete="off" required><div class="item-results d-none"></div><div class="stock-hint"></div></div>
        <div class="receipt-line-field quantity-field"><label>Quantity</label><input type="number" name="items[__INDEX__][quantity]" class="form-control form-control-sm quantity-input" min="0.001" max="999999999999.999" step="0.001" placeholder="0.000" required></div>
        <div class="remove-cell"><button type="button" class="btn btn-sm remove-line" title="Remove row"><i class="bi bi-trash"></i></button></div>
    </div>
</template>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const linesContainer = document.getElementById('receiptLines');
    const template = document.getElementById('receiptLineTemplate');
    const clientSelect = document.getElementById('receiptClient');
    const itemSearchUrl = @json(route('receipts.items.search'));
    let nextIndex = linesContainer.querySelectorAll('.receipt-line').length;
    let searchTimer;

    const resetItem = row => {
        row.querySelector('.warehouse-item-id').value = '';
        row.querySelector('.item-name-display').value = '';
        row.querySelector('.item-search').value = '';
        row.querySelector('.stock-hint').textContent = '';
        row.querySelector('.item-results').classList.add('d-none');
    };

    const filterWarehouses = () => {
        if (!clientSelect) return;
        const clientId = clientSelect.value;
        linesContainer.querySelectorAll('.receipt-line').forEach(row => {
            const select = row.querySelector('.warehouse-select');
            let selectedIsValid = !select.value;
            Array.from(select.options).forEach(option => {
                if (!option.value) return;
                const visible = clientId !== '' && option.dataset.clientId === clientId;
                option.disabled = !visible;
                option.hidden = !visible;
                if (option.selected && visible) selectedIsValid = true;
            });
            if (!selectedIsValid) {
                select.value = '';
                resetItem(row);
            }
        });
    };

    const showResults = (row, items) => {
        const results = row.querySelector('.item-results');
        results.innerHTML = '';
        if (!items.length) {
            results.innerHTML = '<div class="px-3 py-2 text-muted small">No in-stock items found.</div>';
        } else {
            items.forEach(item => {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'item-result';
                button.innerHTML = `<span>${escapeHtml(item.text)}</span><span class="item-result-stock">${escapeHtml(item.quantity)} available</span>`;
                button.addEventListener('click', () => {
                    row.querySelector('.warehouse-item-id').value = item.id;
                    row.querySelector('.item-name-display').value = item.text;
                    row.querySelector('.item-search').value = item.text;
                    row.querySelector('.stock-hint').textContent = `In stock now: ${item.quantity}`;
                    results.classList.add('d-none');
                });
                results.appendChild(button);
            });
        }
        results.classList.remove('d-none');
    };

    const searchItems = async row => {
        const warehouseId = row.querySelector('.warehouse-select').value;
        const input = row.querySelector('.item-search');
        if (!warehouseId) {
            resetItem(row);
            row.querySelector('.stock-hint').textContent = 'Select a warehouse first.';
            return;
        }
        const url = new URL(itemSearchUrl, window.location.origin);
        url.searchParams.set('warehouse_id', warehouseId);
        url.searchParams.set('search', input.value.trim());
        const response = await fetch(url, { headers: { Accept: 'application/json' } });
        if (!response.ok) {
            showResults(row, []);
            return;
        }
        showResults(row, await response.json());
    };

    const renumberLines = () => {
        linesContainer.querySelectorAll('.receipt-line').forEach((row, index) => {
            row.querySelector('.receipt-line-number').textContent = index + 1;
            row.querySelectorAll('[name]').forEach(field => {
                field.name = field.name.replace(/items\[\d+\]/, `items[${index}]`);
            });
        });
    };

    document.getElementById('addReceiptLine').addEventListener('click', () => {
        const html = template.innerHTML.replaceAll('__INDEX__', nextIndex).replaceAll('__NUMBER__', nextIndex + 1);
        linesContainer.insertAdjacentHTML('beforeend', html);
        const newRow = linesContainer.lastElementChild;
        nextIndex++;
        filterWarehouses();
        requestAnimationFrame(() => {
            newRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
            setTimeout(() => newRow.querySelector('.warehouse-select').focus({ preventScroll: true }), 350);
        });
    });

    linesContainer.addEventListener('change', event => {
        if (event.target.classList.contains('warehouse-select')) resetItem(event.target.closest('.receipt-line'));
    });

    linesContainer.addEventListener('input', event => {
        if (!event.target.classList.contains('item-search')) return;
        const row = event.target.closest('.receipt-line');
        row.querySelector('.warehouse-item-id').value = '';
        row.querySelector('.item-name-display').value = event.target.value;
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => searchItems(row), 250);
    });

    linesContainer.addEventListener('focusin', event => {
        if (event.target.classList.contains('item-search')) searchItems(event.target.closest('.receipt-line'));
    });

    linesContainer.addEventListener('click', event => {
        const removeButton = event.target.closest('.remove-line');
        if (!removeButton) return;
        const rows = linesContainer.querySelectorAll('.receipt-line');
        const row = removeButton.closest('.receipt-line');
        if (rows.length === 1) {
            row.querySelector('.warehouse-select').value = '';
            row.querySelector('.quantity-input').value = '';
            resetItem(row);
        } else {
            row.remove();
            renumberLines();
        }
    });

    document.addEventListener('click', event => {
        if (!event.target.closest('.item-search-wrap')) document.querySelectorAll('.item-results').forEach(el => el.classList.add('d-none'));
    });

    if (clientSelect) clientSelect.addEventListener('change', filterWarehouses);
    filterWarehouses();

    function escapeHtml(value) {
        const div = document.createElement('div');
        div.textContent = String(value);
        return div.innerHTML;
    }
});
</script>
@endsection
