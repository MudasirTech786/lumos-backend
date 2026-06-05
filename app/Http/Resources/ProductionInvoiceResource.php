<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductionInvoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'shoot_id' => $this->shoot_id,

            'invoice_number' => $this->invoice_number,

            'title' => $this->title,

            'issue_date' => $this->issue_date,

            'due_date' => $this->due_date,

            'subtotal' => $this->subtotal,

            'tax_percentage' => $this->tax_percentage,

            'tax_amount' => $this->tax_amount,

            'discount_amount' => $this->discount_amount,

            'total_amount' => $this->total_amount,

            'paid_amount' => $this->paid_amount,

            'balance_due' => $this->balance_due,

            'status' => $this->status,

            'notes' => $this->notes,

            'created_at' => $this->created_at,

            'shoot' => $this->whenLoaded('shoot'),

            'items' => ProductionInvoiceItemResource::collection(
                $this->whenLoaded('items')
            ),

            'payments' => ProductionInvoicePaymentResource::collection(
                $this->whenLoaded('payments')
            ),
        ];
    }
}