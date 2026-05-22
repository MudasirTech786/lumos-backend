<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inspection extends Model
{
    protected $fillable = [

        'inventory_item_id',

        'inspected_by',

        'condition',

        'status',

        'notes',

        'recommendations',

        'inspection_date',

        'next_inspection_due',
    ];

    protected $casts = [

        'inspection_date' =>
            'datetime',

        'next_inspection_due' =>
            'datetime',
    ];

    /* ===================================== */
    /* RELATIONS */
    /* ===================================== */

    public function item()
    {
        return $this->belongsTo(
            InventoryItem::class,
            'inventory_item_id'
        );
    }

    public function inspector()
    {
        return $this->belongsTo(
            User::class,
            'inspected_by'
        );
    }
}