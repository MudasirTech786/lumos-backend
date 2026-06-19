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

            'qr_uuid' =>
            $this->qr_uuid,

            'item' => [

                'id' =>
                $this->item?->id,

                'name' =>
                $this->item?->name,

            ],

            'active_allocation' =>

            $this->activeAllocation

                ? [

                    'id' =>
                    $this->activeAllocation->id,

                    'status' =>
                    $this->activeAllocation->status,

                    'allocated_at' =>
                    $this->activeAllocation->allocated_at,

                    'shoot' =>

                    $this->activeAllocation->shoot

                        ? [

                            'id' =>
                            $this->activeAllocation->shoot->id,

                            'title' =>
                            $this->activeAllocation->shoot->title,

                        ]

                        : null,

                    'assigned_user' =>

                    $this->activeAllocation->assignedUser

                        ? [

                            'id' =>
                            $this->activeAllocation->assignedUser->id,

                            'name' =>
                            $this->activeAllocation->assignedUser->name,

                            'email' =>
                            $this->activeAllocation->assignedUser->email,

                        ]

                        : null,

                ]

                : null,

        ];
    }
}
