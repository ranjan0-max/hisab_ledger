@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="fw-bold text-dark mb-0">Dashboard</h3>
        </div>
        <span class="badge bg-light text-secondary border px-3 py-2">
            <i class="bi bi-clock me-1"></i> {{ date('d M Y') }}
        </span>
    </div>

    <!-- Meaningful Analytics Stats Grid -->
    <div class="row g-2 g-md-3 mb-4">
        <!-- Grand Total Money To Receive (Kul Kitne Paise Lene Hain) -->
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm p-3 border-start border-danger border-4 rounded-3 h-100 bg-danger-subtle bg-opacity-10">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-danger fw-bold">TOTAL RECEIVABLE (KUL LENA HAI)</span>
                    <i class="bi bi-wallet2 text-danger fs-4"></i>
                </div>
                <div class="fs-3 fw-extrabold text-danger mt-2">₹{{ number_format($stats['totalReceivable'] ?? 0, 2) }}</div>
                <div class="text-muted small mt-1" style="font-size: 11px;">
                    <i class="bi bi-info-circle me-1"></i>Combined Total (Customer Ledgers + Daily Entries)
                </div>
            </div>
        </div>

        <!-- Customer Ledgers Due (Customer Se Kitne Lene Hain) -->
        <div class="col-6 col-md-4">
            <div class="card border-0 shadow-sm p-3 border-start border-primary border-4 rounded-3 h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted small fw-semibold">Customer Khata Due</span>
                    <i class="bi bi-people text-primary fs-5"></i>
                </div>
                <div class="fs-4 fw-bold text-primary mt-2">₹{{ number_format($stats['totalCustomerDue'], 2) }}</div>
                <div class="text-muted small" style="font-size: 11px;">Regular customer ledgers</div>
            </div>
        </div>

        <!-- Daily Entries Pending Due (Daily Entries Se Kitne Lene Hain) -->
        <div class="col-6 col-md-4">
            <div class="card border-0 shadow-sm p-3 border-start border-info border-4 rounded-3 h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted small fw-semibold">Daily Entries Pending</span>
                    <i class="bi bi-receipt text-info fs-5"></i>
                </div>
                <div class="fs-4 fw-bold text-info-emphasis mt-2">₹{{ number_format($stats['pendingDailyAmount'], 2) }}</div>
                <div class="text-muted small" style="font-size: 11px;">{{ $stats['pendingDailyCount'] }} counter bills</div>
            </div>
        </div>
    </div>



    <!-- Recent Activity -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="card-title fw-bold mb-0">Recent Transactions</h6>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">Latest Activity</span>
        </div>
        
        <!-- Desktop Table View -->
        <div class="card-body p-0 d-none d-md-block">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Party</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentTransactions as $tx)
                            <tr>
                                <td>{{ $tx->transaction_date->format('d M Y') }}</td>
                                <td class="fw-semibold">{{ $tx->contact->name ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-dark border border-secondary-subtle">{{ $tx->transaction_type }}</span>
                                </td>
                                <td>{{ $tx->description }}</td>
                                <td class="text-end fw-bold {{ in_array($tx->transaction_type, ['PAYMENT', 'CUSTOMER_PAYMENT', 'SUPPLIER_PAYMENT']) ? 'text-success' : 'text-danger' }}">
                                    ₹{{ number_format($tx->amount, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No recent transactions found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Mobile Cards View for Recent Transactions -->
        <div class="card-body p-2 d-md-none">
            @forelse($recentTransactions as $tx)
                <div class="mobile-card p-3 mb-2 rounded-3 border">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <div>
                            <h6 class="fw-bold text-dark mb-0" style="font-size: 14px;">{{ $tx->contact->name ?? 'N/A' }}</h6>
                            <span class="small text-muted" style="font-size: 11px;">
                                <i class="bi bi-calendar-event me-1"></i>{{ $tx->transaction_date->format('d M Y') }}
                            </span>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold fs-6 {{ in_array($tx->transaction_type, ['PAYMENT', 'CUSTOMER_PAYMENT', 'SUPPLIER_PAYMENT']) ? 'text-success' : 'text-danger' }}">
                                ₹{{ number_format($tx->amount, 2) }}
                            </div>
                            <span class="badge bg-light text-dark border" style="font-size: 10px;">{{ $tx->transaction_type }}</span>
                        </div>
                    </div>
                    @if($tx->description)
                        <div class="small text-secondary mt-1 bg-light p-2 rounded" style="font-size: 12px;">
                            {{ $tx->description }}
                        </div>
                    @endif
                </div>
            @empty
                <div class="text-center text-muted py-4 small">No recent transactions found.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
