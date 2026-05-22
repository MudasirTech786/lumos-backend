<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Http\Controllers\Controller;

use App\Models\InventoryItem;
use App\Models\InventoryUsage;
use App\Models\InventoryMovement;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryUsageController extends Controller
{
    // =========================================
    // INDEX
    // =========================================

    public function index(Request $request)
    {
        $query = InventoryUsage::with([

            'item',
            'shoot',
            'assignedUser',
            'creator',

        ]);

        // =====================================
        // FILTERS
        // =====================================

        if ($request->usage_type) {

            $query->where(
                'usage_type',
                $request->usage_type
            );
        }

        if ($request->status) {

            $query->where(
                'status',
                $request->status
            );
        }

        return response()->json([

            'data' =>

            $query
                ->latest()
                ->paginate(20)

        ]);
    }

    // =========================================
    // STORE
    // =========================================

    public function store(Request $request)
    {
        $validated = $request->validate([

            'inventory_item_id' =>
            'required|exists:inventory_items,id',

            'usage_type' =>
            'required|in:shoot,rental,internal,maintenance',

            'shoot_id' =>
            'nullable|exists:shoots,id',

            'assigned_to' =>
            'nullable|exists:users,id',

            'quantity' =>
            'required|integer|min:1',

            'notes' =>
            'nullable|string',
        ]);

        // =====================================
        // ITEM
        // =====================================

        $item = InventoryItem::findOrFail(
            $validated['inventory_item_id']
        );

        // =====================================
        // RESERVED STOCK
        // =====================================

        $reserved = InventoryUsage::where(
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
        // =====================================
        // AVAILABLE STOCK
        // =====================================

        $available =
            $item->quantity - $reserved;

        if (
            $validated['quantity']
            >
            $available
        ) {

            return response()->json([

                'message' =>
                'Not enough stock available.'

            ], 422);
        }

        // =====================================
        // CREATE USAGE
        // =====================================

        $usage = InventoryUsage::create([

            'inventory_item_id' =>
            $validated['inventory_item_id'],

            'usage_type' =>
            $validated['usage_type'],

            'shoot_id' =>
            $validated['shoot_id'] ?? null,

            'assigned_to' =>
            $validated['assigned_to'] ?? null,

            'created_by' =>
            Auth::id(),

            'quantity' =>
            $validated['quantity'],

            'returned_quantity' => 0,

            'damaged_quantity' => 0,

            'status' => 'reserved',

            'reserved_at' => now(),

            'notes' =>
            $validated['notes'] ?? null,
        ]);

        // =====================================
        // MOVEMENT
        // =====================================

        InventoryMovement::create([

            'item_id' =>

            $usage->inventory_item_id,

            'type' => 'out',

            'quantity' =>

            $usage->quantity,

            'source_type' =>

            InventoryUsage::class,

            'source_id' =>

            $usage->id,

            'created_by' =>

            Auth::user()->id,

            'notes' =>

            'Equipment allocated for ' .
                $usage->usage_type,
        ]);

        return response()->json([

            'message' =>
            'Inventory allocated successfully.',

            'usage' => $usage->load([

                'item',
                'shoot',
                'assignedUser',
                'creator',

            ])

        ]);
    }

    // =========================================
    // SHOW
    // =========================================

    public function show(
        InventoryUsage $usage
    ) {

        return response()->json(

            $usage->load([

                'item',
                'shoot',
                'assignedUser',
                'creator',

            ])

        );
    }

    // =========================================
    // UPDATE
    // =========================================

    public function update(
        Request $request,
        InventoryUsage $usage
    ) {

        $validated = $request->validate([

            'usage_type' =>
            'nullable|in:shoot,rental,internal,maintenance',

            'shoot_id' =>
            'nullable|exists:shoots,id',

            'assigned_to' =>
            'nullable|exists:users,id',

            'quantity' =>
            'nullable|integer|min:1',

            'notes' =>
            'nullable|string',
        ]);

        $usage->update($validated);

        return response()->json([

            'message' =>
            'Usage updated successfully.',

            'usage' => $usage->fresh([

                'item',
                'shoot',
                'assignedUser',
                'creator',

            ])

        ]);
    }

    // =========================================
    // CHECKOUT
    // =========================================

    public function checkout(
        InventoryUsage $usage
    ) {

        if (
            $usage->status !==
            'reserved'
        ) {

            return response()->json([

                'message' =>
                'Only reserved items can be checked out.'

            ], 422);
        }

        $usage->update([

            'status' =>
            'checked_out',

            'checked_out_at' =>
            now(),

        ]);

        // =====================================
        // MOVEMENT
        // =====================================

        InventoryMovement::create([

            'item_id' =>

            $usage->inventory_item_id,

            'type' => 'out',

            'quantity' =>

            $usage->quantity,

            'source_type' =>

            InventoryUsage::class,

            'source_id' =>

            $usage->id,

            'created_by' =>

            Auth::user()->id,

            'notes' =>
            'Equipment checked out',
        ]);

        return response()->json([

            'message' =>
            'Equipment checked out successfully.',

            'usage' => $usage->fresh([

                'item',
                'shoot',
                'assignedUser',
                'creator',

            ])

        ]);
    }

    // =========================================
    // RETURN
    // =========================================

    public function processReturn(
        Request $request,
        InventoryUsage $usage
    ) {

        $validated = $request->validate([

            'returned_quantity' =>
            'required|integer|min:0',

            'damaged_quantity' =>
            'nullable|integer|min:0',

            'lost_quantity' =>
            'nullable|integer|min:0',

            'notes' =>
            'nullable|string',
        ]);

        /* ===================================== */
        /* INPUTS */
        /* ===================================== */

        $returned =
            (int) ($validated['returned_quantity'] ?? 0);

        $damaged =
            (int) ($validated['damaged_quantity'] ?? 0);

        $lost =
            (int) ($validated['lost_quantity'] ?? 0);

        /* ===================================== */
        /* MUST RESOLVE SOMETHING */
        /* ===================================== */

        if (

            ($returned + $lost) <= 0

        ) {

            return response()->json([

                'message' =>
                'Please return or mark at least one item as lost.'

            ], 422);
        }

        /* ===================================== */
        /* CURRENT TOTALS */
        /* ===================================== */

        $currentReturned =
            (int) $usage->returned_quantity;

        $currentDamaged =
            (int) $usage->damaged_quantity;

        $currentLost =
            (int) $usage->lost_quantity;

        /* ===================================== */
        /* NEW TOTALS */
        /* ===================================== */

        $newReturnedTotal =
            $currentReturned + $returned;

        $newDamagedTotal =
            $currentDamaged + $damaged;

        $newLostTotal =
            $currentLost + $lost;

        /* ===================================== */
        /* VALIDATION */
        /* ===================================== */

        /*
    |--------------------------------------------------------------------------
    | returned_quantity
    |--------------------------------------------------------------------------
    |
    | TOTAL physical items returned
    |
    | damaged_quantity is INCLUDED
    | inside returned_quantity
    |
    */

        if (

            $damaged >

            $returned

        ) {

            return response()->json([

                'message' =>
                'Damaged quantity cannot exceed returned quantity.'

            ], 422);
        }

        if (

            $newDamagedTotal >

            $newReturnedTotal

        ) {

            return response()->json([

                'message' =>
                'Total damaged quantity cannot exceed total returned quantity.'

            ], 422);
        }

        /* ===================================== */
        /* TOTAL RESOLVED */
        /* ===================================== */

        $totalResolved =

            $newReturnedTotal
            +
            $newLostTotal;

        if (

            $totalResolved >

            (int) $usage->quantity

        ) {

            return response()->json([

                'message' =>
                'Resolved quantity exceeds allocated quantity.'

            ], 422);
        }

        /* ===================================== */
        /* STATUS */
        /* ===================================== */

        $status = 'partially_returned';

        if (

            $totalResolved >=

            (int) $usage->quantity

        ) {

            $status = 'returned';
        }

        /* ===================================== */
        /* UPDATE USAGE */
        /* ===================================== */

        $usage->update([

            'returned_quantity' =>
            $newReturnedTotal,

            'damaged_quantity' =>
            $newDamagedTotal,

            'lost_quantity' =>
            $newLostTotal,

            'returned_at' =>
            now(),

            'status' =>
            $status,

            'notes' =>
            $validated['notes']
                ??
                $usage->notes,
        ]);



        /* ===================================== */
        /* RETURN MOVEMENT */
        /* ===================================== */

        if ($returned > 0) {

            InventoryMovement::create([

                'item_id' =>

                $usage->inventory_item_id,

                'type' => 'in',

                'quantity' =>

                $returned,

                'source_type' =>

                InventoryUsage::class,

                'source_id' =>

                $usage->id,

                'created_by' =>

                Auth::id(),

                'notes' =>
                'Equipment returned',
            ]);
        }

        /* ===================================== */
        /* DAMAGE MOVEMENT */
        /* ===================================== */

        if ($damaged > 0) {

            InventoryMovement::create([

                'item_id' =>

                $usage->inventory_item_id,

                'type' => 'damage',

                'quantity' =>

                $damaged,

                'source_type' =>

                InventoryUsage::class,

                'source_id' =>

                $usage->id,

                'created_by' =>

                Auth::id(),

                'notes' =>
                'Equipment damaged during usage',
            ]);
        }

        /* ===================================== */
        /* LOSS MOVEMENT */
        /* ===================================== */

        if ($lost > 0) {

            InventoryMovement::create([

                'item_id' =>

                $usage->inventory_item_id,

                'type' => 'loss',

                'quantity' =>

                $lost,

                'source_type' =>

                InventoryUsage::class,

                'source_id' =>

                $usage->id,

                'created_by' =>

                Auth::id(),

                'notes' => $usage->notes ?: 'Equipment lost during usage',
            ]);
        }

        /* ===================================== */
        /* RESPONSE */
        /* ===================================== */

        return response()->json([

            'message' =>
            'Equipment return processed successfully.',

            'usage' => $usage->fresh([

                'item',
                'shoot',
                'assignedUser',
                'creator',

            ])

        ]);
    }

    /* ========================================= */
    /* DESTROY */
    /* ========================================= */

    public function destroy(
        InventoryUsage $usage
    ) {
        try {
            if ($usage->status !== 'reserved') {

                return response()->json([

                    'message' =>
                    'Only reserved allocations can be deleted.'

                ], 422);
            }


            // delete movements

            InventoryMovement::where(
                'source_type',
                InventoryUsage::class
            )
                ->where(
                    'source_id',
                    $usage->id
                )
                ->delete();

            // delete usage

            $usage->delete();

            return response()->json([

                'message' =>
                'Allocation deleted successfully.'

            ]);
        } catch (\Exception $e) {

            return response()->json([

                'message' =>
                $e->getMessage()

            ], 500);
        }
    }
}
