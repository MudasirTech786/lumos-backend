<?php

namespace App\Services;

use App\Models\AssetAllocation;
use App\Models\InventoryAsset;
use Illuminate\Support\Facades\DB;

class AssetAllocationService
{
    public function allocate(
        InventoryAsset $asset,
        int $shootId,
        int $userId,
        ?string $notes = null
    ): AssetAllocation {

        return DB::transaction(function () use (
            $asset,
            $shootId,
            $userId,
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
                'allocated_at' => now(),
                'status' => 'allocated',
                'notes' => $notes,
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
                ->where('inventory_asset_id', $asset->id)
                ->where('status', 'allocated')
                ->firstOrFail();

            $allocation->update([
                'status' => 'returned',
                'returned_at' => now(),
            ]);

            $asset->update([
                'status' => 'available',
            ]);

            $asset->logs()->create([
                'user_id' => $userId,
                'action' => 'returned',
                'notes' => 'Returned from shoot',
            ]);

            return $allocation;
        });
    }
}