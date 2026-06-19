<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\InventoryItem;
use App\Models\InventoryUsage;
use App\Models\Shoot;
use App\Models\InventoryMovement;
use Illuminate\Http\Request;
use App\Models\InventoryAsset;
use App\Models\AssetAllocation;
use Illuminate\Support\Facades\Auth;

class ShootInventoryController extends Controller
{
    /* ===================================== */
    /* INDEX */
    /* ===================================== */

    public function index(
        Shoot $shoot
    ) {

        $shoot->load([

            'inventoryUsages.item',

            'inventoryUsages.assignedUser',

            'inventoryUsages.asset',

        ]);

        return response()->json([

            'inventory' =>

            $shoot->inventoryUsages

        ]);
    }

    /* ===================================== */
    /* STORE */
    /* ===================================== */

    public function store(
        Request $request,
        Shoot $shoot
    ) {

        $validated =
            $request->validate([

                'inventory_item_id' =>
                'required|exists:inventory_items,id',

                'assigned_to' =>
                'nullable|exists:users,id',

                'quantity' =>
                'required|integer|min:1',

                'notes' =>
                'nullable|string',
            ]);

        $item = InventoryItem::findOrFail(
            $validated['inventory_item_id']
        );

        /* ===================================== */
        /* ACTIVE RESERVED */
        /* ===================================== */

        $activeAllocated =
            InventoryUsage::where(

                'inventory_item_id',

                $item->id

            )

            ->whereIn('status', [

                'reserved',

                'checked_out',

                'in_use',

                'partially_returned',

            ])

            ->get()

            ->sum(function ($usage) {

                return

                    $usage->quantity

                    -

                    $usage->returned_quantity

                    -

                    $usage->lost_quantity;
            });

        /* ===================================== */
        /* AVAILABLE */
        /* ===================================== */

        $available =

            $item->quantity

            -

            $activeAllocated;

        if (
            $validated['quantity']
            > $available
        ) {

            return response()->json([

                'message' =>
                'Insufficient stock available',

                'available_stock' =>
                $available,

            ], 422);
        }

        $usage = InventoryUsage::create([

            'inventory_item_id' =>
            $validated['inventory_item_id'],

            'usage_type' =>
            'shoot',

            'shoot_id' =>
            $shoot->id,

            'assigned_to' =>
            $validated['assigned_to'] ?? null,

            'quantity' =>
            $validated['quantity'],

            'returned_quantity' =>
            0,

            'damaged_quantity' =>
            0,

            'lost_quantity' =>
            0,

            'status' =>
            'reserved',

            'notes' =>
            $validated['notes'] ?? null,

            'created_by' =>
            Auth::id(),
        ]);

        /* ===================================== */
        /* MOVEMENT LOG */
        /* ===================================== */

        InventoryMovement::create([

            'item_id' =>
            $item->id,

            'type' => 'out',

            'quantity' =>
            $validated['quantity'],

            'source_type' => 'shoot',
            'source_id' => $usage->shoot_id,

            'created_by' =>
            Auth::id(),

            'notes' =>

            'Allocated to shoot: '

                .

                $shoot->title,
        ]);
        return response()->json([

            'message' =>
            'Inventory allocated successfully',

            'usage' =>
            $usage->load([
                'item',
                'assignedUser',
            ]),
        ]);
    }

    /* ===================================== */
    /* CHECKOUT */
    /* ===================================== */

    public function checkout(
        InventoryUsage $usage
    ) {

        $usage->update([

            'status' =>
            'checked_out',

            'checked_out_at' =>
            now(),
        ]);

        InventoryMovement::create([

            'item_id' =>
            $usage->inventory_item_id,

            'type' => 'out',

            'source_type' => 'shoot',
            'source_id' => $usage->shoot_id,
            'quantity' =>
            $usage->quantity,

            'created_by' =>
            Auth::id(),

            'notes' =>

            'Equipment checked out for shoot',
        ]);

        return response()->json([

            'message' =>
            'Equipment checked out',
        ]);
    }

    /* ===================================== */
    /* RETURN */
    /* ===================================== */

