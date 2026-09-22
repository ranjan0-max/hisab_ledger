<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WarehouseController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Warehouse::with('client')->withCount('items');

        if (!$user->isSuperAdmin()) {
            $query->where('client_id', $user->client_id);
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($builder) use ($search, $user) {
                $builder->where('name', 'like', "%{$search}%");

                if ($user->isSuperAdmin()) {
                    $builder->orWhereHas('client', function ($clientQuery) use ($search) {
                        $clientQuery->where('name', 'like', "%{$search}%");
                    });
                }
            });
        }

        $warehouses = $query->latest()->paginate(10)->withQueryString();
        $clients = $user->isSuperAdmin()
            ? Client::where('is_active', true)->orderBy('name')->get()
            : collect();

        return view('warehouses.index', compact('warehouses', 'clients'));
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $clientId = $user->isSuperAdmin()
            ? (int) $request->input('client_id')
            : (int) $user->client_id;

        $validated = $request->validate([
            'client_id' => [$user->isSuperAdmin() ? 'required' : 'nullable', 'integer', 'exists:clients,id'],
            'name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('warehouses', 'name')->where(fn ($query) => $query->where('client_id', $clientId)),
            ],
        ]);

        Warehouse::create([
            'client_id' => $clientId,
            'name' => trim($validated['name']),
        ]);

        return redirect()->route('warehouses.index')->with('success', 'Warehouse created successfully.');
    }

    public function update(Request $request, Warehouse $warehouse)
    {
        $user = $request->user();
        $this->authorizeWarehouse($warehouse, $user);

        $clientId = $user->isSuperAdmin()
            ? (int) $request->input('client_id')
            : (int) $user->client_id;

        $validated = $request->validate([
            'client_id' => [$user->isSuperAdmin() ? 'required' : 'nullable', 'integer', 'exists:clients,id'],
            'name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('warehouses', 'name')
                    ->where(fn ($query) => $query->where('client_id', $clientId))
                    ->ignore($warehouse->id),
            ],
        ]);

        $warehouse->update([
            'client_id' => $clientId,
            'name' => trim($validated['name']),
        ]);

        return redirect()->route('warehouses.index')->with('success', 'Warehouse updated successfully.');
    }

    private function authorizeWarehouse(Warehouse $warehouse, User $user): void
    {
        abort_if(!$user->isSuperAdmin() && $warehouse->client_id !== $user->client_id, 403);
    }
}
