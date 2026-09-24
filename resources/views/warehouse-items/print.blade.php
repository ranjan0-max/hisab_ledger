<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $warehouse->name }} - Items</title>
    <style>
        @page { size: A4 portrait; margin: 12mm; }
        * { box-sizing: border-box; }
        body { margin: 0; color: #111827; background: #fff; font-family: Arial, sans-serif; font-size: 12px; }
        .print-actions { display: flex; justify-content: flex-end; margin-bottom: 16px; }
        .print-button { padding: 8px 14px; border: 0; border-radius: 5px; color: #fff; background: #2563eb; cursor: pointer; }
        .sheet-header { margin-bottom: 16px; text-align: center; }
        .sheet-title { margin: 0 0 5px; font-size: 20px; }
        .warehouse-name { margin: 0; font-size: 14px; font-weight: 600; }
        .client-name { margin: 4px 0 0; color: #4b5563; font-size: 11px; }
        .summary { display: flex; justify-content: space-between; margin-bottom: 8px; color: #4b5563; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        thead { display: table-header-group; }
        tr { break-inside: avoid; page-break-inside: avoid; }
        th, td { padding: 7px 8px; border: 1px solid #374151; text-align: left; vertical-align: middle; }
        th { background: #f3f4f6; font-size: 10px; text-transform: uppercase; }
        .serial-column { width: 48px; text-align: center; }
        .quantity-column { width: 130px; text-align: right; }
        .empty-row { padding: 24px; color: #6b7280; text-align: center; }
        @media print {
            .print-actions { display: none; }
        }
    </style>
</head>
<body>
    <div class="print-actions"><button type="button" class="print-button" onclick="window.print()">Print</button></div>

    <header class="sheet-header">
        <h1 class="sheet-title">Warehouse Items</h1>
        <p class="warehouse-name">{{ $warehouse->name }}</p>
        @if(auth()->user()->isSuperAdmin())
            <p class="client-name">{{ $warehouse->client->name }}</p>
        @endif
    </header>

    <div class="summary">
        <span>Total items: {{ $items->count() }}</span>
        <span>Printed: {{ now()->format('d M Y, h:i A') }}</span>
    </div>

    <table>
        <thead>
            <tr>
                <th class="serial-column">#</th>
                <th>Item</th>
                <th class="quantity-column">Quantity</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $item)
                <tr>
                    <td class="serial-column">{{ $loop->iteration }}</td>
                    <td>{{ $item->item_name }}</td>
                    <td class="quantity-column">{{ rtrim(rtrim(number_format((float) $item->quantity, 3, '.', ''), '0'), '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="3" class="empty-row">No items found in this warehouse.</td></tr>
            @endforelse
        </tbody>
    </table>

    <script>
        window.addEventListener('load', () => window.print());
    </script>
</body>
</html>
