<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Http\Controllers\Controller;

use App\Models\DamageReport;

use App\Models\InventoryItem;

use App\Models\InventoryUsage;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;

class DamageReportController extends Controller
{
    /* ===================================== */
    /* INDEX */
    /* ===================================== */

    public function index()
    {
        $reports = DamageReport::with([

            'item',

            'usage',

            'reporter',

        ])

        ->latest()

        ->paginate(20);

        return response()->json([

            'reports' =>
                $reports,
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

                'inventory_usage_id' =>
                'nullable|exists:inventory_usages,id',

                'severity' =>
                'required|in:low,medium,high,critical',

                'issue_type' =>
                'required|string|max:255',

                'description' =>
                'nullable|string',

                'estimated_cost' =>
                'nullable|numeric|min:0',
            ]);

        $report =
            DamageReport::create([

                ...$validated,

                'reported_by' =>
                    Auth::id(),

                'reported_at' =>
                    now(),
            ]);

        /* ===================================== */
        /* ITEM STATUS */
        /* ===================================== */

        $item =
            InventoryItem::findOrFail(
                $validated['inventory_item_id']
            );

        $item->update([

            'status' =>
                'damaged',
        ]);

        return response()->json([

            'message' =>
                'Damage report created',

            'report' =>
                $report->load([

                    'item',

                    'usage',

                    'reporter',

                ]),
        ]);
    }

    /* ===================================== */
    /* UPDATE STATUS */
    /* ===================================== */

    public function updateStatus(
        Request $request,
        DamageReport $damageReport
    ) {

        $validated =
            $request->validate([

                'status' =>
                'required|in:pending,inspection,repair,resolved,writeoff',
            ]);

        $damageReport->update([

            'status' =>
                $validated['status'],
        ]);

        /* ===================================== */
        /* AUTO ITEM STATUS */
        /* ===================================== */

        if (
            $validated['status']
            === 'resolved'
        ) {

            $damageReport
                ->item
                ->update([

                    'status' =>
                        'available',
                ]);
        }

        if (
            $validated['status']
            === 'repair'
        ) {

            $damageReport
                ->item
                ->update([

                    'status' =>
                        'maintenance',
                ]);
        }

        if (
            $validated['status']
            === 'writeoff'
        ) {

            $damageReport
                ->item
                ->update([

                    'status' =>
                        'retired',
                ]);
        }

        return response()->json([

            'message' =>
                'Damage status updated',

            'report' =>
                $damageReport,
        ]);
    }
}