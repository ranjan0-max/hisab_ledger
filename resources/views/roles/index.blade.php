@extends('layouts.app')

@section('content')
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
    <div class="card border-0 shadow-sm">
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
</div>

<!-- Add Role Modal -->
<div class="modal fade" id="addRoleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="{{ route('roles.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Create New Role</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Role Name *</label>
                        <input type="text" name="name" class="form-control" required placeholder="e.g. Manager, Accountant">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>

                    <h6 class="fw-bold mt-4 mb-3">Assign Permissions</h6>
                    @foreach($permissions as $module => $modulePermissions)
                        <div class="card mb-3 border">
                            <div class="card-header bg-light fw-bold py-2 text-capitalize">{{ $module }} Module</div>
                            <div class="card-body">
                                <div class="row g-2">
                                    @foreach($modulePermissions as $perm)
                                        <div class="col-12 col-md-6">
                                            <div class="form-check">
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
<div class="modal fade" id="editRoleModal{{ $role->id }}" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="{{ route('roles.update', $role->id) }}">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Edit Role</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Role Name *</label>
                        <input type="text" name="name" class="form-control" value="{{ $role->name }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2">{{ $role->description }}</textarea>
                    </div>

                    <h6 class="fw-bold mt-4 mb-3">Assign Permissions</h6>
                    @php $assignedPerms = $role->permissions->pluck('id')->toArray(); @endphp
                    @foreach($permissions as $module => $modulePermissions)
                        <div class="card mb-3 border">
                            <div class="card-header bg-light fw-bold py-2 text-capitalize">{{ $module }} Module</div>
                            <div class="card-body">
                                <div class="row g-2">
                                    @foreach($modulePermissions as $perm)
                                        <div class="col-12 col-md-6">
                                            <div class="form-check">
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
