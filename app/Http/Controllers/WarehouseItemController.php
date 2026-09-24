<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WarehouseItemController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = WarehouseItem::with(['warehouse.client']);

        if (!$user->isSuperAdmin()) {
            $query->whereHas('warehouse', fn ($warehouseQuery) => $warehouseQuery
                ->where('client_id', $user->client_id));
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($builder) use ($search) {
                $builder->where('item_name', 'like', "%{$search}%")
                    ->orWhereHas('warehouse', fn ($warehouseQuery) => $warehouseQuery
                        ->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->integer('warehouse_id'));
        }

        $items = $query->latest()->paginate(10)->withQueryString();
        $warehouses = $this->availableWarehouses($user);

        return view('warehouse-items.index', compact('items', 'warehouses'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateItem($request, $request->user());

        WarehouseItem::create([
            'warehouse_id' => $validated['warehouse_id'],
            'item_name' => trim($validated['item_name']),
            'quantity' => $validated['quantity'],
        ]);

        return redirect()->route('warehouse-items.index')->with('success', 'Item created successfully.');
    }

    public function printList(Request $request)
    {
        $user = $request->user();
        $warehouseExists = Rule::exists('warehouses', 'id');

        if (!$user->isSuperAdmin()) {
            $warehouseExists->where(fn ($query) => $query->where('client_id', $user->client_id));
        }

        $validated = $request->validate([
            'warehouse_id' => ['required', 'integer', $warehouseExists],
        ]);

        $warehouse = Warehouse::with('client')->findOrFail($validated['warehouse_id']);
        $this->authorizeWarehouse($warehouse, $user);

        $items = $warehouse->items()
            ->orderBy('item_name')
            ->get();

        return view('warehouse-items.print', compact('warehouse', 'items'));
    }

    public function update(Request $request, WarehouseItem $warehouseItem)
    {
        $user = $request->user();
        $warehouseItem->loadMissing('warehouse');
        $this->authorizeWarehouse($warehouseItem->warehouse, $user);

        $validated = $this->validateItem($request, $user, $warehouseItem);

        $warehouseItem->update([
            'item_name' => trim($validated['item_name']),
            'quantity' => $validated['quantity'],
        ]);

        return redirect()->route('warehouse-items.index')->with('success', 'Item updated successfully.');
    }

    private function validateItem(Request $request, User $user, ?WarehouseItem $warehouseItem = null): array
    {
        $warehouseId = $warehouseItem?->warehouse_id ?? (int) $request->input('warehouse_id');
        $rules = [
            'item_name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('items_in_warehouses', 'item_name')
                    ->where(fn ($query) => $query->where('warehouse_id', $warehouseId))
                    ->ignore($warehouseItem?->id),
            ],
            'quantity' => ['required', 'numeric', 'min:0', 'max:999999999999.999'],
        ];

        if (!$warehouseItem) {
            $warehouseExists = Rule::exists('warehouses', 'id');

            if (!$user->isSuperAdmin()) {
                $warehouseExists->where(fn ($query) => $query->where('client_id', $user->client_id));
            }

            $rules['warehouse_id'] = ['required', 'integer', $warehouseExists];
        }

        return $request->validate($rules);
    }

    private function availableWarehouses(User $user): Collection
    {
        return Warehouse::with('client')
            ->when(!$user->isSuperAdmin(), fn ($query) => $query->where('client_id', $user->client_id))
            ->orderBy('name')
            ->get();
    }

    private function authorizeWarehouse(Warehouse $warehouse, User $user): void
    {
        abort_if(!$user->isSuperAdmin() && $warehouse->client_id !== $user->client_id, 403);
    }
}
