<?php

namespace App\Observers;

use App\Models\InventoryAsset;
use App\Models\InventoryItem;
use App\Services\AssetCodeService;
use Illuminate\Support\Str;
use App\Services\AssetQrService;

class InventoryItemObserver
{
    public function created(
        InventoryItem $item
    ): void {

        if (
            !$item->track_serial
            ||
            $item->type !== 'asset'
        ) {
            return;
        }

        $item->load('category');
        $prefix = strtoupper(
            substr(
                preg_replace(
                    '/[^A-Za-z]/',
                    '',
                    $item->category->name
                ),
                0,
                3
            )
        );

        for (
            $i = 1;
            $i <= $item->quantity;
            $i++
        ) {

            $asset = InventoryAsset::create([

                'inventory_item_id' =>
                $item->id,

                'asset_code' =>
                AssetCodeService::generate(
                    $prefix
                ),

                'qr_uuid' =>
                Str::uuid(),

                'status' =>
                'available',

            ]);

            // AssetQrService::generate(
            //     $asset
            // );
        }
    }
}
