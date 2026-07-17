<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use Illuminate\Support\Facades\Auth;
use App\Services\NotificationService;

class InventoryItemController extends Controller
{
    public function index()
    {
        return InventoryItem::with([
            'category'
        ])
            ->withCount([
                'assets'
            ])
            ->latest()
            ->get();
    }

    public function store(Request $request)
    {
        $validated =
            $request->validate([

                'category_id' =>
                'required|exists:inventory_categories,id',

                'name' =>
                'required|string|max:255',

                'model' =>
                'nullable|string|max:255',

                'serial_number' =>
                'nullable|string|max:255',

                'quantity' =>
                'required|integer|min:0',

                'minimum_quantity' =>
                'nullable|integer|min:0',

                'purchase_price' =>
                'nullable|numeric|min:0',

                'purchase_date' =>
                'nullable|date',

                'warranty_expiry' =>
                'nullable|date',

                'purchased_from' =>
                'nullable|string|max:255',

                'type' =>
                'required|in:asset,consumable',

                'track_serial' =>
                'nullable|boolean',

                'status' =>
                'required|in:available,maintenance,damaged,retired',

                'daily_rental_value' =>
                'nullable|numeric|min:0',

                'notes' =>
                'nullable|string',
            ]);

        $item =
            InventoryItem::create(
                $validated
            );

        return response()->json([
            'message' =>
            'Item created successfully',

            'data' =>
            $item,
        ]);
    }

    public function update(
        Request $request,
        InventoryItem $item
    ) {

        $validated = $request->validate([

            'category_id' => 'required|exists:inventory_categories,id',

            'name' => 'required|string|max:255',

            'model' => 'nullable|string|max:255',

            'serial_number' => 'nullable|string|max:255',

            'quantity' => 'required|integer|min:0',

            'minimum_quantity' => 'nullable|integer|min:0',

            'purchase_price' => 'nullable|numeric|min:0',

            'purchase_date' => 'nullable|date',

            'warranty_expiry' => 'nullable|date',

            'purchased_from' => 'nullable|string|max:255',

            'type' => 'required|in:asset,consumable',

            'track_serial' =>
            'nullable|boolean',

            'status' => 'required|in:available,maintenance,damaged,retired',

            'daily_rental_value' => 'nullable|numeric|min:0',

            'notes' => 'nullable|string',
        ]);

        $item->update($validated);

        return response()->json($item);
    }

    public function destroy(
        InventoryItem $item
    ) {
        if (
            $item->assets()
            ->exists()
        ) {

            return response()
                ->json([
                    'message' =>
                    'This item contains tracked assets and cannot be deleted.'
                ], 422);
        }

        $item->delete();

        return response()->json([
            'message' =>
            'Item deleted successfully'
        ]);
    }

    public function updateStock(
        Request $request,
        InventoryItem $item
    ) {

        $validated = $request->validate([
            'type' => 'required|in:in,out,adjustment',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        if (
            $validated['type'] === 'out'
            && $item->quantity < $validated['quantity']
        ) {

            return response()->json([
                'message' => 'Insufficient stock'
            ], 422);
        }

        if ($validated['type'] === 'in') {

            $item->increment(
                'quantity',
                $validated['quantity']
            );
        } elseif ($validated['type'] === 'out') {

            $item->decrement(
                'quantity',
                $validated['quantity']
            );
        } else {

            $item->quantity = $validated['quantity'];

            $item->save();
        }

        InventoryMovement::create([
            'item_id' => $item->id,
            'type' => $validated['type'],
            'quantity' => $validated['quantity'],
            'created_by' => Auth::user()->id,
            'notes' => $validated['notes'] ?? null,
        ]);

        if (
            $item->minimum_quantity > 0 &&
            $item->quantity <= $item->minimum_quantity
        ) {
            $notification = app(NotificationService::class);
            $notification->sendToPermission([
                'title' => 'Low Stock Alert',
                'message' => '"' . $item->name . '" is running low on stock (' . $item->quantity . ' remaining, minimum: ' . $item->minimum_quantity . ').',
                'module' => 'inventory',
                'type' => 'warning',
                'priority' => 'high',
                'action_url' => '/dashboard/inventory/items',
                'related_model' => 'InventoryItem',
                'related_id' => $item->id,
                'created_by' => Auth::user()->id,
            ], 'inventory.view');
        }

        return response()->json([
            'message' => 'Stock updated',
            'item' => $item
        ]);
    }
}
