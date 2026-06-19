<?php

namespace App\Services;

use App\Models\AssetAllocation;
use App\Models\InventoryAsset;
use Illuminate\Support\Facades\DB;
use App\Models\InventoryUsage;

class AssetAllocationService
{
    public function allocate(
        InventoryAsset $asset,
        int $shootId,
        int $userId,
        ?int $assignedTo = null,
        ?string $notes = null
    ): AssetAllocation {

        return DB::transaction(function () use (
            $asset,
            $shootId,
            $userId,
            $assignedTo,
            $notes
        ) {

            if ($asset->status !== 'available') {
                throw new \Exception(
                    'Asset is not available.'
                );
            }

            $allocation = AssetAllocation::create([
                'inventory_asset_id' => $asset->id,
                'shoot_id' => $shootId,
                'allocated_by' => $userId,
                'assigned_to' => $assignedTo,
                'allocated_at' => now(),
                'status' => 'allocated',
                'notes' => $notes,
            ]);

            InventoryUsage::create([

                'inventory_asset_id' =>
                $asset->id,

                'inventory_item_id' =>
                $asset->inventory_item_id,

                'usage_type' =>
                'shoot',

                'assigned_to' =>
                $assignedTo,

                'shoot_id' =>
                $shootId,

                'created_by' =>
                $userId,

                'quantity' =>
                1,

                'returned_quantity' =>
                0,

                'damaged_quantity' =>
                0,

                'lost_quantity' =>
                0,

                'status' =>
                'checked_out',

                'checked_out_at' =>
                now(),

                'notes' =>
                $notes,
            ]);

            $asset->update([
                'status' => 'in_use',
            ]);

            $asset->logs()->create([
                'user_id' => $userId,
                'action' => 'allocated',
                'notes' => "Allocated to shoot #{$shootId}",
            ]);

            return $allocation;
        });
    }

    public function returnAsset(
        InventoryAsset $asset,
        int $userId
    ) {

        return DB::transaction(function () use (
            $asset,
            $userId
        ) {

            $allocation = AssetAllocation::query()

                ->where(
                    'inventory_asset_id',
                    $asset->id
                )

                ->where(
                    'status',
                    'allocated'
                )

                ->firstOrFail();

            $allocation->update([

                'status' =>
                'returned',

                'returned_at' =>
                now(),

            ]);

            $usage = InventoryUsage::query()

                ->where(
                    'inventory_asset_id',
                    $asset->id
                )

                ->where(
                    'status',
                    'checked_out'
                )

                ->latest()

                ->first();

            if ($usage) {

                $usage->update([

                    'returned_quantity' => $usage->quantity,

                    'status' =>
                    'returned',

                    'returned_at' =>
                    now(),

                ]);
            }

            $asset->update([

                'status' =>
                'available',

            ]);

            $asset->logs()->create([

                'user_id' =>
                $userId,

                'action' =>
                'returned',

                'notes' =>
                'Returned from shoot',

            ]);

            return $allocation;
        });
    }
}
