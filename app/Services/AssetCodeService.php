<?php

namespace App\Services;

use App\Models\InventoryAsset;

class AssetCodeService
{
    public static function generate(string $prefix): string
    {
        $lastAsset = InventoryAsset::where(
            'asset_code',
            'like',
            $prefix . '-%'
        )
            ->latest('id')
            ->first();

        $nextNumber = 1;

        if ($lastAsset) {

            preg_match(
                '/(\d+)$/',
                $lastAsset->asset_code,
                $matches
            );

            $nextNumber =
                intval($matches[1]) + 1;
        }

        return sprintf(
            '%s-%05d',
            $prefix,
            $nextNumber
        );
    }
}