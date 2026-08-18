<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $currentUser = auth()->user();
        $query = User::with(['role', 'client']);

        // Non-superadmin only sees users of their own client
        if (!$currentUser->isSuperAdmin()) {
            $query->where('client_id', $currentUser->client_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate(10)->withQueryString();
        $roles = Role::where('is_active', true)->get();
        $clients = $currentUser->isSuperAdmin() ? Client::where('is_active', true)->get() : collect();

        return view('users.index', compact('users', 'roles', 'clients'));
    }

    public function store(Request $request)
    {
        $currentUser = auth()->user();

        $validated = $request->validate([
            'username' => ['required', 'string', 'max:80', 'unique:users,username'],
            'password' => ['required', 'string', 'min:6'],
            'role_id'  => ['required', 'exists:roles,id'],
            'client_id'=> [$currentUser->isSuperAdmin() ? 'nullable' : 'required', 'exists:clients,id'],
            'address'  => ['nullable', 'string'],
            'is_active'=> ['boolean'],
        ]);

        User::create([
            'username'      => $validated['username'],
            'password_hash' => Hash::make($validated['password']),
            'role_id'       => $validated['role_id'],
            'client_id'     => $currentUser->isSuperAdmin() ? $request->client_id : $currentUser->client_id,
            'address'       => $validated['address'] ?? null,
            'is_active'     => $request->has('is_active'),
        ]);

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function update(Request $request, User $user)
    {
        $currentUser = auth()->user();

        // Non superadmin cannot edit user outside their client
        if (!$currentUser->isSuperAdmin() && $user->client_id !== $currentUser->client_id) {
            abort(403);
        }

        $validated = $request->validate([
            'username' => ['required', 'string', 'max:80', 'unique:users,username,' . $user->id],
            'password' => ['nullable', 'string', 'min:6'],
            'role_id'  => ['required', 'exists:roles,id'],
            'client_id'=> [$currentUser->isSuperAdmin() ? 'nullable' : 'required', 'exists:clients,id'],
            'address'  => ['nullable', 'string'],
            'is_active'=> ['boolean'],
        ]);

        $updateData = [
            'username'  => $validated['username'],
            'role_id'   => $validated['role_id'],
            'address'   => $validated['address'] ?? null,
            'is_active' => $request->has('is_active'),
        ];

        if ($currentUser->isSuperAdmin()) {
            $updateData['client_id'] = $request->client_id;
        }

        if (!empty($validated['password'])) {
            $updateData['password_hash'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }
}