    public function processReturn(
        Request $request,
        InventoryUsage $usage
    ) {

        $validated =
            $request->validate([

                'returned_quantity' =>
                'nullable|integer|min:0',

                'lost_quantity' =>
                'nullable|integer|min:0',

                'damaged_quantity' =>
                'nullable|integer|min:0',

                'notes' =>
                'nullable|string',
            ]);

        $usage->returned_quantity +=
            $validated['returned_quantity'] ?? 0;

        $usage->lost_quantity +=
            $validated['lost_quantity'] ?? 0;

        $usage->damaged_quantity +=
            $validated['damaged_quantity'] ?? 0;

        $resolved =

            $usage->returned_quantity

            +

            $usage->lost_quantity;

        $remaining =

            $usage->quantity

            -

            $resolved;

        if ($remaining <= 0) {

            $usage->status =
                'returned';

            $usage->returned_at =
                now();
        } else {

            $usage->status =
                'partially_returned';
        }

        $usage->notes =
            $validated['notes']
            ??
            $usage->notes;

        $usage->save();

        /* ===================================== */
        /* QR ASSET SYNC */
        /* ===================================== */

        if ($usage->inventory_asset_id) {

            $asset = InventoryAsset::find(
                $usage->inventory_asset_id
            );

            if ($asset) {

                /*
            |--------------------------------------------------------------------------
            | Returned
            |--------------------------------------------------------------------------
            */

                if (
                    ($validated['returned_quantity'] ?? 0) > 0
                ) {

                    $asset->update([
                        'status' => 'available',
                    ]);

                    AssetAllocation::query()

                        ->where(
                            'inventory_asset_id',
                            $asset->id
                        )

                        ->where(
                            'status',
                            'allocated'
                        )

                        ->update([

                            'status' =>
                            'returned',

                            'returned_at' =>
                            now(),
                        ]);

                    $asset->logs()->create([

                        'user_id' =>
                        Auth::id(),

                        'action' =>
                        'returned',

                        'notes' =>
                        'Returned from shoot inventory page',
                    ]);
                }

                /*
            |--------------------------------------------------------------------------
            | Damaged
            |--------------------------------------------------------------------------
            */

                if (
                    ($validated['damaged_quantity'] ?? 0) > 0
                ) {

                    $asset->update([
                        'status' => 'damaged',
                    ]);

                    $asset->logs()->create([

                        'user_id' =>
                        Auth::id(),

                        'action' =>
                        'damage',

                        'notes' =>
                        'Damaged during shoot return',
                    ]);
                }

                /*
            |--------------------------------------------------------------------------
            | Lost / Written Off
            |--------------------------------------------------------------------------
            */

                if (
                    ($validated['lost_quantity'] ?? 0) > 0
                ) {

                    $asset->update([
                        'status' => 'written_off',
                    ]);

                    $asset->logs()->create([

                        'user_id' =>
                        Auth::id(),

                        'action' =>
                        'writeoff',

                        'notes' =>
                        'Lost during shoot',
                    ]);
                }
            }
        }

        /* ===================================== */
        /* RETURN MOVEMENT */
        /* ===================================== */

        if (
            ($validated['returned_quantity'] ?? 0) > 0
        ) {

            InventoryMovement::create([

                'item_id' =>
                $usage->inventory_item_id,

                'type' =>
                'return',

                'source_type' =>
                'shoot',

                'source_id' =>
                $usage->shoot_id,

                'quantity' =>
                $validated['returned_quantity'],

                'created_by' =>
                Auth::id(),

                'notes' =>

                $validated['notes']

                    ??

                    'Inventory returned from shoot',
            ]);
        }

        /* ===================================== */
        /* LOST MOVEMENT */
        /* ===================================== */

        if (
            ($validated['lost_quantity'] ?? 0) > 0
        ) {

            InventoryMovement::create([

                'item_id' =>
                $usage->inventory_item_id,

                'type' =>
                'loss',

                'source_type' =>
                'shoot',

                'source_id' =>
                $usage->shoot_id,

                'quantity' =>
                $validated['lost_quantity'],

                'created_by' =>
                Auth::id(),

                'notes' =>
                'Inventory lost during shoot',
            ]);
        }

        /* ===================================== */
        /* DAMAGED MOVEMENT */
        /* ===================================== */

        if (
            ($validated['damaged_quantity'] ?? 0) > 0
        ) {

            InventoryMovement::create([

                'item_id' =>
                $usage->inventory_item_id,

                'type' =>
                'damage',

                'quantity' =>
                $validated['damaged_quantity'],

                'created_by' =>
                Auth::id(),

                'notes' =>
                'Inventory damaged during shoot',
            ]);
        }

        return response()->json([

            'message' =>
            'Return processed',

            'usage' =>
            $usage->fresh(),
        ]);
    }

    /* ===================================== */
    /* DELETE */
    /* ===================================== */

    public function destroy(
        InventoryUsage $usage
    ) {

        if (
            $usage->status !==
            'reserved'
        ) {

            return response()->json([

                'message' =>
                'Only reserved allocations can be deleted'

            ], 422);
        }

        $usage->delete();

        InventoryMovement::create([

            'item_id' =>
            $usage->inventory_item_id,

            'type' => 'in',

            'source_type' => 'shoot',
            'source_id' => $usage->shoot_id,

            'quantity' =>
            $usage->quantity,

            'created_by' =>
            Auth::id(),

            'notes' =>

            'Reserved allocation deleted',
        ]);
        return response()->json([

            'message' =>
            'Allocation deleted'
        ]);
    }
}
