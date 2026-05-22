<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryMovement extends Model
{
    protected $fillable = [
        'item_id',
        'type',
        'quantity',
        'source_type',
        'source_id',
        'created_by',
        'notes',
    ];

    public function item()
    {
        return $this->belongsTo(
            InventoryItem::class
        );
    }

    public function source()
    {
        return $this->morphTo();
    }
}
