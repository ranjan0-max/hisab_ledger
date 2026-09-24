<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $receipt->number }}</title>
    <style>
        @page { size: A4 portrait; margin: 12mm; }
        * { box-sizing: border-box; }
        body { margin: 0; color: #111827; background: #fff; font-family: Arial, sans-serif; font-size: 12px; }
        .print-actions { display: flex; justify-content: flex-end; margin-bottom: 16px; }
        .print-button { padding: 8px 14px; border: 0; border-radius: 5px; color: #fff; background: #2563eb; cursor: pointer; }
        .sheet-header { margin-bottom: 16px; text-align: center; }
        .sheet-title { margin: 0 0 5px; font-size: 20px; }
        .receipt-number { margin: 0; font-size: 14px; font-weight: 700; }
        .meta-table { width: 100%; margin-bottom: 14px; border-collapse: collapse; }
        .meta-table td { width: 50%; padding: 6px 8px; border: 1px solid #9ca3af; }
        .meta-label { display: block; margin-bottom: 2px; color: #6b7280; font-size: 9px; font-weight: 700; text-transform: uppercase; }
        .items-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .items-table thead { display: table-header-group; }
        .items-table tr { break-inside: avoid; page-break-inside: avoid; }
        .items-table th, .items-table td { padding: 7px 8px; border: 1px solid #374151; text-align: left; vertical-align: middle; }
        .items-table th { background: #f3f4f6; font-size: 10px; text-transform: uppercase; }
        .serial-column { width: 48px; text-align: center !important; }
        .quantity-column { width: 120px; text-align: right !important; }
        .total-row td { font-weight: 700; background: #f9fafb; }
        @media print { .print-actions { display: none; } }
    </style>
</head>
<body>
    <div class="print-actions"><button type="button" class="print-button" onclick="window.print()">Print</button></div>

    <header class="sheet-header">
        <h1 class="sheet-title">Receipt</h1>
        <p class="receipt-number">{{ $receipt->number }}</p>
    </header>

    <table class="meta-table">
        <tr>
            <td><span class="meta-label">Customer</span>{{ $receipt->customer_name }}</td>
            <td><span class="meta-label">Date</span>{{ $receipt->created_at->format('d M Y, h:i A') }}</td>
        </tr>
        <tr>
            <td colspan="2"><span class="meta-label">Warehouse</span>{{ $receipt->items->first()?->warehouseItem?->warehouse?->name }}</td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th class="serial-column">#</th>
                <th>Item</th>
                <th class="quantity-column">Quantity</th>
            </tr>
        </thead>
        <tbody>
            @foreach($receipt->items as $line)
                <tr>
                    <td class="serial-column">{{ $loop->iteration }}</td>
                    <td>{{ $line->warehouseItem->item_name }}</td>
                    <td class="quantity-column">{{ rtrim(rtrim(number_format((float) $line->quantity, 3, '.', ''), '0'), '.') }}</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="2" class="quantity-column">Total Quantity</td>
                <td class="quantity-column">{{ rtrim(rtrim(number_format((float) $receipt->items->sum('quantity'), 3, '.', ''), '0'), '.') }}</td>
            </tr>
        </tbody>
    </table>

    <script>
        window.addEventListener('load', () => window.print());
    </script>
</body>
</html>
