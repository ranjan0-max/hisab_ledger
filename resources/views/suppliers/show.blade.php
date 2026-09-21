@extends('layouts.app')

@section('content')
<style>
    .supplier-ledger-back {
        border-color: #e2e8f0;
        color: #64748b;
        background: #f8fafc;
    }

    .supplier-ledger-profile {
        border: 1px solid #cffafe !important;
        box-shadow: 0 5px 18px rgba(8, 145, 178, 0.06) !important;
    }

    .supplier-ledger-khata {
        display: inline-flex;
        padding: 0.3rem 0.6rem;
        border: 1px solid #a5f3fc;
        border-radius: 7px;
        color: #0e7490;
        background: #ecfeff;
        font-size: 0.76rem;
        font-weight: 700;
    }

    .supplier-ledger-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .supplier-ledger-meta-item {
        display: inline-flex;
        align-items: center;
        padding: 0.32rem 0.55rem;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        color: #64748b;
        background: #f8fafc;
        font-size: 0.72rem;
    }

    .supplier-ledger-meta-item i {
        color: #0891b2;
    }

    .supplier-ledger-due {
        border-color: #fecaca;
        color: #b91c1c;
        background: #fef2f2;
    }

    .supplier-ledger-advance {
        border-color: #bbf7d0;
        color: #15803d;
        background: #f0fdf4;
    }

    .supplier-ledger-clear {
        color: #475569;
        background: #f1f5f9;
    }

    .supplier-ledger-pdf {
        border-color: #fecaca;
        color: #b91c1c;
        background: #fef2f2;
    }

    .supplier-ledger-add {
        border-color: #a5f3fc;
        color: #0e7490;
        background: #ecfeff;
    }

    @media (max-width: 767.98px) {
        .supplier-ledger-profile .card-body {
            padding: 1rem !important;
        }

        .supplier-ledger-name {
            font-size: 1.2rem;
        }

        .supplier-ledger-meta {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
        }

        .supplier-ledger-meta-item {
            width: 100%;
            overflow-wrap: anywhere;
        }

        .supplier-ledger-actions .btn {
            flex: 1 1 0;
            min-width: 0;
        }

        .supplier-ledger-mobile-card {
            border: 1px solid #e2e8f0 !important;
            border-radius: 14px !important;
            background: #ffffff;
            box-shadow: 0 4px 14px rgba(15, 23, 42, 0.05) !important;
        }

        .supplier-ledger-mobile-card .card-body {
            padding: 1rem !important;
        }

        .supplier-ledger-mobile-card .min-w-0 {
            min-width: 0;
        }

        .supplier-ledger-mobile-void {
            opacity: 0.68;
        }

        .supplier-ledger-tx-header {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            align-items: start;
            gap: 0.75rem;
        }

        .supplier-ledger-date {
            color: #94a3b8;
            font-size: 0.68rem;
        }

        .supplier-ledger-amount {
            font-size: 0.9rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .supplier-ledger-amount-purchase {
            color: #b91c1c;
        }

        .supplier-ledger-amount-payment {
            color: #15803d;
        }

        .supplier-ledger-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 0.35rem;
            margin-top: 0.35rem;
        }

        .supplier-ledger-tag {
            display: inline-flex;
            padding: 0.2rem 0.45rem;
            border: 1px solid transparent;
            border-radius: 6px;
            font-size: 0.62rem;
            font-weight: 700;
        }

        .supplier-ledger-purchase {
            border-color: #fecaca;
            color: #b91c1c;
            background: #fef2f2;
        }

        .supplier-ledger-payment {
            border-color: #bbf7d0;
            color: #15803d;
            background: #f0fdf4;
        }

        .supplier-ledger-other,
        .supplier-ledger-mode {
            border-color: #e2e8f0;
            color: #64748b;
            background: #f8fafc;
        }

        .supplier-ledger-description {
            margin: 0.75rem 0 0;
            color: #475569;
            font-size: 0.78rem;
            line-height: 1.45;
            overflow-wrap: anywhere;
        }

        .supplier-ledger-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            margin-top: 0.75rem;
            padding-top: 0.7rem;
            border-top: 1px solid #eef2f7;
        }

        .supplier-ledger-status {
            color: #64748b;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .supplier-ledger-void-button {
            border-color: #fecaca;
            color: #b91c1c;
            background: #fef2f2;
            font-size: 0.72rem;
            font-weight: 600;
        }

        .supplier-ledger-voided {
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
        <a href="{{ route('suppliers.index') }}" class="btn btn-sm supplier-ledger-back">
            <i class="bi bi-arrow-left me-1"></i> Back to Suppliers
        </a>
    </div>

    <!-- Premium Header Card -->
    <div class="card supplier-ledger-profile mb-4 bg-white overflow-hidden">
        <div class="card-body p-3 p-md-4">
            <div class="row align-items-center g-3">
                <div class="col-12 col-md-7">
                    <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                        <h3 class="supplier-ledger-name fw-bold text-dark mb-0">{{ $contact->name }}</h3>
                        <span class="supplier-ledger-khata">
                            Khata #{{ $contact->khata_number }}
                        </span>
                    </div>
                    <div class="supplier-ledger-meta">
                        <span class="supplier-ledger-meta-item"><i class="bi bi-telephone me-1"></i> {{ $contact->phoneNumbers->first()->phone_number ?? 'No Phone' }}</span>
                        <span class="supplier-ledger-meta-item"><i class="bi bi-wallet2 me-1"></i> Opening: ₹{{ number_format($contact->opening_balance, 2) }} ({{ $contact->opening_balance_type }})</span>
                        @php
                            // $currentBalance is passed directly from controller (SQL aggregate, no PHP loop)
                        @endphp
                        <span class="supplier-ledger-meta-item fw-semibold {{ $currentBalance > 0 ? 'supplier-ledger-due' : ($currentBalance < 0 ? 'supplier-ledger-advance' : 'supplier-ledger-clear') }}">
                            <i class="bi bi-bar-chart-line me-1"></i>
                            Balance: ₹{{ number_format(abs($currentBalance), 2) }}
                            @if($currentBalance > 0) (Due)
                            @elseif($currentBalance < 0) (Advance)
                            @else (Clear)
                            @endif
                        </span>
                        @if($contact->address)
                            <span class="supplier-ledger-meta-item"><i class="bi bi-geo-alt me-1"></i> {{ $contact->address }}</span>
                        @endif
                    </div>
                </div>
                <div class="col-12 col-md-5 text-md-end">
                    <div class="supplier-ledger-actions d-flex gap-2 justify-content-start justify-content-md-end">
                        <button class="btn btn-sm supplier-ledger-pdf" data-bs-toggle="modal" data-bs-target="#downloadPdfModal">
                            <i class="bi bi-file-earmark-pdf me-1"></i> PDF Statement
                        </button>
                        @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('suppliers.manage'))
                            <button class="btn btn-sm supplier-ledger-add" data-bs-toggle="modal" data-bs-target="#addTransactionModal">
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
                                    @if($tx->transaction_type === 'PURCHASE')
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">PURCHASE (+)</span>
                                    @elseif($tx->transaction_type === 'SUPPLIER_PAYMENT')
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
            <div class="card supplier-ledger-mobile-card mb-3 {{ $tx->status === 'VOID' ? 'supplier-ledger-mobile-void' : '' }}">
                <div class="card-body">
                    <div class="supplier-ledger-tx-header">
                        <div class="min-w-0">
                            <span class="supplier-ledger-date"><i class="bi bi-calendar3 me-1"></i>{{ \Carbon\Carbon::parse($tx->transaction_date)->format('d M Y') }}</span>
                            <div class="supplier-ledger-tags">
                                @if($tx->transaction_type === 'PURCHASE')
                                    <span class="supplier-ledger-tag supplier-ledger-purchase">PURCHASE (+)</span>
                                @elseif($tx->transaction_type === 'SUPPLIER_PAYMENT')
                                    <span class="supplier-ledger-tag supplier-ledger-payment">PAYMENT (-)</span>
                                @else
                                    <span class="supplier-ledger-tag supplier-ledger-other">{{ $tx->transaction_type }}</span>
                                @endif
                                @if($tx->payment_mode)
                                    <span class="supplier-ledger-tag supplier-ledger-mode">{{ $tx->payment_mode }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="supplier-ledger-amount {{ $tx->transaction_type === 'SUPPLIER_PAYMENT' ? 'supplier-ledger-amount-payment' : 'supplier-ledger-amount-purchase' }}">
                            ₹{{ number_format($tx->amount, 2) }}
                        </div>
                    </div>
                    <p class="supplier-ledger-description {{ $tx->status === 'VOID' ? 'text-decoration-line-through' : '' }}">
                        {{ $tx->description ?: 'No description' }}
                    </p>
                    <div class="supplier-ledger-footer">
                        <span class="supplier-ledger-status">Status: {{ $tx->status }}</span>
                        @if($tx->status !== 'VOID' && (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('transactions.void')))
                            <form method="POST" action="{{ route('transactions.void', $tx->id) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to void this transaction?')">
                                @csrf
                                <button type="submit" class="btn btn-sm supplier-ledger-void-button"><i class="bi bi-slash-circle me-1"></i> Void</button>
                            </form>
                        @elseif($tx->status === 'VOID')
                            <span class="supplier-ledger-voided">VOIDED</span>
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
                    <h5 class="modal-title fw-bold">Add Supplier Transaction</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Transaction Type *</label>
                        <select name="transaction_type" id="supplierTxTypeSelect" class="form-select" required onchange="toggleSupplierPaymentMode()">
                            <option value="PURCHASE">PURCHASE (Kharidi / (+) Balance)</option>
                            <option value="SUPPLIER_PAYMENT">SUPPLIER PAYMENT (Bhugtan / (-) Balance)</option>
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
                    <div class="mb-3" id="supplierPaymentModeContainer" style="display: none;">
                        <label class="form-label">Payment Mode</label>
                        <select name="payment_mode" class="form-select">
                            <option value="CASH">CASH</option>
                            <option value="ONLINE">ONLINE / UPI</option>
                            <option value="CHEQUE">CHEQUE</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description (Optional)</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Invoice no, item details, etc. (optional)"></textarea>
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
function toggleSupplierPaymentMode() {
    var type = document.getElementById('supplierTxTypeSelect').value;
    var container = document.getElementById('supplierPaymentModeContainer');
    if (type === 'SUPPLIER_PAYMENT') {
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
            <form method="GET" action="{{ route('suppliers.statement.pdf', $contact->id) }}" target="_blank" onsubmit="setTimeout(() => { bootstrap.Modal.getInstance(document.getElementById('downloadPdfModal')).hide(); }, 300);">
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
