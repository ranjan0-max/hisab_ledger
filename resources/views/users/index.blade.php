@extends('layouts.app')

@section('content')
<style>
    @media (max-width: 767.98px) {
        .user-mobile-card {
            border: 1px solid #e2e8f0 !important;
            border-radius: 14px !important;
            background: #ffffff;
            box-shadow: 0 4px 14px rgba(15, 23, 42, 0.05) !important;
        }

        .user-mobile-card .card-body {
            padding: 1rem !important;
        }

        .user-mobile-card .min-w-0 {
            min-width: 0;
        }

        .user-avatar {
            display: inline-flex;
            flex: 0 0 40px;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border: 1px solid #c7d2fe;
            border-radius: 50%;
            color: #4f46e5;
            background: #eef2ff;
            font-size: 0.9rem;
            font-weight: 700;
        }

        .user-card-name {
            color: #0f172a;
            font-size: 1rem;
            line-height: 1.25;
        }

        .user-card-role {
            display: inline-flex;
            padding: 0.16rem 0.42rem;
            border: 1px solid #e0e7ff;
            border-radius: 6px;
            color: #4f46e5;
            background: #f5f3ff;
            font-size: 0.78rem;
            font-weight: 600;
        }

        .user-status-pill {
            display: inline-flex;
            flex: 0 0 auto;
            align-items: center;
            gap: 0.35rem;
            padding: 0.25rem 0.5rem;
            border: 1px solid transparent;
            border-radius: 999px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .user-status-pill::before {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            content: '';
            background: currentColor;
        }

        .user-status-active {
            border-color: #bbf7d0;
            color: #15803d;
            background: #f0fdf4;
        }

        .user-status-inactive {
            border-color: #fecaca;
            color: #b91c1c;
            background: #fef2f2;
        }

        .user-details {
            margin-top: 0.9rem;
            padding-top: 0.75rem;
            border-top: 1px solid #eef2f7;
        }

        .user-detail-row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            padding: 0.3rem 0;
            font-size: 0.78rem;
        }

        .user-detail-label {
            flex: 0 0 auto;
            color: #94a3b8;
        }

        .user-detail-label i {
            color: #6366f1;
        }

        .user-detail-value {
            overflow-wrap: anywhere;
            color: #334155;
            font-weight: 600;
            text-align: right;
        }

        .user-card-actions {
            margin-top: 0.65rem;
            text-align: right;
        }

        .user-edit-button {
            border-color: #c7d2fe;
            border-radius: 8px;
            color: #4338ca;
            background: #eef2ff;
            font-size: 0.78rem;
            font-weight: 600;
        }

        .user-form-modal .modal-dialog {
            height: calc(100vh - 0.75rem) !important;
            height: calc(100dvh - 0.75rem) !important;
            max-height: calc(100vh - 0.75rem) !important;
            max-height: calc(100dvh - 0.75rem) !important;
            min-height: 0 !important;
        }

        .user-form-modal .modal-content,
        .user-form-modal .modal-content > form {
            height: 100% !important;
            max-height: 100% !important;
            min-height: 0 !important;
        }

        .user-form-modal .modal-content,
        .user-form-modal .modal-content > form {
            overflow: hidden !important;
        }

        .user-form-modal .modal-content > form {
            display: flex !important;
            flex-direction: column;
        }

        .user-form-modal .modal-header,
        .user-form-modal .modal-footer {
            flex: 0 0 auto;
        }

        .user-form-modal .modal-body {
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

        .user-form-modal .modal-footer {
            flex-wrap: nowrap;
            padding: 0.75rem 1rem;
        }

        .user-form-modal .modal-footer .btn {
            flex: 1 1 0;
        }
    }

    @media (min-width: 768px) {
        .user-form-modal .modal-dialog {
            max-width: min(900px, calc(100vw - 3rem));
        }

        .user-form-modal .modal-content {
            max-height: calc(100vh - 3.5rem);
            overflow: hidden;
        }

        .user-form-modal .modal-content > form {
            display: flex;
            flex-direction: column;
            min-height: 0;
            max-height: calc(100vh - 3.5rem);
        }

        .user-form-modal .modal-header,
        .user-form-modal .modal-footer {
            flex: 0 0 auto;
            padding-left: 2rem;
            padding-right: 2rem;
        }

        .user-form-modal .modal-body {
            flex: 1 1 auto;
            min-height: 0;
            overflow-x: hidden;
            overflow-y: auto;
            overscroll-behavior: contain;
            padding: 1.5rem 2rem;
        }
    }
</style>
<div class="container-fluid">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h3 class="fw-bold text-dark mb-1">Users</h3>
            <p class="text-muted small mb-0">Manage staff accounts and permissions</p>
        </div>
        @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('users.manage'))
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                <i class="bi bi-person-plus me-1"></i> Add User
            </button>
        @endif
    </div>

    <!-- Search & Filter -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('users.index') }}" class="row g-2 align-items-center">
                <div class="col-12 col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search username or address..." value="{{ request('search') }}">
                </div>
                <div class="col-6 col-md-auto">
                    <button type="submit" class="btn btn-secondary w-100"><i class="bi bi-search me-1"></i> Search</button>
                </div>
                @if(request('search'))
                    <div class="col-6 col-md-auto">
                        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary w-100"><i class="bi bi-x-lg me-1"></i> Reset</a>
                    </div>
                @endif
            </form>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card border-0 shadow-sm d-none d-md-block">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Username</th>
                            <th>Role</th>
                            @if(auth()->user()->isSuperAdmin())
                                <th>Client / Shop</th>
                            @endif
                            <th>Last Login</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td class="fw-bold">{{ $user->username }}</td>
                                <td><span class="badge bg-secondary">{{ $user->role->name ?? 'No Role' }}</span></td>
                                @if(auth()->user()->isSuperAdmin())
                                    <td>{{ $user->client->name ?? 'System (SuperAdmin)' }}</td>
                                @endif
                                <td>{{ $user->last_login_at ? $user->last_login_at->format('d M Y H:i') : 'Never' }}</td>
                                <td>
                                    @if($user->is_active)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">Active</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('users.manage'))
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editUserModal{{ $user->id }}">
                                            <i class="bi bi-pencil me-1"></i> Edit
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ auth()->user()->isSuperAdmin() ? 6 : 5 }}" class="text-center text-muted py-4">No users found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($users->hasPages())
            <div class="card-footer bg-white border-top py-3">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    <!-- Mobile View (Cards) -->
    <div class="d-md-none">
        @forelse($users as $user)
            <div class="card mobile-card user-mobile-card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <div class="d-flex align-items-center gap-3 min-w-0">
                            <div class="user-avatar" aria-hidden="true">
                                {{ Str::upper(Str::substr($user->username, 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <h5 class="user-card-name fw-bold mb-1 text-break">{{ $user->username }}</h5>
                                <div class="user-card-role">{{ $user->role->name ?? 'No Role' }}</div>
                            </div>
                        </div>
                        @if($user->is_active)
                            <span class="user-status-pill user-status-active">Active</span>
                        @else
                            <span class="user-status-pill user-status-inactive">Inactive</span>
                        @endif
                    </div>

                    <div class="user-details">
                        @if(auth()->user()->isSuperAdmin())
                            <div class="user-detail-row">
                                <span class="user-detail-label"><i class="bi bi-shop-window me-1"></i> Client</span>
                                <span class="user-detail-value">{{ $user->client->name ?? 'System (SuperAdmin)' }}</span>
                            </div>
                        @endif
                        <div class="user-detail-row">
                            <span class="user-detail-label"><i class="bi bi-clock-history me-1"></i> Last login</span>
                            <span class="user-detail-value">{{ $user->last_login_at ? $user->last_login_at->format('d M Y, H:i') : 'Never' }}</span>
                        </div>
                    </div>

                    @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('users.manage'))
                        <div class="user-card-actions">
                            <button class="btn btn-sm user-edit-button" data-bs-toggle="modal" data-bs-target="#editUserModal{{ $user->id }}">
                                <i class="bi bi-pencil me-1"></i> Edit User
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="card border-0 shadow-sm p-4 text-center text-muted">No users found.</div>
        @endforelse

        @if($users->hasPages())
            <div class="py-3">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade user-form-modal" id="addUserModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <form method="POST" action="{{ route('users.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Add New User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-lg-6">
                            <label class="form-label">Username *</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        <div class="col-12 col-lg-6">
                            <label class="form-label">Password *</label>
                            <input type="password" name="password" class="form-control" required minlength="6">
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-lg-6">
                            <label class="form-label">Role *</label>
                            <select name="role_id" class="form-select" required>
                                <option value="">-- Select Role --</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @if(auth()->user()->isSuperAdmin())
                            <div class="col-12 col-lg-6">
                                <label class="form-label">Assign Client / Shop <span class="text-muted">(Optional)</span></label>
                                <select name="client_id" class="form-select">
                                    <option value="">-- None (SuperAdmin) --</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}">{{ $client->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                    </div>
                    <div class="row g-3">
                        <div class="col-12 col-lg-9">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="col-12 col-lg-3 d-flex align-items-end">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="addUserActive" checked>
                                <label class="form-check-label" for="addUserActive">Active</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit User Modals -->
@foreach($users as $user)
<div class="modal fade user-form-modal" id="editUserModal{{ $user->id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <form method="POST" action="{{ route('users.update', $user->id) }}">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-lg-6">
                            <label class="form-label">Username *</label>
                            <input type="text" name="username" class="form-control" value="{{ $user->username }}" required>
                        </div>
                        <div class="col-12 col-lg-6">
                            <label class="form-label">New Password <span class="text-muted">(leave blank to keep unchanged)</span></label>
                            <input type="password" name="password" class="form-control" minlength="6">
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-lg-6">
                            <label class="form-label">Role *</label>
                            <select name="role_id" class="form-select" required>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}" {{ $user->role_id == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @if(auth()->user()->isSuperAdmin())
                            <div class="col-12 col-lg-6">
                                <label class="form-label">Assign Client / Shop</label>
                                <select name="client_id" class="form-select">
                                    <option value="" {{ is_null($user->client_id) ? 'selected' : '' }}>-- None (SuperAdmin) --</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}" {{ $user->client_id == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                    </div>
                    <div class="row g-3">
                        <div class="col-12 col-lg-9">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control" rows="3">{{ $user->address }}</textarea>
                        </div>
                        <div class="col-12 col-lg-3 d-flex align-items-end">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="editUserActive{{ $user->id }}" {{ $user->is_active ? 'checked' : '' }}>
                                <label class="form-check-label" for="editUserActive{{ $user->id }}">Active</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update User</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

@endsection
