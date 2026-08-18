<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $query = Role::with('permissions');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
        }

        $roles = $query->paginate(10)->withQueryString();
        $permissions = Permission::all()->groupBy('module');

        return view('roles.index', compact('roles', 'permissions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:80', 'unique:roles,name'],
            'description' => ['nullable', 'string', 'max:255'],
            'permissions' => ['array'],
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_system_role' => false,
            'is_active' => true,
        ]);

        if (!empty($validated['permissions'])) {
            $role->permissions()->sync($validated['permissions']);
        }

        return redirect()->route('roles.index')->with('success', 'Role created successfully.');
    }

    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:80', 'unique:roles,name,' . $role->id],
            'description' => ['nullable', 'string', 'max:255'],
            'permissions' => ['array'],
        ]);

        $role->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        $role->permissions()->sync($validated['permissions'] ?? []);

        return redirect()->route('roles.index')->with('success', 'Role updated successfully.');
    }
}
