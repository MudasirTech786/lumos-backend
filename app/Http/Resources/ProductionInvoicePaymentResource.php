<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductionInvoicePaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'payment_date' => $this->payment_date,

            'amount' => $this->amount,

            'payment_method' => $this->payment_method,

            'reference_number' => $this->reference_number,

            'notes' => $this->notes,
        ];
    }
}