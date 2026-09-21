@extends('layouts.app')

@section('content')
<style>
    @media (max-width: 767.98px) {
        .daily-mobile-card {
            border: 1px solid #e2e8f0 !important;
            border-left: 1px solid #e2e8f0 !important;
            border-radius: 14px !important;
            background: #ffffff;
            box-shadow: 0 4px 14px rgba(15, 23, 42, 0.05) !important;
        }

        .daily-mobile-card:active {
            transform: none;
        }

        .daily-mobile-card .card-body {
            padding: 1rem !important;
        }

        .daily-mobile-card .min-w-0 {
            min-width: 0;
        }

        .daily-entry-void {
            opacity: 0.68;
        }

        .daily-entry-date {
            color: #94a3b8;
            font-size: 0.68rem;
        }

        .daily-entry-name {
            margin-top: 0.15rem;
            color: #0f172a;
            font-size: 0.95rem;
            line-height: 1.3;
        }

        .daily-entry-phone {
            margin-top: 0.2rem;
            color: #64748b;
            font-size: 0.72rem;
        }

        .daily-status {
            display: inline-flex;
            flex: 0 0 auto;
            padding: 0.2rem 0.5rem;
            border: 1px solid transparent;
            border-radius: 999px;
            font-size: 0.62rem;
            font-weight: 700;
            letter-spacing: 0.03em;
        }

        .daily-status-paid {
            border-color: #bbf7d0;
            color: #15803d;
            background: #f0fdf4;
        }

        .daily-status-partial {
            border-color: #fde68a;
            color: #a16207;
            background: #fffbeb;
        }

        .daily-status-unpaid {
            border-color: #fecaca;
            color: #b91c1c;
            background: #fef2f2;
        }

        .daily-status-advance {
            border-color: #a5f3fc;
            color: #0e7490;
            background: #ecfeff;
        }

        .daily-amount-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.55rem;
            margin-top: 0.8rem;
        }

        .daily-amount-item {
            min-width: 0;
            padding: 0.65rem 0.7rem;
            border: 1px solid transparent;
            border-radius: 10px;
        }

        .daily-amount-paid {
            border-color: #dcfce7;
            background: #f0fdf4;
        }

        .daily-amount-due {
            border-color: #fee2e2;
            background: #fef2f2;
        }

        .daily-amount-label {
            display: block;
            margin-bottom: 0.15rem;
            color: #94a3b8;
            font-size: 0.6rem;
            font-weight: 700;
            letter-spacing: 0.04em;
        }

        .daily-amount-value {
            display: block;
            overflow: hidden;
            font-size: 0.82rem;
            font-weight: 700;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .daily-card-actions {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 0.4rem;
            margin-top: 0.75rem;
            padding-top: 0.7rem;
            border-top: 1px solid #eef2f7;
        }

        .daily-card-action {
            border-radius: 8px;
            font-size: 0.74rem;
            font-weight: 600;
        }

        .daily-action-pay {
            border-color: #bbf7d0;
            color: #15803d;
            background: #f0fdf4;
        }

        .daily-action-history {
            border-color: #a5f3fc;
            color: #0e7490;
            background: #ecfeff;
        }

        .daily-action-edit {
            border-color: #c7d2fe;
            color: #4338ca;
            background: #eef2ff;
        }

        .daily-action-void {
            border-color: #fecaca;
            color: #b91c1c;
            background: #fef2f2;
        }

        .daily-payment-modal .modal-dialog {
            height: calc(100vh - 0.75rem) !important;
            height: calc(100dvh - 0.75rem) !important;
            max-height: calc(100vh - 0.75rem) !important;
            max-height: calc(100dvh - 0.75rem) !important;
            min-height: 0 !important;
        }

        .daily-payment-modal .modal-content,
        .daily-payment-modal .modal-content > form {
            height: 100% !important;
            max-height: 100% !important;
            min-height: 0 !important;
            overflow: hidden !important;
        }

        .daily-payment-modal .modal-content > form {
            display: flex !important;
            flex-direction: column;
        }

        .daily-payment-modal .modal-header,
        .daily-payment-modal .modal-footer {
            flex: 0 0 auto;
        }

        .daily-payment-modal .modal-body {
            flex: 1 1 auto;
            height: auto !important;
            max-height: none !important;
            min-height: 0 !important;
            overflow-x: hidden !important;
            overflow-y: auto !important;
            overscroll-behavior: contain;
            touch-action: pan-y;
            -webkit-overflow-scrolling: touch;
            padding: 1rem;
        }

        .daily-payment-modal .modal-footer {
            flex-wrap: nowrap;
            padding: 0.75rem 1rem;
        }

        .daily-payment-modal .modal-footer .btn {
            flex: 1 1 0;
            min-width: 0;
        }
    }
