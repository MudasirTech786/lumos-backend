<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Http\Controllers\Controller;

use App\Models\DamageReport;

use App\Models\InventoryItem;

use App\Models\Repair;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;

use App\Services\NotificationService;

class RepairController extends Controller
{
    /* ===================================== */
    /* INDEX */
    /* ===================================== */

    public function index()
    {
        $repairs = Repair::with([

            'item',

            'damageReport',

            'creator',

        ])

        ->latest()

        ->paginate(20);

        return response()->json([

            'repairs' =>
                $repairs,
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

                'damage_report_id' =>
                'required|exists:damage_reports,id',

                'vendor_name' =>
                'nullable|string|max:255',

                'technician_name' =>
                'nullable|string|max:255',

                'repair_details' =>
                'nullable|string',

                'repair_cost' =>
                'nullable|numeric|min:0',
            ]);

        $damageReport =
            DamageReport::findOrFail(
                $validated['damage_report_id']
            );

        $repair =
            Repair::create([

                'damage_report_id' =>
                    $damageReport->id,

                'inventory_item_id' =>
                    $damageReport->inventory_item_id,

                'created_by' =>
                    Auth::id(),

                'vendor_name' =>
                    $validated['vendor_name'] ?? null,

                'technician_name' =>
                    $validated['technician_name'] ?? null,

                'repair_details' =>
                    $validated['repair_details'] ?? null,

                'repair_cost' =>
                    $validated['repair_cost'] ?? 0,

                'status' =>
                    'pending',
            ]);

        /* ===================================== */
        /* DAMAGE STATUS */
        /* ===================================== */

        $damageReport->update([

            'status' =>
                'repair',
        ]);

        /* ===================================== */
        /* ITEM STATUS */
        /* ===================================== */

        $damageReport
            ->item
            ->update([

                'status' =>
                    'maintenance',
            ]);

        return response()->json([

            'message' =>
                'Repair created',

            'repair' =>
                $repair->load([

                    'item',

                    'damageReport',

                    'creator',

                ]),
        ]);
    }

    /* ===================================== */
    /* UPDATE STATUS */
    /* ===================================== */

    public function updateStatus(
        Request $request,
        Repair $repair
    ) {

        $validated =
            $request->validate([

                'status' =>
                'required|in:pending,in_progress,completed,cancelled',
            ]);

        $repair->update([

            'status' =>
                $validated['status'],
        ]);

        /* ===================================== */
        /* START */
        /* ===================================== */

        if (
            $validated['status']
            === 'in_progress'
        ) {

            $repair->update([

                'started_at' =>
                    now(),
            ]);
        }

        /* ===================================== */
        /* COMPLETED */
        /* ===================================== */

        if (
            $validated['status']
            === 'completed'
        ) {

            $repair->update([

                'completed_at' =>
                    now(),
            ]);

            /* ===================================== */
            /* DAMAGE REPORT */
            /* ===================================== */

            $repair
                ->damageReport
                ->update([

                    'status' =>
                        'resolved',
                ]);

            /* ===================================== */
            /* ITEM STATUS */
            /* ===================================== */

            $repair
                ->item
                ->update([

                    'status' =>
                        'available',
                ]);

            $notification = app(NotificationService::class);
            $notification->sendToPermission([
                'title' => 'Repair Completed',
                'message' => 'Repair for "' . $repair->item->name . '" has been completed.',
                'module' => 'inventory',
                'type' => 'success',
                'priority' => 'normal',
                'action_url' => '/dashboard/inventory/damages',
                'related_model' => 'Repair',
                'related_id' => $repair->id,
                'created_by' => Auth::id(),
            ], 'inventory.view');
        }

        return response()->json([

            'message' =>
                'Repair status updated',

            'repair' =>
                $repair,
        ]);
    }
}