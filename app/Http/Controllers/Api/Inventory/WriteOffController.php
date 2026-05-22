<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Http\Controllers\Controller;

use App\Models\DamageReport;

use App\Models\InventoryItem;

use App\Models\InventoryMovement;

use App\Models\WriteOff;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;

class WriteOffController extends Controller
{
    /* ===================================== */
    /* INDEX */
    /* ===================================== */

    public function index()
    {
        $writeOffs = WriteOff::with([

            'item',

            'damageReport',

            'approver',

        ])

        ->latest()

        ->paginate(20);

        return response()->json([

            'write_offs' =>
                $writeOffs,
        ]);
    }

    /* ===================================== */
    /* STORE */
    /* ===================================== */

    public function store(
        Request $request
    ) {

        $validated =
            $request->validate([

                'inventory_item_id' =>
                'required|exists:inventory_items,id',

                'damage_report_id' =>
                'nullable|exists:damage_reports,id',

                'reason' =>
                'required|string|max:255',

                'notes' =>
                'nullable|string',

                'estimated_loss_value' =>
                'nullable|numeric|min:0',
            ]);

        $item =
            InventoryItem::findOrFail(
                $validated['inventory_item_id']
            );

        /* ===================================== */
        /* PREVENT DUPLICATE WRITEOFF */
        /* ===================================== */

        if (
            $item->status === 'retired'
        ) {

            return response()->json([

                'message' =>
                    'Item already retired',

            ], 422);
        }

        $writeOff =
            WriteOff::create([

                'inventory_item_id' =>
                    $validated['inventory_item_id'],

                'damage_report_id' =>
                    $validated['damage_report_id'] ?? null,

                'approved_by' =>
                    Auth::id(),

                'reason' =>
                    $validated['reason'],

                'notes' =>
                    $validated['notes'] ?? null,

                'estimated_loss_value' =>
                    $validated['estimated_loss_value'] ?? null,

                'written_off_at' =>
                    now(),
            ]);

        /* ===================================== */
        /* ITEM STATUS */
        /* ===================================== */

        $item->update([

            'status' =>
                'retired',
        ]);

        /* ===================================== */
        /* DAMAGE REPORT STATUS */
        /* ===================================== */

        if (
            $writeOff->damageReport
        ) {

            $writeOff
                ->damageReport
                ->update([

                    'status' =>
                        'writeoff',
                ]);
        }

        /* ===================================== */
        /* MOVEMENT LOG */
        /* ===================================== */

        InventoryMovement::create([

            'item_id' =>
                $item->id,

            'type' =>
                'adjustment',

            'quantity' =>
                1,

            'source_type' =>
                'writeoff',

            'source_id' =>
                $writeOff->id,

            'created_by' =>
                Auth::id(),

            'notes' =>

                'Inventory written off: '

                .

                $validated['reason'],
        ]);

        return response()->json([

            'message' =>
                'Inventory written off successfully',

            'write_off' =>
                $writeOff->load([

                    'item',

                    'damageReport',

                    'approver',

                ]),
        ]);
    }
}