</style>
<div class="container-fluid">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-3 gap-2">
        <div>
            <h3 class="fw-bold text-dark mb-0">Daily Entries</h3>
        </div>
        <div class="d-flex gap-2 justify-content-start justify-content-sm-end">
            <button class="btn btn-sm btn-outline-danger" id="dailyPdfExportBtn" onclick="handlePdfExport(this, '{{ route('daily.index', array_merge(request()->all(), ['export' => 'pdf'])) }}')">
                <i class="bi bi-file-earmark-pdf me-1"></i> Export PDF
            </button>
            @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('daily.manage'))
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addEntryModal">
                    <i class="bi bi-plus-lg me-1"></i> New Entry
                </button>
            @endif
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('daily.index') }}" class="row g-2">
                <div class="col-12 col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search by customer name, mobile or description..." value="{{ request('search') }}">
                </div>
                <div class="col-12 col-md-4">
                    <select name="payment_status" class="form-select">
                        <option value="">-- All Payment Statuses --</option>
                        <option value="UNPAID" {{ request('payment_status') == 'UNPAID' ? 'selected' : '' }}>UNPAID</option>
                        <option value="PARTIAL" {{ request('payment_status') == 'PARTIAL' ? 'selected' : '' }}>PARTIAL</option>
                        <option value="PAID" {{ request('payment_status') == 'PAID' ? 'selected' : '' }}>PAID</option>
                        <option value="ADVANCE" {{ request('payment_status') == 'ADVANCE' ? 'selected' : '' }}>ADVANCE</option>
                    </select>
                </div>
                <div class="col-12 col-md-auto">
                    <button type="submit" class="btn btn-secondary w-100"><i class="bi bi-filter me-1"></i> Filter</button>
                </div>
                @if(request('search') || request('payment_status'))
                    <div class="col-12 col-md-auto">
                        <a href="{{ route('daily.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
                    </div>
                @endif
            </form>
        </div>
    </div>

    <!-- Desktop Table -->
    <div class="card border-0 shadow-sm d-none d-md-block">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Customer Name</th>
                            <th class="text-end">Paid</th>
                            <th class="text-end">Remaining</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($entries as $entry)
                            <tr class="{{ $entry->status === 'VOID' ? 'table-secondary text-decoration-line-through' : '' }}">
                                <td>{{ \Carbon\Carbon::parse($entry->entry_date)->format('d M Y') }}</td>
                                <td>
                                    <div class="fw-bold">{{ $entry->customer_name }}</div>
                                    @if($entry->mobile_number)
                                        <small class="text-muted"><i class="bi bi-telephone me-1"></i>{{ $entry->mobile_number }}</small>
                                    @endif
                                </td>
                                <td class="text-end text-success fw-bold">₹{{ number_format($entry->paid_amount, 2) }}</td>
                                <td class="text-end text-danger fw-bold">₹{{ number_format($entry->remaining_amount, 2) }}</td>
                                <td>
                                    @if($entry->payment_status === 'PAID')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">PAID</span>
                                    @elseif($entry->payment_status === 'PARTIAL')
                                        <span class="badge bg-warning-subtle text-dark border border-warning-subtle">PARTIAL</span>
                                    @elseif($entry->payment_status === 'UNPAID')
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">UNPAID</span>
                                    @else
                                        <span class="badge bg-info-subtle text-info border border-info-subtle">ADVANCE</span>
                                    @endif
                                </td>
                                <td class="text-end text-nowrap">
                                    @if($entry->status !== 'VOID' && (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('daily.manage')))
                                        <div class="btn-group btn-group-sm" role="group">
                                            @if($entry->remaining_amount > 0)
                                                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addPaymentModal{{ $entry->id }}" title="Receive Payment">
                                                    <i class="bi bi-cash-stack me-1"></i> Pay
                                                </button>
                                            @endif
                                            <button class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#historyModal{{ $entry->id }}" title="Payment History">
                                                <i class="bi bi-clock-history"></i>
                                            </button>
                                            <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editEntryModal{{ $entry->id }}" title="Edit Entry">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button type="submit" form="voidForm{{ $entry->id }}" class="btn btn-outline-danger" title="Void Entry">
                                                <i class="bi bi-slash-circle"></i>
                                            </button>
                                        </div>
                                        <form id="voidForm{{ $entry->id }}" method="POST" action="{{ route('daily.void', $entry->id) }}" class="d-none" onsubmit="return confirm('Void this daily entry?')">
                                            @csrf
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No daily entries found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($entries->hasPages())
            <div class="card-footer bg-white border-top py-3">
                {{ $entries->links() }}
            </div>
        @endif
    </div>

    <!-- Mobile Cards View for Daily Entries -->
    <div class="d-md-none">
        @forelse($entries as $entry)
            <div class="mobile-card card daily-mobile-card mb-3 {{ $entry->status === 'VOID' ? 'daily-entry-void' : '' }}">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start gap-3">
                        <div class="min-w-0">
                            <span class="daily-entry-date">
                                <i class="bi bi-calendar-event me-1"></i>{{ \Carbon\Carbon::parse($entry->entry_date)->format('d M Y') }}
                            </span>
                            <h5 class="daily-entry-name fw-bold mb-0 text-break">{{ $entry->customer_name }}</h5>
                            @if($entry->mobile_number)
                                <div class="daily-entry-phone"><i class="bi bi-telephone me-1"></i>{{ $entry->mobile_number }}</div>
                            @endif
                        </div>
                        @if($entry->payment_status === 'PAID')
                            <span class="daily-status daily-status-paid">PAID</span>
                        @elseif($entry->payment_status === 'PARTIAL')
                            <span class="daily-status daily-status-partial">PARTIAL</span>
                        @elseif($entry->payment_status === 'UNPAID')
                            <span class="daily-status daily-status-unpaid">UNPAID</span>
                        @else
                            <span class="daily-status daily-status-advance">ADVANCE</span>
                        @endif
                    </div>

                    <div class="daily-amount-grid">
                        <div class="daily-amount-item daily-amount-paid">
                            <span class="daily-amount-label">PAID</span>
                            <span class="daily-amount-value text-success">₹{{ number_format($entry->paid_amount, 2) }}</span>
                        </div>
                        <div class="daily-amount-item daily-amount-due">
                            <span class="daily-amount-label">DUE / REMAINING</span>
                            <span class="daily-amount-value text-danger">₹{{ number_format($entry->remaining_amount, 2) }}</span>
                        </div>
                    </div>

                    @if($entry->status !== 'VOID' && (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('daily.manage')))
                        <div class="daily-card-actions">
                            <button class="btn btn-sm daily-card-action daily-action-pay" data-bs-toggle="modal" data-bs-target="#addPaymentModal{{ $entry->id }}" title="Receive Payment">
                                <i class="bi bi-cash-stack me-1"></i> Pay
                            </button>
                            <button class="btn btn-sm daily-card-action daily-action-history" data-bs-toggle="modal" data-bs-target="#historyModal{{ $entry->id }}" title="Payment History">
                                <i class="bi bi-clock-history"></i>
                            </button>
                            <button class="btn btn-sm daily-card-action daily-action-edit" data-bs-toggle="modal" data-bs-target="#editEntryModal{{ $entry->id }}" title="Edit Entry">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form method="POST" action="{{ route('daily.void', $entry->id) }}" class="d-inline" onsubmit="return confirm('Void this daily entry?')">
                                @csrf
                                <button type="submit" class="btn btn-sm daily-card-action daily-action-void" title="Void Entry">
                                    <i class="bi bi-slash-circle"></i>
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="card border-0 shadow-sm p-4 text-center text-muted">No daily entries found.</div>
        @endforelse

        @if($entries->hasPages())
            <div class="py-3">
                {{ $entries->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addEntryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('daily.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">New Daily Entry</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label">Customer Name *</label>
                            <input type="text" name="customer_name" class="form-control" required placeholder="Walk-in / Name">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Mobile Number</label>
                            <input type="text" name="mobile_number" class="form-control" placeholder="10-digit mobile">
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label">Total Amount (₹) *</label>
                            <input type="number" step="0.01" name="total_amount" class="form-control" required placeholder="0.00">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Entry Date *</label>
                            <input type="date" name="entry_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description (Optional)</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Item names, quantity, bill notes... (optional)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Entry</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modals -->
@foreach($entries as $entry)
@if($entry->status !== 'VOID')
<div class="modal fade" id="editEntryModal{{ $entry->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('daily.update', $entry->id) }}">
                @csrf
                @method('PATCH')
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Edit Daily Entry</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label">Customer Name *</label>
                            <input type="text" name="customer_name" class="form-control" value="{{ $entry->customer_name }}" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Mobile Number</label>
                            <input type="text" name="mobile_number" class="form-control" value="{{ $entry->mobile_number }}" placeholder="Mobile">
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label">Total Amount (₹) *</label>
                            <input type="number" step="0.01" name="total_amount" class="form-control" value="{{ $entry->total_amount }}" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Entry Date *</label>
                            <input type="date" name="entry_date" class="form-control" value="{{ $entry->entry_date->format('Y-m-d') }}" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description (Optional)</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Item names, quantity, bill notes... (optional)">{{ $entry->description }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Entry</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Receive Installment Payment Modal -->
<div class="modal fade daily-payment-modal" id="addPaymentModal{{ $entry->id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <form method="POST" action="{{ route('daily.payments.store', $entry->id) }}">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title fw-bold"><i class="bi bi-cash-stack me-1"></i> Receive Payment</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-light border mb-3">
                        <div class="d-flex flex-column flex-sm-row justify-content-between gap-1">
                            <span>Customer: <strong>{{ $entry->customer_name }}</strong></span>
                            <span>Total Bill: <strong>₹{{ number_format($entry->total_amount, 2) }}</strong></span>
                        </div>
                        <div class="d-flex flex-column flex-sm-row justify-content-between gap-1 mt-1">
                            <span>Total Paid: <strong class="text-success">₹{{ number_format($entry->paid_amount, 2) }}</strong></span>
                            <span>Remaining Due: <strong class="text-danger">₹{{ number_format($entry->remaining_amount, 2) }}</strong></span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Amount (₹) *</label>
                        <input type="number" step="0.01" name="amount" class="form-control form-control-lg fw-bold text-success" max="{{ $entry->remaining_amount }}" placeholder="0.00" required>
                        <div class="form-text">Max installment amount: ₹{{ number_format($entry->remaining_amount, 2) }}</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Date *</label>
                        <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Mode</label>
                        <select name="payment_mode" class="form-select">
                            <option value="CASH">CASH</option>
                            <option value="ONLINE">ONLINE / UPI</option>
                            <option value="CHEQUE">CHEQUE</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes (Optional)</label>
                        <input type="text" name="notes" class="form-control" placeholder="e.g. Part payment via GPay">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success text-white"><i class="bi bi-check-circle me-1"></i> Save Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Payment History Modal -->
<div class="modal fade" id="historyModal{{ $entry->id }}" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info-subtle">
                <h5 class="modal-title fw-bold text-dark"><i class="bi bi-clock-history me-1"></i> Payment History - {{ $entry->customer_name }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="p-3 bg-light border-bottom">
                    <div class="row text-center">
                        <div class="col-4 border-end">
                            <small class="text-muted d-block">TOTAL BILL</small>
                            <strong class="fs-6 text-dark">₹{{ number_format($entry->total_amount, 2) }}</strong>
                        </div>
                        <div class="col-4 border-end">
                            <small class="text-muted d-block">TOTAL PAID</small>
                            <strong class="fs-6 text-success">₹{{ number_format($entry->paid_amount, 2) }}</strong>
                        </div>
                        <div class="col-4">
                            <small class="text-muted d-block">REMAINING DUE</small>
                            <strong class="fs-6 text-danger">₹{{ number_format($entry->remaining_amount, 2) }}</strong>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th class="text-end">Amount Received</th>
                                <th>Mode</th>
                                <th>Notes</th>
                                <th>Received By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($entry->payments as $pmt)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($pmt->payment_date)->format('d M Y') }}</td>
                                    <td class="text-end fw-bold text-success">₹{{ number_format($pmt->amount, 2) }}</td>
                                    <td><span class="badge bg-secondary-subtle text-dark">{{ $pmt->payment_mode }}</span></td>
                                    <td class="small text-muted">{{ $pmt->notes ?? '—' }}</td>
                                    <td class="small">{{ $pmt->createdBy->username ?? 'System' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-3">No payments recorded yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endif
@endforeach

@endsection
