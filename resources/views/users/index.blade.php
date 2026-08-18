@extends('layouts.app')

@section('content')
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
            <form method="GET" action="{{ route('users.index') }}" class="row g-2">
                <div class="col-12 col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search username or address..." value="{{ request('search') }}">
                </div>
                <div class="col-12 col-md-auto">
                    <button type="submit" class="btn btn-secondary w-100"><i class="bi bi-search me-1"></i> Search</button>
                </div>
                @if(request('search'))
                    <div class="col-12 col-md-auto">
                        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
                    </div>
                @endif
            </form>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card border-0 shadow-sm">
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
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('users.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Add New User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Username *</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password *</label>
                        <input type="password" name="password" class="form-control" required minlength="6">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role *</label>
                        <select name="role_id" class="form-select" required>
                            <option value="">-- Select Role --</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if(auth()->user()->isSuperAdmin())
                        <div class="mb-3">
                            <label class="form-label">Assign Client / Shop (Optional for SuperAdmin)</label>
                            <select name="client_id" class="form-select">
                                <option value="">-- None (SuperAdmin) --</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}">{{ $client->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="addUserActive" checked>
                        <label class="form-check-label" for="addUserActive">Active</label>
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
<div class="modal fade" id="editUserModal{{ $user->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('users.update', $user->id) }}">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Username *</label>
                        <input type="text" name="username" class="form-control" value="{{ $user->username }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Password <span class="text-muted">(leave blank to keep unchanged)</span></label>
                        <input type="password" name="password" class="form-control" minlength="6">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role *</label>
                        <select name="role_id" class="form-select" required>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}" {{ $user->role_id == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if(auth()->user()->isSuperAdmin())
                        <div class="mb-3">
                            <label class="form-label">Assign Client / Shop</label>
                            <select name="client_id" class="form-select">
                                <option value="" {{ is_null($user->client_id) ? 'selected' : '' }}>-- None (SuperAdmin) --</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}" {{ $user->client_id == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control" rows="2">{{ $user->address }}</textarea>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="editUserActive{{ $user->id }}" {{ $user->is_active ? 'checked' : '' }}>
                        <label class="form-check-label" for="editUserActive{{ $user->id }}">Active</label>
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
