<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Receipt;
use App\Models\ReceiptItem;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ReceiptController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $query = Receipt::with(['client', 'createdBy'])
            ->withCount('items')
            ->withSum('items', 'quantity');

        if (!$user->isSuperAdmin()) {
            $query->where('client_id', $user->client_id);
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $receiptId = preg_replace('/\D+/', '', $search);

            $query->where(function ($builder) use ($search, $receiptId, $user) {
                $builder->where('customer_name', 'like', "%{$search}%");

                if ($receiptId !== '') {
                    $builder->orWhere('id', (int) $receiptId);
                }

                if ($user->isSuperAdmin()) {
                    $builder->orWhereHas('client', fn ($clientQuery) => $clientQuery
                        ->where('name', 'like', "%{$search}%"));
                }
            });
        }

        $receipts = $query->latest()->paginate(10)->withQueryString();

        return view('receipts.index', compact('receipts'));
    }

    public function create(Request $request): View
    {
        $user = $request->user();
        $selectedClientId = $user->isSuperAdmin()
            ? session('active_client_id')
            : $user->client_id;

        return view($request->boolean('modal') ? 'receipts.form-modal' : 'receipts.form', [
            'receipt' => null,
            'clients' => $this->availableClients($user),
            'warehouses' => $this->availableWarehouses($user),
            'selectedClientId' => $selectedClientId,
            'lines' => collect(),
        ]);
    }

    public function show(Request $request, Receipt $receipt): View
    {
        $this->authorizeReceipt($receipt, $request->user());
        $receipt->load(['client', 'createdBy', 'items.warehouseItem.warehouse']);

        return view($request->boolean('modal') ? 'receipts.show-modal' : 'receipts.show', compact('receipt'));
    }

    public function printReceipt(Request $request, Receipt $receipt): View
    {
        $this->authorizeReceipt($receipt, $request->user());
        $receipt->load(['items.warehouseItem.warehouse']);

        return view('receipts.print', compact('receipt'));
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $validated = $this->validateReceipt($request, $user);
        $clientId = $user->isSuperAdmin()
            ? (int) $validated['client_id']
            : (int) $user->client_id;

        $receipt = DB::transaction(function () use ($validated, $clientId, $user) {
            $lines = $this->normalizedLines($validated['items'], (int) $validated['warehouse_id']);
            $stockItems = $this->lockedStockItems($lines->pluck('warehouse_item_id'));
            $this->validateLineOwnershipAndStock($lines, $stockItems, $clientId);

            $receipt = Receipt::create([
                'client_id' => $clientId,
                'customer_name' => trim($validated['customer_name']),
                'created_by' => $user->id,
            ]);

            foreach ($lines as $index => $line) {
                $stockItem = $stockItems->get($line['warehouse_item_id']);
                $stockItem->update([
                    'quantity' => bcsub($stockItem->quantity, $line['quantity'], 3),
                ]);

                $receipt->items()->create([
                    'warehouse_item_id' => $line['warehouse_item_id'],
                    'quantity' => $line['quantity'],
                ]);
            }

            return $receipt;
        });

        $message = "Receipt {$receipt->number} created and stock updated successfully.";

        if ($request->ajax()) {
            session()->flash('success', $message);

            return response()->json(['redirect' => route('receipts.index')]);
        }

        return redirect()->route('receipts.index')->with('success', $message);
    }

    public function edit(Request $request, Receipt $receipt): View
    {
        $user = $request->user();
        $this->authorizeReceipt($receipt, $user);
        $receipt->load(['items.warehouseItem.warehouse']);

        return view($request->boolean('modal') ? 'receipts.form-modal' : 'receipts.form', [
            'receipt' => $receipt,
            'clients' => collect(),
            'warehouses' => Warehouse::where('client_id', $receipt->client_id)->orderBy('name')->get(),
            'selectedClientId' => $receipt->client_id,
            'lines' => $receipt->items,
        ]);
    }

    public function update(Request $request, Receipt $receipt)
    {
        $user = $request->user();
        $this->authorizeReceipt($receipt, $user);
        $validated = $this->validateReceipt($request, $user, $receipt);

        DB::transaction(function () use ($validated, $receipt) {
            $lockedReceipt = Receipt::whereKey($receipt->id)->lockForUpdate()->firstOrFail();
            $oldLines = ReceiptItem::where('receipt_id', $lockedReceipt->id)->get();
            $newLines = $this->normalizedLines($validated['items'], (int) $validated['warehouse_id']);

            $itemIds = $oldLines->pluck('warehouse_item_id')
                ->merge($newLines->pluck('warehouse_item_id'))
                ->unique()
                ->sort()
                ->values();
            $stockItems = $this->lockedStockItems($itemIds);
            $this->validateLineOwnershipAndStock($newLines, $stockItems, $lockedReceipt->client_id, $oldLines);

            $oldByItem = $oldLines->keyBy('warehouse_item_id');
            $newByItem = $newLines->keyBy('warehouse_item_id');

            foreach ($itemIds as $itemId) {
                $stockItem = $stockItems->get($itemId);
                $oldQuantity = $oldByItem->has($itemId) ? $this->decimal($oldByItem->get($itemId)->quantity) : '0.000';
                $newQuantity = $newByItem->has($itemId) ? $newByItem->get($itemId)['quantity'] : '0.000';
                $difference = bcsub($newQuantity, $oldQuantity, 3);

                $stockItem->update([
                    'quantity' => bcsub($stockItem->quantity, $difference, 3),
                ]);
            }

            $lockedReceipt->update(['customer_name' => trim($validated['customer_name'])]);
            $lockedReceipt->items()->delete();

            foreach ($newLines as $line) {
                $lockedReceipt->items()->create([
                    'warehouse_item_id' => $line['warehouse_item_id'],
                    'quantity' => $line['quantity'],
                ]);
            }
        });

        $message = "Receipt {$receipt->number} updated and stock adjusted successfully.";

        if ($request->ajax()) {
            session()->flash('success', $message);

            return response()->json(['redirect' => route('receipts.index')]);
        }

        return redirect()->route('receipts.index')->with('success', $message);
    }

    public function destroy(Request $request, Receipt $receipt)
    {
        $user = $request->user();
        $this->authorizeReceipt($receipt, $user);
        $receiptNumber = $receipt->number;

        DB::transaction(function () use ($receipt) {
            $lockedReceipt = Receipt::whereKey($receipt->id)->lockForUpdate()->firstOrFail();
            $lines = ReceiptItem::where('receipt_id', $lockedReceipt->id)->get();
            $stockItems = $this->lockedStockItems($lines->pluck('warehouse_item_id'));

            foreach ($lines as $line) {
                $stockItem = $stockItems->get($line->warehouse_item_id);
                $stockItem->update([
                    'quantity' => bcadd($stockItem->quantity, $line->quantity, 3),
                ]);
            }

            $lockedReceipt->delete();
        });

        return redirect()->route('receipts.index')->with('success', "Receipt {$receiptNumber} deleted and stock restored successfully.");
    }

    public function searchItems(Request $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'warehouse_id' => ['required', 'integer', 'exists:warehouses,id'],
            'search' => ['nullable', 'string', 'max:150'],
        ]);

        $warehouse = Warehouse::findOrFail($validated['warehouse_id']);
        abort_if(!$user->isSuperAdmin() && $warehouse->client_id !== $user->client_id, 403);

        $items = WarehouseItem::where('warehouse_id', $warehouse->id)
            ->where('quantity', '>', 0)
            ->when($request->filled('search'), fn ($query) => $query
                ->where('item_name', 'like', '%'.trim((string) $request->input('search')).'%'))
            ->orderBy('item_name')
            ->limit(20)
            ->get(['id', 'item_name', 'quantity']);

        return response()->json($items->map(fn (WarehouseItem $item) => [
            'id' => $item->id,
            'text' => $item->item_name,
            'quantity' => $item->quantity,
        ]));
    }

    private function validateReceipt(Request $request, User $user, ?Receipt $receipt = null): array
    {
        $warehouseRules = ['required', 'integer', 'exists:warehouses,id'];

        if ($receipt) {
            $originalWarehouseIds = $receipt->items()
                ->with('warehouseItem:id,warehouse_id')
                ->get()
                ->pluck('warehouseItem.warehouse_id')
                ->unique();

            if ($originalWarehouseIds->count() !== 1) {
                throw ValidationException::withMessages([
                    'warehouse_id' => 'This receipt does not have a single original warehouse and cannot be updated.',
                ]);
            }

            $warehouseRules[] = Rule::in([(int) $originalWarehouseIds->first()]);
        }

        return $request->validate([
            'client_id' => [
                $user->isSuperAdmin() && !$receipt ? 'required' : 'nullable',
                'integer',
                Rule::exists('clients', 'id')->where(fn ($query) => $query->where('is_active', true)),
            ],
            'customer_name' => ['required', 'string', 'max:150'],
            'warehouse_id' => $warehouseRules,
            'items' => ['required', 'array', 'min:1'],
            'items.*.warehouse_item_id' => ['required', 'integer', 'distinct', 'exists:items_in_warehouses,id'],
            'items.*.quantity' => ['required', 'numeric', 'decimal:0,3', 'min:0.001', 'max:999999999999.999'],
        ]);
    }

    private function normalizedLines(array $items, int $warehouseId): Collection
    {
        return collect($items)->map(fn (array $line) => [
            'warehouse_id' => $warehouseId,
            'warehouse_item_id' => (int) $line['warehouse_item_id'],
            'quantity' => $this->decimal($line['quantity']),
        ]);
    }

    private function lockedStockItems(Collection $itemIds): Collection
    {
        return WarehouseItem::with('warehouse')
            ->whereIn('id', $itemIds)
            ->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->keyBy('id');
    }

    private function validateLineOwnershipAndStock(
        Collection $newLines,
        Collection $stockItems,
        int $clientId,
        ?Collection $oldLines = null,
    ): void {
        $oldByItem = ($oldLines ?? collect())->keyBy('warehouse_item_id');

        foreach ($newLines as $index => $line) {
            $stockItem = $stockItems->get($line['warehouse_item_id']);

            if (!$stockItem || $stockItem->warehouse_id !== $line['warehouse_id']) {
                throw ValidationException::withMessages([
                    "items.{$index}.warehouse_item_id" => 'The selected item does not belong to this warehouse.',
                ]);
            }

            if ($stockItem->warehouse->client_id !== $clientId) {
                throw ValidationException::withMessages([
                    "items.{$index}.warehouse_item_id" => 'The selected item does not belong to this client.',
                ]);
            }

            $oldQuantity = $oldByItem->has($stockItem->id)
                ? $this->decimal($oldByItem->get($stockItem->id)->quantity)
                : '0.000';
            $additionalQuantity = bcsub($line['quantity'], $oldQuantity, 3);

            if (bccomp($additionalQuantity, '0.000', 3) === 1
                && bccomp($stockItem->quantity, $additionalQuantity, 3) === -1) {
                throw ValidationException::withMessages([
                    "items.{$index}.quantity" => "Only {$stockItem->quantity} units of {$stockItem->item_name} are available in {$stockItem->warehouse->name}.",
                ]);
            }
        }
    }

    private function availableClients(User $user): Collection
    {
        return $user->isSuperAdmin()
            ? Client::where('is_active', true)->orderBy('name')->get()
            : collect();
    }

    private function availableWarehouses(User $user): Collection
    {
        return Warehouse::with('client')
            ->when(!$user->isSuperAdmin(), fn ($query) => $query->where('client_id', $user->client_id))
            ->orderBy('name')
            ->get();
    }

    private function authorizeReceipt(Receipt $receipt, User $user): void
    {
        abort_if(!$user->isSuperAdmin() && $receipt->client_id !== $user->client_id, 403);
    }

    private function decimal(mixed $quantity): string
    {
        return bcadd((string) $quantity, '0', 3);
    }
}
