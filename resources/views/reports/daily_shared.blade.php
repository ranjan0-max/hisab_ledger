@extends('reports.partials.shared_layout', ['paginator' => $entries])

@section('title', 'Daily Entries Report')

@section('company', $client->name ?? config('app.name', 'Hisab Ledger'))

@section('subtitle', 'Daily Entries Report')

@section('table')
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Customer Name</th>
                    <th>Description</th>
                    <th>Mode</th>
                    <th class="text-right">Total Amt</th>
                    <th class="text-right">Paid Amt</th>
                </tr>
            </thead>
            <tbody>
                @forelse($entries as $entry)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($entry->entry_date)->format('d M Y') }}</td>
                        <td class="desc">
                            {{ $entry->customer_name }}
                            @if($entry->mobile_number)
                                <div class="muted">{{ $entry->mobile_number }}</div>
                            @endif
                        </td>
                        <td class="desc">{{ $entry->description }}</td>
                        <td>{{ $entry->payment_mode ?? 'CASH' }}</td>
                        <td class="text-right">Rs. {{ number_format($entry->total_amount, 2) }}</td>
                        <td class="text-right">Rs. {{ number_format($entry->paid_amount, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="muted" style="text-align: center;">No daily entries found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
@endsection
