<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Statement - {{ $contact->name }}</title>
    <style>
        @page {
            margin: 25px 30px;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #1e293b;
            line-height: 1.5;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            border-bottom: 2px solid #4f46e5;
            padding-bottom: 12px;
        }
        .company-title {
            font-size: 20px;
            font-weight: bold;
            color: #4f46e5;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .statement-title {
            font-size: 16px;
            font-weight: bold;
            color: #0f172a;
            text-align: right;
        }
        .info-section {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
        }
        .info-cell {
            padding: 10px 14px;
            vertical-align: top;
        }
        .party-name {
            font-size: 14px;
            font-weight: bold;
            color: #0f172a;
            margin-bottom: 4px;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            font-size: 9px;
            font-weight: bold;
            color: #4f46e5;
            background-color: #e0e7ff;
            border-radius: 10px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .table th {
            background-color: #4f46e5;
            color: #ffffff;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9px;
            letter-spacing: 0.5px;
            padding: 5px 8px;
            border: 1px solid #4f46e5;
        }
        .table td {
            padding: 4px 8px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 9.5px;
        }
        .table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
        .text-success { color: #16a34a; }
        .text-danger { color: #dc2626; }
        .text-muted { color: #64748b; }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 9px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <table class="header-table">
        <tr>
            <td>
                <div class="company-title">{{ $contact->client->name ?? config('app.name', 'Hisab Ledger') }}</div>
                <div class="text-muted" style="font-size: 10px;">Official Account Ledger Statement</div>
            </td>
            <td class="statement-title">
                ACCOUNT STATEMENT
                <div class="text-muted" style="font-size: 9.5px; font-weight: normal;">
                    @if(!empty($fromDate) || !empty($toDate))
                        Period: {{ $fromDate ? \Carbon\Carbon::parse($fromDate)->format('d M Y') : 'Start' }} to {{ $toDate ? \Carbon\Carbon::parse($toDate)->format('d M Y') : 'Present' }}
                    @else
                        Generated on: {{ now()->format('d M Y, h:i A') }}
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <!-- Party Details -->
    <table class="info-section">
        <tr>
            <td class="info-cell" style="width: 60%;">
                <div class="party-name">{{ $contact->name }}</div>
                <div><strong>Khata No:</strong> <span class="badge">#{{ $contact->khata_number }}</span></div>
                <div><strong>Type:</strong> {{ $contact->type === 'REGULAR_CUSTOMER' ? 'Customer' : 'Supplier' }}</div>
                @if($contact->phoneNumbers->first())
                    <div><strong>Mobile:</strong> {{ $contact->phoneNumbers->first()->phone_number }}</div>
                @endif
            </td>
            <td class="info-cell text-right" style="width: 40%;">
                <div style="margin-bottom: 6px;">
                    <div class="text-muted" style="font-size: 9px;">OPENING BALANCE</div>
                    <div style="font-size: 11px; font-weight: bold;">
                        Rs. {{ number_format($contact->opening_balance, 2) }}
                        <span style="font-size: 9px;" class="{{ $contact->opening_balance_type === 'DUE' ? 'text-danger' : 'text-success' }}">
                            ({{ $contact->opening_balance_type }})
                        </span>
                    </div>
                </div>
                <div>
                    <div class="text-muted" style="font-size: 9px; font-weight: bold;">CURRENT NET BALANCE</div>
                    <div style="font-size: 14px; font-weight: bold;">
                        @if($currentBalance < 0)
                            <span class="text-success">Rs. {{ number_format(abs($currentBalance), 2) }} (ADVANCE)</span>
                        @elseif($currentBalance > 0)
                            <span class="text-danger">Rs. {{ number_format($currentBalance, 2) }} (DUE)</span>
                        @else
                            <span class="text-muted">Rs. 0.00</span>
                        @endif
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <!-- Transactions Table -->
    <table class="table">
        <thead>
            <tr>
                <th class="text-left" style="width: 15%;">Date</th>
                <th class="text-left" style="width: 15%;">Type</th>
                <th class="text-left" style="width: 40%;">Description</th>
                <th class="text-right" style="width: 15%;">Amount</th>
                <th class="text-right" style="width: 15%;">Balance</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $tx)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($tx->transaction_date)->format('d M Y') }}</td>
                    <td>
                        <span class="fw-bold">{{ $tx->transaction_type }}</span>
                    </td>
                    <td>{{ $tx->description ?? '—' }}</td>
                    <td class="text-right fw-bold {{ in_array($tx->transaction_type, ['PAYMENT', 'SALE']) ? 'text-success' : 'text-danger' }}">
                        Rs. {{ number_format($tx->amount, 2) }}
                    </td>
                    <td class="text-right fw-bold">
                        Rs. {{ number_format($tx->running_balance, 2) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-muted" style="padding: 20px;">No transactions recorded yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        This is a computer-generated statement and does not require a physical signature. Powered by {{ config('app.name', 'Hisab Ledger') }}.
    </div>
</body>
</html>
