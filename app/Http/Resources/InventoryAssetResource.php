<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryAssetResource extends JsonResource
{
    public function toArray(
        Request $request
    ): array {

        return [

            'id' => $this->id,

            'asset_code' => $this->asset_code,

            'status' => $this->status,

            'serial_number' =>
            $this->serial_number,

            'qr_uuid' => $this->qr_uuid,

            'item' => [
                'id' =>
                $this->item?->id,

                'name' =>
                $this->item?->name,
            ]
        ];
    }
}