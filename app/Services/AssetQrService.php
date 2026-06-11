<?php

namespace App\Services;

use App\Models\InventoryAsset;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class AssetQrService
{
    public static function generate(
        InventoryAsset $asset
    ): void {

        $fileName =
            $asset->asset_code . '.png';

        $relativePath =
            'assets/' . $fileName;

        $fullPath =
            storage_path(
                'app/public/' .
                $relativePath
            );

        QrCode::format('png')
            ->size(400)
            ->margin(2)
            ->generate(
                $asset->qr_uuid,
                $fullPath
            );

        $asset->update([
            'qr_image_path' =>
            $relativePath
        ]);
    }
}