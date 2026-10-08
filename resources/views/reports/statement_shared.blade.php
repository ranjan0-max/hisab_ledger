@extends('reports.partials.shared_layout', ['paginator' => $transactions])

@section('title', 'Statement - ' . $contact->name)

@section('company', $contact->client->name ?? config('app.name', 'Hisab Ledger'))

@section('subtitle')
    Account Statement
    @if(!empty($fromDate) || !empty($toDate))
        · Period: {{ $fromDate ? \Carbon\Carbon::parse($fromDate)->format('d M Y') : 'Start' }} to {{ $toDate ? \Carbon\Carbon::parse($toDate)->format('d M Y') : 'Present' }}
    @endif
@endsection

@section('summary')
    <div class="info">
        <div>
            <div class="party-name">{{ $contact->name }}</div>
            @if($contact->khata_number !== null)
                <div><strong>Khata No:</strong> #{{ $contact->khata_number }}</div>
            @endif
            <div><strong>Type:</strong> {{ $contact->type === 'REGULAR_CUSTOMER' ? 'Customer' : 'Supplier' }}</div>
            @if($contact->phoneNumbers->first())
                <div><strong>Mobile:</strong> {{ $contact->phoneNumbers->first()->phone_number }}</div>
            @endif
        </div>
        <div>
            <div class="muted">OPENING BALANCE</div>
            <div>
                Rs. {{ number_format($contact->opening_balance, 2) }}
                <span class="{{ $contact->opening_balance_type === 'DUE' ? 'due' : 'advance' }}">({{ $contact->opening_balance_type }})</span>
            </div>
            <div class="muted" style="margin-top: 6px;">CURRENT BALANCE (as of today)</div>
            <div class="balance">
                @if($currentBalance < 0)
                    <span class="advance">Rs. {{ number_format(abs($currentBalance), 2) }} (ADVANCE)</span>
                @elseif($currentBalance > 0)
                    <span class="due">Rs. {{ number_format($currentBalance, 2) }} (DUE)</span>
                @else
                    <span>Rs. 0.00</span>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('table')
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Description</th>
                    <th class="text-right">Debit</th>
                    <th class="text-right">Credit</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $tx)
                    @php
                        // Customer: Sale/Cash Given/Adjustment = Debit, Payment = Credit
                        // Supplier: Purchase/Adjustment = Credit, Payment = Debit
                        $isPayment = in_array($tx->transaction_type, ['CUSTOMER_PAYMENT', 'SUPPLIER_PAYMENT']);
                        $isDebit = $contact->type === 'REGULAR_CUSTOMER' ? !$isPayment : $isPayment;
                    @endphp
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($tx->transaction_date)->format('d M Y') }}</td>
                        <td>{{ $tx->transaction_type }}</td>
                        <td class="desc">{{ $tx->description ?? '—' }}</td>
                        <td class="text-right">{{ $isDebit ? 'Rs. ' . number_format($tx->amount, 2) : '' }}</td>
                        <td class="text-right">{{ $isDebit ? '' : 'Rs. ' . number_format($tx->amount, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="muted" style="text-align: center;">No transactions recorded.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
@endsection
