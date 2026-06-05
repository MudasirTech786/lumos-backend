<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionInvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id',
        'description',
        'quantity',
        'unit_price',
        'line_total',
        'sort_order',
    ];

    public function invoice()
    {
        return $this->belongsTo(
            ProductionInvoice::class,
            'invoice_id'
        );
    }
}