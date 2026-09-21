@extends('layouts.app')

@section('content')
<style>
    .ledger-back-button {
        border-color: #e2e8f0;
        color: #64748b;
        background: #f8fafc;
    }

    .ledger-profile-card {
        border: 1px solid #e0e7ff !important;
        box-shadow: 0 5px 18px rgba(79, 70, 229, 0.06) !important;
    }

    .ledger-khata {
        display: inline-flex;
        padding: 0.3rem 0.6rem;
        border: 1px solid #c7d2fe;
        border-radius: 7px;
        color: #4f46e5;
        background: #eef2ff;
        font-size: 0.76rem;
        font-weight: 700;
    }

    .ledger-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .ledger-meta-item {
        display: inline-flex;
        align-items: center;
        padding: 0.32rem 0.55rem;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        color: #64748b;
        background: #f8fafc;
        font-size: 0.72rem;
    }

    .ledger-meta-item i {
        color: #6366f1;
    }

    .ledger-balance-due {
        border-color: #fecaca;
        color: #b91c1c;
        background: #fef2f2;
    }

    .ledger-balance-advance {
        border-color: #bbf7d0;
        color: #15803d;
        background: #f0fdf4;
    }

    .ledger-balance-clear {
        color: #475569;
        background: #f1f5f9;
    }

    .ledger-pdf-button {
        border-color: #fecaca;
        color: #b91c1c;
        background: #fef2f2;
    }

    .ledger-add-button {
        border-color: #c7d2fe;
        color: #4338ca;
        background: #eef2ff;
    }

    @media (max-width: 767.98px) {
        .ledger-profile-card .card-body {
            padding: 1rem !important;
        }

        .ledger-profile-name {
            font-size: 1.2rem;
        }

        .ledger-meta {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
        }

        .ledger-meta-item {
            width: 100%;
            overflow-wrap: anywhere;
        }

        .ledger-header-actions .btn {
            flex: 1 1 0;
            min-width: 0;
        }

        .ledger-mobile-card {
            border: 1px solid #e2e8f0 !important;
            border-radius: 14px !important;
            background: #ffffff;
            box-shadow: 0 4px 14px rgba(15, 23, 42, 0.05) !important;
        }

        .ledger-mobile-card .card-body {
            padding: 1rem !important;
        }

        .ledger-mobile-card .min-w-0 {
            min-width: 0;
        }

        .ledger-mobile-card-void {
            opacity: 0.68;
        }

        .ledger-tx-header {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            align-items: start;
            gap: 0.75rem;
        }

        .ledger-tx-date {
            color: #94a3b8;
            font-size: 0.68rem;
        }

        .ledger-tx-amount {
            color: #0f172a;
            font-size: 0.9rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .ledger-amount-sale {
            color: #b91c1c;
        }

        .ledger-amount-cash {
            color: #a16207;
        }

        .ledger-amount-payment {
            color: #15803d;
        }

        .ledger-tx-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 0.35rem;
            margin-top: 0.35rem;
        }

        .ledger-tx-tag {
            display: inline-flex;
            padding: 0.2rem 0.45rem;
            border: 1px solid transparent;
            border-radius: 6px;
            font-size: 0.62rem;
            font-weight: 700;
        }

        .ledger-tx-sale {
            border-color: #fecaca;
            color: #b91c1c;
            background: #fef2f2;
        }

        .ledger-tx-cash {
            border-color: #fde68a;
            color: #a16207;
            background: #fffbeb;
        }

        .ledger-tx-payment {
            border-color: #bbf7d0;
            color: #15803d;
            background: #f0fdf4;
        }

        .ledger-tx-other,
        .ledger-tx-mode {
            border-color: #e2e8f0;
            color: #64748b;
            background: #f8fafc;
        }

        .ledger-tx-description {
            margin: 0.75rem 0 0;
            color: #475569;
            font-size: 0.78rem;
            line-height: 1.45;
            overflow-wrap: anywhere;
        }

        .ledger-tx-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            margin-top: 0.75rem;
            padding-top: 0.7rem;
            border-top: 1px solid #eef2f7;
        }

        .ledger-tx-status {
            color: #64748b;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .ledger-void-button {
            border-color: #fecaca;
            color: #b91c1c;
            background: #fef2f2;
            font-size: 0.72rem;
            font-weight: 600;
        }

        .ledger-voided-label {
            display: inline-flex;
            padding: 0.2rem 0.45rem;
            border-radius: 6px;
            color: #64748b;
            background: #f1f5f9;
            font-size: 0.65rem;
            font-weight: 700;
        }
    }
