<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionInvoicePayment extends Model
{
    protected $fillable = [
        'invoice_id',
        'payment_date',
        'amount',
        'payment_method',
        'reference_number',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
    ];

    public function invoice()
    {
        return $this->belongsTo(
            ProductionInvoice::class,
            'invoice_id'
        );
    }
}