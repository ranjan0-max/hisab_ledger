@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h3 class="fw-bold text-dark mb-1">Slow Query Monitoring</h3>
            <p class="text-muted small mb-0">Track slow execution SQL queries and optimize system response times</p>
        </div>
        @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('slow_query.manage'))
            <div>
                <form method="POST" action="{{ route('slow-queries.clear-all') }}" class="d-inline" onsubmit="return confirm('Are you sure you want to delete ALL slow query logs?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger me-2">
                        <i class="bi bi-trash me-1"></i> Clear All Logs
                    </button>
                </form>
                <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#settingsModal">
                    <i class="bi bi-gear me-1"></i> Threshold Settings
                </button>
            </div>
        @endif
    </div>

    <!-- Threshold Alert Card -->
    <div class="alert alert-info border-0 shadow-sm d-flex align-items-center mb-4">
        <i class="bi bi-speedometer fs-4 me-3 text-info"></i>
        <div>
            <strong>Current Slow Query Threshold:</strong> {{ $setting->threshold_ms }} ms
            <div class="small text-muted">Queries taking longer than {{ $setting->threshold_ms }} ms are automatically captured here.</div>
        </div>
    </div>

    <!-- Slow Query Log Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Executed At</th>
                            <th>Duration (ms)</th>
                            <th>Endpoint</th>
                            <th>Query Text</th>
                            <th>User</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($log->executed_at)->format('d M Y H:i:s') }}</td>
                                <td><span class="badge bg-danger fs-6">{{ $log->duration_ms }} ms</span></td>
                                <td><code>{{ $log->http_method }} {{ $log->endpoint }}</code></td>
                                <td class="text-truncate" style="max-width: 300px;" title="{{ $log->query_text }}">
                                    <code>{{ $log->query_text }}</code>
                                </td>
                                <td>{{ $log->user->username ?? 'Guest/System' }}</td>
                                <td class="text-end">
                                    @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('slow_query.manage'))
                                        <form method="POST" action="{{ route('slow-queries.destroy', $log->id) }}" class="d-inline" onsubmit="return confirm('Delete this log entry?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2" style="font-size: 12px;">Delete</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No slow queries detected. System is running fast! 🚀</td>
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

<!-- Settings Modal -->
<div class="modal fade" id="settingsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('slow-queries.settings') }}">
                @csrf
                @method('PATCH')
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Slow Query Settings</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Threshold (in milliseconds) *</label>
                        <input type="number" name="threshold_ms" class="form-control" value="{{ $setting->threshold_ms }}" required min="50">
                        <div class="form-text">Example: 500ms = 0.5 seconds.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Settings</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
