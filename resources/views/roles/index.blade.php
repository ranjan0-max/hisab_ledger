@extends('layouts.app')

@section('content')
<style>
    .permission-module-card {
        overflow: hidden;
        border-color: #e2e8f0 !important;
        border-radius: 12px !important;
        box-shadow: none !important;
    }

    .permission-module-card .card-header {
        border-bottom-color: #e2e8f0;
        color: #334155;
        background: #f8fafc !important;
    }

    .permission-option {
        padding: 0.45rem 0.5rem 0.45rem 2rem;
        border-radius: 8px;
    }

    .permission-option:hover {
        background: #f8fafc;
    }

    @media (max-width: 767.98px) {
        .role-mobile-card {
            border: 1px solid #e2e8f0 !important;
            border-radius: 14px !important;
            background: #ffffff;
            box-shadow: 0 4px 14px rgba(15, 23, 42, 0.05) !important;
        }

        .role-mobile-card .card-body {
            padding: 1rem !important;
        }

        .role-mobile-card .min-w-0 {
            min-width: 0;
        }

        .role-icon {
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
        }

        .role-card-name {
            color: #0f172a;
            font-size: 1rem;
            line-height: 1.25;
        }

        .role-type {
            display: inline-flex;
            padding: 0.16rem 0.42rem;
            border: 1px solid transparent;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .role-type-system {
            border-color: #a5f3fc;
            color: #0e7490;
            background: #ecfeff;
        }

        .role-type-custom {
            border-color: #ddd6fe;
            color: #6d28d9;
            background: #f5f3ff;
        }

        .role-description {
            margin-top: 0.85rem;
            color: #64748b;
            font-size: 0.8rem;
            line-height: 1.5;
        }

        .role-card-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            margin-top: 0.85rem;
            padding-top: 0.75rem;
            border-top: 1px solid #eef2f7;
        }

        .role-permission-count {
            display: inline-flex;
            align-items: center;
            padding: 0.22rem 0.48rem;
            border: 1px solid #e0e7ff;
            border-radius: 6px;
            color: #4f46e5;
            background: #eef2ff;
            font-size: 0.78rem;
            font-weight: 600;
        }

        .role-edit-button {
            border-color: #c7d2fe;
            border-radius: 8px;
            color: #4338ca;
            background: #eef2ff;
            font-size: 0.78rem;
            font-weight: 600;
        }

        .role-form-modal .modal-dialog {
            height: calc(100vh - 0.75rem) !important;
            height: calc(100dvh - 0.75rem) !important;
            max-height: calc(100vh - 0.75rem) !important;
            max-height: calc(100dvh - 0.75rem) !important;
            min-height: 0 !important;
        }

        .role-form-modal .modal-content,
        .role-form-modal .modal-content > form {
            height: 100% !important;
            max-height: 100% !important;
            min-height: 0 !important;
            overflow: hidden !important;
        }

        .role-form-modal .modal-content > form {
            display: flex !important;
            flex-direction: column;
        }

        .role-form-modal .modal-header,
        .role-form-modal .modal-footer {
            flex: 0 0 auto;
        }

        .role-form-modal .modal-body {
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

        .role-form-modal .modal-footer {
            flex-wrap: nowrap;
            padding: 0.75rem 1rem;
        }

        .role-form-modal .modal-footer .btn {
            flex: 1 1 0;
        }

        .permission-module-card .card-body {
            padding: 0.75rem !important;
        }
    }

    @media (min-width: 768px) {
        .role-form-modal .modal-dialog {
            max-width: min(1000px, calc(100vw - 3rem));
        }

        .role-form-modal .modal-content {
            max-height: calc(100vh - 3.5rem);
            overflow: hidden;
        }

        .role-form-modal .modal-content > form {
            display: flex;
            flex-direction: column;
            min-height: 0;
            max-height: calc(100vh - 3.5rem);
        }

        .role-form-modal .modal-header,
        .role-form-modal .modal-footer {
            flex: 0 0 auto;
            padding-left: 2rem;
            padding-right: 2rem;
        }

        .role-form-modal .modal-body {
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
            <h3 class="fw-bold text-dark mb-1">Roles & Permissions</h3>
            <p class="text-muted small mb-0">Control staff access with granular permissions</p>
        </div>
        @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('roles.manage'))
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRoleModal">
                <i class="bi bi-plus-lg me-1"></i> Create Role
            </button>
        @endif
    </div>

    <!-- Roles Table -->
    <div class="card border-0 shadow-sm d-none d-md-block">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Role Name</th>
                            <th>Description</th>
                            <th>Permissions Count</th>
                            <th>Type</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($roles as $role)
                            <tr>
                                <td class="fw-bold">{{ $role->name }}</td>
                                <td>{{ $role->description ?? '—' }}</td>
                                <td><span class="badge bg-secondary">{{ $role->permissions->count() }} permissions</span></td>
                                <td>
                                    @if($role->is_system_role)
                                        <span class="badge bg-info-subtle text-info border border-info-subtle">System Role</span>
                                    @else
                                        <span class="badge bg-light text-dark border">Custom</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if((auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('roles.manage')) && !$role->is_system_role)
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editRoleModal{{ $role->id }}">
                                            <i class="bi bi-pencil me-1"></i> Edit
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No roles found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($roles->hasPages())
            <div class="card-footer bg-white border-top py-3">
                {{ $roles->links() }}
            </div>
        @endif
    </div>

    <!-- Mobile View (Cards) -->
    <div class="d-md-none">
        @forelse($roles as $role)
            <div class="card mobile-card role-mobile-card mb-3">
                <div class="card-body">
                    <div class="d-flex align-items-start gap-3">
                        <div class="role-icon" aria-hidden="true">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <div class="min-w-0 flex-grow-1">
                            <h5 class="role-card-name fw-bold mb-1 text-break">{{ $role->name }}</h5>
                            <div class="role-type {{ $role->is_system_role ? 'role-type-system' : 'role-type-custom' }}">
                                {{ $role->is_system_role ? 'System role' : 'Custom role' }}
                            </div>
                        </div>
                    </div>

                    <div class="role-description">
                        {{ $role->description ?? 'No description provided.' }}
                    </div>

                    <div class="role-card-footer">
                        <span class="role-permission-count">
                            <i class="bi bi-key me-1"></i>
                            {{ $role->permissions->count() }} {{ $role->permissions->count() === 1 ? 'permission' : 'permissions' }}
                        </span>
                        @if((auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('roles.manage')) && !$role->is_system_role)
                            <button class="btn btn-sm role-edit-button" data-bs-toggle="modal" data-bs-target="#editRoleModal{{ $role->id }}">
                                <i class="bi bi-pencil me-1"></i> Edit
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="card border-0 shadow-sm p-4 text-center text-muted">No roles found.</div>
        @endforelse

        @if($roles->hasPages())
            <div class="py-3">
                {{ $roles->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Add Role Modal -->
<div class="modal fade role-form-modal" id="addRoleModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <form method="POST" action="{{ route('roles.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Create New Role</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-lg-4">
                            <label class="form-label">Role Name *</label>
                            <input type="text" name="name" class="form-control" required placeholder="e.g. Manager, Accountant">
                        </div>
                        <div class="col-12 col-lg-8">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="2"></textarea>
                        </div>
                    </div>

                    <h6 class="fw-bold mt-4 mb-1">Assign Permissions</h6>
                    <p class="text-muted small mb-3">Select the actions users with this role are allowed to perform.</p>
                    @foreach($permissions as $module => $modulePermissions)
                        <div class="card permission-module-card mb-3 border">
                            <div class="card-header bg-light fw-bold py-2 text-capitalize">{{ $module }} Module</div>
                            <div class="card-body">
                                <div class="row g-2">
                                    @foreach($modulePermissions as $perm)
                                        <div class="col-12 col-md-6">
                                            <div class="form-check permission-option">
                                                <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $perm->id }}" id="perm{{ $perm->id }}">
                                                <label class="form-check-label small" for="perm{{ $perm->id }}">
                                                    {{ $perm->name }} <span class="text-muted">({{ $perm->key }})</span>
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Role</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Role Modals -->
@foreach($roles as $role)
@if(!$role->is_system_role)
<div class="modal fade role-form-modal" id="editRoleModal{{ $role->id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <form method="POST" action="{{ route('roles.update', $role->id) }}">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Edit Role</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-lg-4">
                            <label class="form-label">Role Name *</label>
                            <input type="text" name="name" class="form-control" value="{{ $role->name }}" required>
                        </div>
                        <div class="col-12 col-lg-8">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="2">{{ $role->description }}</textarea>
                        </div>
                    </div>

                    <h6 class="fw-bold mt-4 mb-1">Assign Permissions</h6>
                    <p class="text-muted small mb-3">Update the actions users with this role are allowed to perform.</p>
                    @php $assignedPerms = $role->permissions->pluck('id')->toArray(); @endphp
                    @foreach($permissions as $module => $modulePermissions)
                        <div class="card permission-module-card mb-3 border">
                            <div class="card-header bg-light fw-bold py-2 text-capitalize">{{ $module }} Module</div>
                            <div class="card-body">
                                <div class="row g-2">
                                    @foreach($modulePermissions as $perm)
                                        <div class="col-12 col-md-6">
                                            <div class="form-check permission-option">
                                                <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $perm->id }}" id="editPerm{{ $role->id }}_{{ $perm->id }}" {{ in_array($perm->id, $assignedPerms) ? 'checked' : '' }}>
                                                <label class="form-check-label small" for="editPerm{{ $role->id }}_{{ $perm->id }}">
                                                    {{ $perm->name }} <span class="text-muted">({{ $perm->key }})</span>
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Role</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endforeach

@endsection
