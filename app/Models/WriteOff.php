<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WriteOff extends Model
{
    protected $fillable = [

        'inventory_item_id',

        'damage_report_id',

        'approved_by',

        'reason',

        'notes',

        'estimated_loss_value',

        'written_off_at',
    ];

    protected $casts = [

        'written_off_at' =>
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

    public function damageReport()
    {
        return $this->belongsTo(
            DamageReport::class
        );
    }

    public function approver()
    {
        return $this->belongsTo(
            User::class,
            'approved_by'
        );
    }
}