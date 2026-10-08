@extends('reports.partials.shared_layout', ['paginator' => $contacts])

@php
    $isCustomer = $type === 'REGULAR_CUSTOMER';
@endphp

@section('title', $isCustomer ? 'Customers List' : 'Suppliers List')

@section('company', $client->name ?? config('app.name', 'Hisab Ledger'))

@section('subtitle')
    {{ $isCustomer ? 'Customers' : 'Suppliers' }} List
    @if(!empty($inactiveMonths))
        · No transaction in last {{ $inactiveMonths }} {{ $inactiveMonths == 1 ? 'month' : 'months' }} + Pending Balance
    @endif
@endsection

@section('table')
        <table>
            <thead>
                <tr>
                    <th>Khata No</th>
                    <th>{{ $isCustomer ? 'Customer Name' : 'Supplier Name' }}</th>
                    <th>Mobile Number</th>
                    <th>Address</th>
                    <th class="text-right">Current Balance</th>
                </tr>
            </thead>
            <tbody>
                @forelse($contacts as $contact)
                    <tr>
                        <td>{{ $contact->khata_number !== null ? '#' . $contact->khata_number : '' }}</td>
                        <td class="desc">{{ $contact->name }}</td>
                        <td>{{ $contact->phoneNumbers->first()->phone_number ?? '—' }}</td>
                        <td class="desc">{{ $contact->address ?? '—' }}</td>
                        <td class="text-right">
                            @if($contact->current_balance < 0)
                                <span class="advance">Rs. {{ number_format(abs($contact->current_balance), 2) }} (ADV)</span>
                            @elseif($contact->current_balance > 0)
                                <span class="due">Rs. {{ number_format($contact->current_balance, 2) }} (DUE)</span>
                            @else
                                <span>Rs. 0.00</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="muted" style="text-align: center;">No {{ $isCustomer ? 'customers' : 'suppliers' }} found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
@endsection
