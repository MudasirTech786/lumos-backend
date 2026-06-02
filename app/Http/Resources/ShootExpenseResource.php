<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShootExpenseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            'id' => $this->id,

            'shoot_id' => $this->shoot_id,

            'category' => $this->category,

            'description' => $this->description,

            'amount' => $this->amount,

            'receipt' => $this->receipt
                ? asset('storage/'.$this->receipt)
                : null,

            'created_by' => $this->created_by,

            'created_at' => $this->created_at,

            'updated_at' => $this->updated_at
        ];
    }
}