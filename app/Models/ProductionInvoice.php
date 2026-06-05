<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionInvoice extends Model
{
    protected $fillable = [
        'shoot_id',
        'invoice_number',
        'title',
        'issue_date',
        'due_date',
        'subtotal',
        'tax_percentage',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'paid_amount',
        'balance_due',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
    ];

    public function shoot()
    {
        return $this->belongsTo(Shoot::class);
    }

    public function items()
    {
        return $this->hasMany(
            ProductionInvoiceItem::class,
            'invoice_id'
        );
    }

    public function payments()
    {
        return $this->hasMany(
            ProductionInvoicePayment::class,
            'invoice_id'
        );
    }
}