</style>
<div class="container-fluid">
    <!-- Back Link -->
    <div class="mb-3">
        <a href="{{ route('customers.index') }}" class="btn btn-sm ledger-back-button">
            <i class="bi bi-arrow-left me-1"></i> Back to Customers
        </a>
    </div>

    <!-- Premium Header Card -->
    <div class="card ledger-profile-card mb-4 bg-white overflow-hidden">
        <div class="card-body p-3 p-md-4">
            <div class="row align-items-center g-3">
                <div class="col-12 col-md-7">
                    <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                        <h3 class="ledger-profile-name fw-bold text-dark mb-0">{{ $contact->name }}</h3>
                        <span class="ledger-khata">
                            Khata #{{ $contact->khata_number }}
                        </span>
                    </div>
                    <div class="ledger-meta">
                        <span class="ledger-meta-item"><i class="bi bi-telephone me-1"></i> {{ $contact->phoneNumbers->first()->phone_number ?? 'No Phone' }}</span>
                        <span class="ledger-meta-item"><i class="bi bi-wallet2 me-1"></i> Opening: ₹{{ number_format($contact->opening_balance, 2) }} ({{ $contact->opening_balance_type }})</span>
                        @php
                            // $currentBalance is passed directly from controller (SQL aggregate, no PHP loop)
                        @endphp
                        <span class="ledger-meta-item fw-semibold {{ $currentBalance > 0 ? 'ledger-balance-due' : ($currentBalance < 0 ? 'ledger-balance-advance' : 'ledger-balance-clear') }}">
                            <i class="bi bi-bar-chart-line me-1"></i>
                            Balance: ₹{{ number_format(abs($currentBalance), 2) }}
                            @if($currentBalance > 0) (Due)
                            @elseif($currentBalance < 0) (Advance)
                            @else (Clear)
                            @endif
                        </span>
                        @if($contact->address)
                            <span class="ledger-meta-item"><i class="bi bi-geo-alt me-1"></i> {{ $contact->address }}</span>
                        @endif
                    </div>
                </div>
                <div class="col-12 col-md-5 text-md-end">
                    <div class="ledger-header-actions d-flex gap-2 justify-content-start justify-content-md-end">
                        <button class="btn btn-sm ledger-pdf-button" data-bs-toggle="modal" data-bs-target="#downloadPdfModal">
                            <i class="bi bi-file-earmark-pdf me-1"></i> PDF Statement
                        </button>
                        @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('customers.manage'))
                            <button class="btn btn-sm ledger-add-button" data-bs-toggle="modal" data-bs-target="#addTransactionModal">
                                <i class="bi bi-plus-lg me-1"></i> Add Entry
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Running Ledger Table (Desktop) -->
    <div class="card border-0 shadow-sm d-none d-md-block">
        <div class="card-header bg-white py-3">
            <h5 class="fw-bold mb-0">Ledger Statement</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th>Payment Mode</th>
                            <th class="text-end">Amount</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $tx)
                            <tr class="{{ $tx->status === 'VOID' ? 'table-secondary text-decoration-line-through' : '' }}">
                                <td>{{ \Carbon\Carbon::parse($tx->transaction_date)->format('d M Y') }}</td>
                                <td>
                                    @if($tx->transaction_type === 'SALE')
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">SALE (+)</span>
                                    @elseif($tx->transaction_type === 'CASH_GIVEN')
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">CASH GIVEN (+)</span>
                                    @elseif($tx->transaction_type === 'CUSTOMER_PAYMENT')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">PAYMENT (-)</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $tx->transaction_type }}</span>
                                    @endif
                                </td>
                                <td>{{ $tx->description }}</td>
                                <td><span class="badge bg-light text-dark border">{{ $tx->payment_mode ?? '—' }}</span></td>
                                <td class="text-end fw-bold">₹{{ number_format($tx->amount, 2) }}</td>
                                <td class="text-end">
                                    @if($tx->status !== 'VOID' && (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('transactions.void')))
                                        <form method="POST" action="{{ route('transactions.void', $tx->id) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to void this transaction?')">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Void</button>
                                        </form>
                                    @elseif($tx->status === 'VOID')
                                        <span class="badge bg-secondary">VOIDED</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">No transactions recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Running Ledger Cards (Mobile) -->
    <div class="d-md-none">
        <h5 class="fw-bold mb-3">Ledger Statement</h5>
        @forelse($transactions as $tx)
            <div class="card ledger-mobile-card mb-3 {{ $tx->status === 'VOID' ? 'ledger-mobile-card-void' : '' }}">
                <div class="card-body">
                    <div class="ledger-tx-header">
                        <div class="min-w-0">
                            <span class="ledger-tx-date"><i class="bi bi-calendar3 me-1"></i>{{ \Carbon\Carbon::parse($tx->transaction_date)->format('d M Y') }}</span>
                            <div class="ledger-tx-tags">
                                @if($tx->transaction_type === 'SALE')
                                    <span class="ledger-tx-tag ledger-tx-sale">SALE (+)</span>
                                @elseif($tx->transaction_type === 'CASH_GIVEN')
                                    <span class="ledger-tx-tag ledger-tx-cash">CASH GIVEN (+)</span>
                                @elseif($tx->transaction_type === 'CUSTOMER_PAYMENT')
                                    <span class="ledger-tx-tag ledger-tx-payment">PAYMENT (-)</span>
                                @else
                                    <span class="ledger-tx-tag ledger-tx-other">{{ $tx->transaction_type }}</span>
                                @endif
                                @if($tx->payment_mode)
                                    <span class="ledger-tx-tag ledger-tx-mode">{{ $tx->payment_mode }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="ledger-tx-amount {{ $tx->transaction_type === 'CUSTOMER_PAYMENT' ? 'ledger-amount-payment' : ($tx->transaction_type === 'CASH_GIVEN' ? 'ledger-amount-cash' : 'ledger-amount-sale') }}">
                            ₹{{ number_format($tx->amount, 2) }}
                        </div>
                    </div>
                    <p class="ledger-tx-description {{ $tx->status === 'VOID' ? 'text-decoration-line-through' : '' }}">
                        {{ $tx->description ?: 'No description' }}
                    </p>
                    <div class="ledger-tx-footer">
                        <span class="ledger-tx-status">Status: {{ $tx->status }}</span>
                        @if($tx->status !== 'VOID' && (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('transactions.void')))
                            <form method="POST" action="{{ route('transactions.void', $tx->id) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to void this transaction?')">
                                @csrf
                                <button type="submit" class="btn btn-sm ledger-void-button"><i class="bi bi-slash-circle me-1"></i> Void</button>
                            </form>
                        @elseif($tx->status === 'VOID')
                            <span class="ledger-voided-label">VOIDED</span>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="card border-0 shadow-sm p-4 text-center text-muted">No transactions recorded yet.</div>
        @endforelse
    </div>

    @if($transactions->hasPages())
        <div class="mt-3 d-flex justify-content-center">
            {{ $transactions->links() }}
        </div>
    @endif
</div>

<!-- Add Transaction Modal -->
<div class="modal fade" id="addTransactionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('transactions.store') }}">
                @csrf
                <input type="hidden" name="contact_id" value="{{ $contact->id }}">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Add Transaction</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Transaction Type *</label>
                        <select name="transaction_type" id="txTypeSelect" class="form-select" required onchange="togglePaymentMode()">
                            <option value="SALE">SALE (Udhar / (+) Balance)</option>
                            <option value="CASH_GIVEN">CASH GIVEN (Cash diya / (+) Balance)</option>
                            <option value="CUSTOMER_PAYMENT">CUSTOMER PAYMENT (Jama / (-) Balance)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount (₹) *</label>
                        <input type="number" step="0.01" name="amount" class="form-control" required placeholder="0.00">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Transaction Date *</label>
                        <input type="date" name="transaction_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3" id="paymentModeContainer" style="display: none;">
                        <label class="form-label">Payment Mode</label>
                        <select name="payment_mode" class="form-select">
                            <option value="CASH">CASH</option>
                            <option value="ONLINE">ONLINE / UPI</option>
                            <option value="CHEQUE">CHEQUE</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description (Optional)</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Bill no, item description, etc. (optional)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Transaction</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function togglePaymentMode() {
    var type = document.getElementById('txTypeSelect').value;
    var container = document.getElementById('paymentModeContainer');
    if (type === 'CUSTOMER_PAYMENT' || type === 'SUPPLIER_PAYMENT') {
        container.style.display = 'block';
    } else {
        container.style.display = 'none';
    }
}
</script>
<!-- Download PDF Statement Modal with Date Filters -->
<div class="modal fade" id="downloadPdfModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="GET" action="{{ route('customers.statement.pdf', $contact->id) }}" target="_blank" onsubmit="setTimeout(() => { bootstrap.Modal.getInstance(document.getElementById('downloadPdfModal')).hide(); }, 300);">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="bi bi-file-earmark-pdf text-danger me-2"></i> Download Account Statement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-3">Select date range to filter statement entries or leave blank to download complete ledger statement.</p>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">From Date</label>
                            <input type="date" name="from_date" class="form-control form-control-sm">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">To Date</label>
                            <input type="date" name="to_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-danger px-3"><i class="bi bi-download me-1"></i> Download PDF</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
