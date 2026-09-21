@extends('layouts.app')

@section('content')
<style>
    @media (max-width: 767.98px) {
        .slow-query-actions {
            display: flex;
            gap: 0.5rem;
            width: 100%;
        }

        .slow-query-actions form,
        .slow-query-actions .btn {
            flex: 1 1 0;
            min-width: 0;
        }

        .slow-query-actions form .btn {
            width: 100%;
        }

        .slow-query-mobile-card {
            border: 1px solid #e2e8f0 !important;
            border-radius: 14px !important;
            background: #ffffff;
            box-shadow: 0 4px 14px rgba(15, 23, 42, 0.05) !important;
        }

        .slow-query-mobile-card .card-body {
            padding: 1rem !important;
        }

        .slow-query-time {
            color: #94a3b8;
            font-size: 0.68rem;
        }

        .slow-query-duration {
            display: inline-flex;
            flex: 0 0 auto;
            padding: 0.22rem 0.5rem;
            border: 1px solid #fecaca;
            border-radius: 999px;
            color: #b91c1c;
            background: #fef2f2;
            font-size: 0.68rem;
            font-weight: 700;
        }

        .slow-query-endpoint {
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
            margin-top: 0.75rem;
            padding-top: 0.7rem;
            border-top: 1px solid #eef2f7;
        }

        .slow-query-method {
            flex: 0 0 auto;
            padding: 0.18rem 0.4rem;
            border: 1px solid #c7d2fe;
            border-radius: 5px;
            color: #4338ca;
            background: #eef2ff;
            font-size: 0.62rem;
            font-weight: 700;
        }

        .slow-query-path {
            min-width: 0;
            overflow-wrap: anywhere;
            color: #475569;
            font-size: 0.74rem;
            font-weight: 600;
        }

        .slow-query-code {
            display: -webkit-box;
            overflow: hidden;
            margin-top: 0.65rem;
            padding: 0.65rem;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            color: #64748b;
            background: #f8fafc;
            font-family: var(--bs-font-monospace);
            font-size: 0.68rem;
            line-height: 1.45;
            overflow-wrap: anywhere;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 3;
        }

        .slow-query-card-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            margin-top: 0.7rem;
        }

        .slow-query-user {
            min-width: 0;
            overflow: hidden;
            color: #64748b;
            font-size: 0.72rem;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .slow-query-delete {
            flex: 0 0 auto;
            border-color: #fecaca;
            border-radius: 8px;
            color: #b91c1c;
            background: #fef2f2;
            font-size: 0.72rem;
            font-weight: 600;
        }
    }
</style>
<div class="container-fluid">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h3 class="fw-bold text-dark mb-1">Slow Query Monitoring</h3>
            <p class="text-muted small mb-0">Track slow execution SQL queries and optimize system response times</p>
        </div>
        @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('slow_query.manage'))
            <div class="slow-query-actions">
                <form method="POST" action="{{ route('slow-queries.clear-all') }}" onsubmit="return confirm('Are you sure you want to delete ALL slow query logs?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger">
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
    <div class="card border-0 shadow-sm d-none d-md-block">
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

    <!-- Mobile View (Cards) -->
    <div class="d-md-none">
        @forelse($logs as $log)
            <div class="card slow-query-mobile-card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start gap-3">
                        <span class="slow-query-time">
                            <i class="bi bi-clock me-1"></i>{{ \Carbon\Carbon::parse($log->executed_at)->format('d M Y, H:i:s') }}
                        </span>
                        <span class="slow-query-duration">{{ $log->duration_ms }} ms</span>
                    </div>

                    <div class="slow-query-endpoint">
                        <span class="slow-query-method">{{ $log->http_method }}</span>
                        <span class="slow-query-path">{{ $log->endpoint ?: 'Unknown endpoint' }}</span>
                    </div>

                    <div class="slow-query-code" title="{{ $log->query_text }}">{{ $log->query_text }}</div>

                    <div class="slow-query-card-footer">
                        <span class="slow-query-user">
                            <i class="bi bi-person me-1"></i>{{ $log->user->username ?? 'Guest/System' }}
                        </span>
                        @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('slow_query.manage'))
                            <form method="POST" action="{{ route('slow-queries.destroy', $log->id) }}" onsubmit="return confirm('Delete this log entry?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm slow-query-delete">
                                    <i class="bi bi-trash me-1"></i> Delete
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="card border-0 shadow-sm p-4 text-center text-muted">
                No slow queries detected. System is running fast!
            </div>
        @endforelse

        @if($logs->hasPages())
            <div class="py-3">
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
