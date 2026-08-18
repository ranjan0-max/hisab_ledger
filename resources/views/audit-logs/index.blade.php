@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">Audit Logs</h3>
            <p class="text-muted small mb-0">System activity trail and data modification history</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('audit-logs.index') }}" class="row g-2">
                <div class="col-12 col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search by entity or IP..." value="{{ request('search') }}">
                </div>
                <div class="col-12 col-md-auto">
                    <button type="submit" class="btn btn-secondary w-100"><i class="bi bi-search me-1"></i> Search</button>
                </div>
                @if(request('search'))
                    <div class="col-12 col-md-auto">
                        <a href="{{ route('audit-logs.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
                    </div>
                @endif
            </form>
        </div>
    </div>

    <!-- Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Timestamp</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Entity</th>
                            <th>IP Address</th>
                            <th class="text-end">Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td>{{ $log->created_at->format('d M Y H:i:s') }}</td>
                                <td class="fw-semibold">{{ $log->user->username ?? 'System' }}</td>
                                <td><span class="badge bg-primary-subtle text-primary border border-primary-subtle">{{ $log->action }}</span></td>
                                <td>{{ $log->entity_type }} #{{ $log->entity_id ?? 'N/A' }}</td>
                                <td><code>{{ $log->ip_address }}</code></td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#viewLogModal{{ $log->id }}">
                                        <i class="bi bi-eye me-1"></i> View Changes
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No audit logs found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($logs->hasPages())
            <div class="card-footer bg-white border-top py-3">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Log Details Modals -->
@foreach($logs as $log)
<div class="modal fade" id="viewLogModal{{ $log->id }}" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Audit Log Details #{{ $log->id }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-6">
                        <strong>User:</strong> {{ $log->user->username ?? 'System' }}
                    </div>
                    <div class="col-6">
                        <strong>Action:</strong> {{ $log->action }}
                    </div>
                </div>

                <h6 class="fw-bold text-danger">Old Values</h6>
                <pre class="bg-light p-2 rounded small"><code>{{ json_encode($log->old_values, JSON_PRETTY_PRINT) }}</code></pre>

                <h6 class="fw-bold text-success mt-3">New Values</h6>
                <pre class="bg-light p-2 rounded small"><code>{{ json_encode($log->new_values, JSON_PRETTY_PRINT) }}</code></pre>
            </div>
        </div>
    </div>
</div>
@endforeach

@endsection
