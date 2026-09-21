@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h3 class="fw-bold text-dark mb-1">Database Backups</h3>
            <p class="text-muted small mb-0">Manage manual & automated Google Drive backups of your MySQL database</p>
        </div>
        @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('backup.manage'))
            <form method="POST" action="{{ route('database-backups.run') }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-cloud-arrow-up me-1"></i> Trigger Backup Now
                </button>
            </form>
        @endif
    </div>

    <!-- Google Drive Status Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <i class="bi bi-google-drive fs-1 text-success me-3"></i>
                    <div>
                        <h5 class="fw-bold mb-1">Google Drive Connection</h5>
                        @if($driveSetting && $driveSetting->drive_email)
                            <div class="text-success small fw-semibold"><i class="bi bi-check-circle-fill me-1"></i> Connected to {{ $driveSetting->drive_email }}</div>
                        @else
                            <div class="text-muted small">Google Drive is not connected yet. Automated backups require a connected account.</div>
                        @endif
                    </div>
                </div>
                <div>
                    @if($driveSetting && $driveSetting->drive_email)
                        <form method="POST" action="{{ route('google-drive.disconnect') }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger btn-sm">Disconnect</button>
                        </form>
                    @else
                        <a href="{{ route('google-drive.connect') }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-google me-1"></i> Connect Google Drive
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Backup History Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="fw-bold mb-0">Backup Logs History</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Started At</th>
                            <th>File Name</th>
                            <th>Trigger Type</th>
                            <th>Status</th>
                            <th>Triggered By</th>
                            <th class="text-end">Drive Link</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($log->started_at)->format('d M Y H:i:s') }}</td>
                                <td><code>{{ $log->file_name }}</code></td>
                                <td><span class="badge bg-secondary">{{ $log->trigger_type }}</span></td>
                                <td>
                                    @if($log->status === 'SUCCESS')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">SUCCESS</span>
                                    @elseif($log->status === 'RUNNING')
                                        <span class="badge bg-warning-subtle text-dark border border-warning-subtle">RUNNING</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">FAILED</span>
                                    @endif
                                </td>
                                <td>{{ $log->triggeredBy->username ?? 'System Schedule' }}</td>
                                <td class="text-end">
                                    @if($log->drive_web_view_link)
                                        <a href="{{ $log->drive_web_view_link }}" target="_blank" class="btn btn-sm btn-outline-success">View Drive</a>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No database backups triggered yet.</td>
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
@endsection
