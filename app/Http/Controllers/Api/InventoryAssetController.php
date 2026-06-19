<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InventoryAsset;
use App\Http\Resources\InventoryAssetResource;
use App\Models\AssetScanLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryAssetController extends Controller
{
    public function index()
    {
        return InventoryAssetResource::collection(
            InventoryAsset::with([
                'item',
                'activeAllocation.shoot',
                'activeAllocation.assignedUser',
            ])
                ->latest()
                ->paginate()
        );
    }

    public function lookup($uuid)
    {
        return InventoryAsset::with([

            'item.category',

            'activeAllocation.shoot',

            'activeAllocation.assignedUser',

        ])
            ->where(
                'qr_uuid',
                $uuid
            )
            ->firstOrFail();
    }

    public function updateStatus(
        Request $request,
        InventoryAsset $asset
    ) {
        $validated =
            $request->validate([

                'status' => [
                    'required',
                    'in:available,in_use,returned,damaged,under_repair,written_off'
                ],

                'notes' =>
                'nullable|string',
            ]);

        $asset->update([
            'status' =>
            $validated['status']
        ]);

        AssetScanLog::create([

            'inventory_asset_id' =>
            $asset->id,

            'user_id' =>
            Auth::id(),

            'action' =>
            match ($validated['status']) {

                'damaged' =>
                'damage',

                'under_repair' =>
                'repair',

                'written_off' =>
                'writeoff',

                default =>
                'inspection',
            },

            'notes' =>
            $validated['notes'] ?? null,
        ]);

        return response()->json([
            'message' =>
            'Asset updated',

            'asset' =>
            $asset
        ]);
    }

    public function show(InventoryAsset $asset)
    {
        $asset->load([
            'item',
            'logs.user',
            'activeAllocation.shoot',
            'activeAllocation.assignedUser',
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $asset->id,
                'asset_code' => $asset->asset_code,
                'serial_number' => $asset->serial_number,
                'status' => $asset->status,
                'qr_uuid' => $asset->qr_uuid,
                'notes' => $asset->notes,
                'active_allocation' => $asset->activeAllocation
                    ? [

                        'id' =>
                        $asset->activeAllocation->id,

                        'status' =>
                        $asset->activeAllocation->status,

                        'allocated_at' =>
                        $asset->activeAllocation->allocated_at,

                        'shoot' =>
                        $asset->activeAllocation->shoot
                            ? [
                                'id' =>
                                $asset->activeAllocation->shoot->id,

                                'title' =>
                                $asset->activeAllocation->shoot->title,
                            ]
                            : null,

                        'assigned_user' =>
                        $asset->activeAllocation->assignedUser
                            ? [

                                'id' =>
                                $asset->activeAllocation
                                    ->assignedUser
                                    ->id,

                                'name' =>
                                $asset->activeAllocation
                                    ->assignedUser
                                    ->name,

                                'email' =>
                                $asset->activeAllocation
                                    ->assignedUser
                                    ->email,

                            ]
                            : null,

                    ]
                    : null,
                'item' => [
                    'id' => $asset->item?->id,
                    'name' => $asset->item?->name,
                    'sku' => $asset->item?->sku,

                    'category' =>
                    $asset->item?->category?->name,
                ],

                'logs' => $asset->logs->map(function ($log) {

                    return [
                        'id' => $log->id,
                        'action' => $log->action,
                        'notes' => $log->notes,
                        'user' => $log->user?->name,
                        'created_at' => $log->created_at,
                    ];
                }),
            ]
        ]);
    }
}
