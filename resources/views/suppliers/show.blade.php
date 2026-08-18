@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Back Link -->
    <div class="mb-3">
        <a href="{{ route('suppliers.index') }}" class="btn btn-sm btn-light border text-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Suppliers
        </a>
    </div>

    <!-- Premium Header Card -->
    <div class="card border-0 shadow-sm mb-4 bg-white overflow-hidden">
        <div class="card-body p-3 p-md-4">
            <div class="row align-items-center g-3">
                <div class="col-12 col-md-7">
                    <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                        <h3 class="fw-bold text-dark mb-0">{{ $contact->name }}</h3>
                        <span class="badge bg-info-subtle text-info border border-info-subtle px-3 py-2 fs-6">
                            Khata #{{ $contact->khata_number }}
                        </span>
                    </div>
                    <div class="d-flex flex-wrap align-items-center gap-3 text-muted small">
                        <span><i class="bi bi-telephone text-info me-1"></i> {{ $contact->phoneNumbers->first()->phone_number ?? 'No Phone' }}</span>
                        <span><i class="bi bi-wallet2 text-info me-1"></i> Opening: ₹{{ number_format($contact->opening_balance, 2) }} ({{ $contact->opening_balance_type }})</span>
                        @php
                            // $currentBalance is passed directly from controller (SQL aggregate, no PHP loop)
                        @endphp
                        <span class="fw-semibold {{ $currentBalance > 0 ? 'text-danger' : ($currentBalance < 0 ? 'text-success' : 'text-muted') }}">
                            <i class="bi bi-bar-chart-line me-1"></i>
                            Balance: ₹{{ number_format(abs($currentBalance), 2) }}
                            @if($currentBalance > 0) (Due)
                            @elseif($currentBalance < 0) (Advance)
                            @else (Clear)
                            @endif
                        </span>
                        @if($contact->address)
                            <span><i class="bi bi-geo-alt text-info me-1"></i> {{ $contact->address }}</span>
                        @endif
                    </div>
                </div>
                <div class="col-12 col-md-5 text-md-end">
                    <div class="d-flex gap-2 justify-content-start justify-content-md-end">
                        <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#downloadPdfModal">
                            <i class="bi bi-file-earmark-pdf me-1"></i> PDF Statement
                        </button>
                        @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('suppliers.manage'))
                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addTransactionModal">
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
            <div class="card border-0 shadow-sm mb-3 {{ $tx->status === 'VOID' ? 'bg-light text-muted' : '' }}">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <span class="small text-muted">{{ \Carbon\Carbon::parse($tx->transaction_date)->format('d M Y') }}</span>
                            <div>
                                @if($tx->transaction_type === 'PURCHASE')
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle">PURCHASE (+)</span>
                                @elseif($tx->transaction_type === 'SUPPLIER_PAYMENT')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">PAYMENT (-)</span>
                                @else
                                    <span class="badge bg-secondary">{{ $tx->transaction_type }}</span>
                                @endif
                                @if($tx->payment_mode)
                                    <span class="badge bg-light text-dark border ms-1">{{ $tx->payment_mode }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold fs-6">₹{{ number_format($tx->amount, 2) }}</div>
                        </div>
                    </div>
                    <p class="small text-dark mb-2 {{ $tx->status === 'VOID' ? 'text-decoration-line-through' : '' }}">{{ $tx->description }}</p>
                    <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                        <span class="small text-muted">Status: {{ $tx->status }}</span>
                        @if($tx->status !== 'VOID' && (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('transactions.void')))
                            <form method="POST" action="{{ route('transactions.void', $tx->id) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to void this transaction?')">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2" style="font-size: 12px;">Void</button>
                            </form>
                        @elseif($tx->status === 'VOID')
                            <span class="badge bg-secondary">VOIDED</span>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="card border-0 shadow-sm p-4 text-center text-muted">No transactions recorded yet.</div>
        @endforelse
    </div>
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
