<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use Illuminate\Support\Facades\Auth;

class InventoryItemController extends Controller
{
    public function index()
    {
        return InventoryItem::with('category')
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

        return response()->json([
            'message' => 'Stock updated',
            'item' => $item
        ]);
    }
}
