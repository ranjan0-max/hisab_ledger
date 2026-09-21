@extends('layouts.app')

@section('content')
<style>
    @media (max-width: 767.98px) {
        .recent-transactions-mobile {
            display: grid;
            gap: 0.6rem;
            width: 100%;
            min-width: 0;
            overflow: hidden;
            padding: 0.75rem !important;
            background: #f8fafc;
        }

        .transaction-mobile-card {
            width: 100%;
            max-width: 100%;
            min-width: 0;
            overflow: hidden;
            box-sizing: border-box;
            padding: 0.9rem;
            border: 1px solid #e2e8f0 !important;
            border-left: 1px solid #e2e8f0 !important;
            border-radius: 12px;
            background: #ffffff;
            box-shadow: none;
        }

        .transaction-mobile-card:active {
            transform: none;
        }

        .transaction-card-header {
            display: block;
            width: 100%;
            min-width: 0;
        }

        .transaction-party {
            display: block;
            max-width: 100%;
            min-width: 0;
            overflow: hidden;
            color: #0f172a;
            font-size: 0.88rem;
            font-weight: 700;
            line-height: 1.3;
            overflow-wrap: anywhere;
            white-space: normal;
        }

        .transaction-amount {
            display: block;
            width: 100%;
            margin-top: 0.25rem;
            font-size: 0.9rem;
            font-weight: 700;
            text-align: right;
            white-space: nowrap;
        }

        .transaction-meta {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.35rem;
            margin-top: 0.25rem;
            color: #94a3b8;
            font-size: 0.7rem;
        }

        .transaction-meta-separator {
            color: #cbd5e1;
        }

        .transaction-khata {
            display: inline-flex;
            padding: 0.12rem 0.38rem;
            border: 1px solid #c7d2fe;
            border-radius: 5px;
            color: #4f46e5;
            background: #eef2ff;
            font-weight: 600;
        }

        .transaction-card-footer {
            display: flex;
            align-items: flex-start;
            gap: 0.6rem;
            margin-top: 0.7rem;
            padding-top: 0.65rem;
            border-top: 1px solid #f1f5f9;
        }

        .transaction-type {
            flex: 0 0 auto;
            padding: 0.2rem 0.45rem;
            border: 1px solid transparent;
            border-radius: 5px;
            font-size: 0.62rem;
            font-weight: 700;
            letter-spacing: 0.02em;
            line-height: 1.35;
        }

        .transaction-type-credit {
            border-color: #bbf7d0;
            color: #15803d;
            background: #f0fdf4;
        }

        .transaction-type-debit {
            border-color: #fecaca;
            color: #b91c1c;
            background: #fef2f2;
        }

        .transaction-description {
            display: -webkit-box;
            overflow: hidden;
            color: #64748b;
            font-size: 0.72rem;
            line-height: 1.4;
            overflow-wrap: anywhere;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 2;
        }
    }
</style>
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
                            <th>Khata Number</th>
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
                                <td>{{ $tx->contact->khata_number ?? 'N/A' }}</td>
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
                                <td colspan="6" class="text-center text-muted py-4">No recent transactions found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Mobile Cards View for Recent Transactions -->
        <div class="card-body recent-transactions-mobile d-md-none">
            @forelse($recentTransactions as $tx)
                <div class="mobile-card transaction-mobile-card">
                    <div class="transaction-card-header">
                        <div class="transaction-party">{{ $tx->contact->name ?? 'N/A' }}</div>
                        <div class="transaction-amount {{ in_array($tx->transaction_type, ['PAYMENT', 'CUSTOMER_PAYMENT', 'SUPPLIER_PAYMENT']) ? 'text-success' : 'text-danger' }}">
                            ₹{{ number_format($tx->amount, 2) }}
                        </div>
                    </div>
                    <div class="transaction-meta">
                        <span class="transaction-khata">Khata #{{ $tx->contact->khata_number ?? 'N/A' }}</span>
                        <span class="transaction-meta-separator">&bull;</span>
                        <span><i class="bi bi-calendar3 me-1"></i>{{ $tx->transaction_date->format('d M Y') }}</span>
                    </div>
                    <div class="transaction-card-footer">
                        <span class="transaction-type {{ in_array($tx->transaction_type, ['PAYMENT', 'CUSTOMER_PAYMENT', 'SUPPLIER_PAYMENT']) ? 'transaction-type-credit' : 'transaction-type-debit' }}">
                            {{ str_replace('_', ' ', $tx->transaction_type) }}
                        </span>
                        @if($tx->description)
                            <span class="transaction-description">{{ $tx->description }}</span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center text-muted py-4 small">No recent transactions found.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
