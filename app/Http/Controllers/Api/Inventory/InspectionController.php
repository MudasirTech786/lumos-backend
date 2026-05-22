<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Http\Controllers\Controller;

use App\Models\Inspection;

use App\Models\InventoryItem;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;

class InspectionController extends Controller
{
    /* ===================================== */
    /* INDEX */
    /* ===================================== */

    public function index()
    {
        $inspections = Inspection::with([

            'item',

            'inspector',

        ])

        ->latest()

        ->paginate(20);

        return response()->json([

            'inspections' =>
                $inspections,
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

                'condition' =>
                'required|in:excellent,good,fair,poor,critical',

                'status' =>
                'required|in:passed,attention,failed',

                'notes' =>
                'nullable|string',

                'recommendations' =>
                'nullable|string',

                'next_inspection_due' =>
                'nullable|date',
            ]);

        $inspection =
            Inspection::create([

                'inventory_item_id' =>
                    $validated['inventory_item_id'],

                'inspected_by' =>
                    Auth::id(),

                'condition' =>
                    $validated['condition'],

                'status' =>
                    $validated['status'],

                'notes' =>
                    $validated['notes'] ?? null,

                'recommendations' =>
                    $validated['recommendations'] ?? null,

                'inspection_date' =>
                    now(),

                'next_inspection_due' =>
                    $validated['next_inspection_due'] ?? null,
            ]);

        /* ===================================== */
        /* AUTO ITEM STATUS */
        /* ===================================== */

        $item =
            InventoryItem::findOrFail(
                $validated['inventory_item_id']
            );

        if (
            $validated['status']
            === 'failed'
        ) {

            $item->update([

                'status' =>
                    'maintenance',
            ]);
        }

        if (
            $validated['condition']
            === 'critical'
        ) {

            $item->update([

                'status' =>
                    'damaged',
            ]);
        }

        return response()->json([

            'message' =>
                'Inspection completed',

            'inspection' =>
                $inspection->load([

                    'item',

                    'inspector',

                ]),
        ]);
    }
}