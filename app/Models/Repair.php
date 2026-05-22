<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Repair extends Model
{
    protected $fillable = [

        'damage_report_id',

        'inventory_item_id',

        'created_by',

        'vendor_name',

        'technician_name',

        'repair_details',

        'repair_cost',

        'status',

        'started_at',

        'completed_at',
    ];

    protected $casts = [

        'started_at' =>
            'datetime',

        'completed_at' =>
            'datetime',
    ];

    /* ===================================== */
    /* RELATIONS */
    /* ===================================== */

    public function damageReport()
    {
        return $this->belongsTo(
            DamageReport::class
        );
    }

    public function item()
    {
        return $this->belongsTo(
            InventoryItem::class,
            'inventory_item_id'
        );
    }

    public function creator()
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }
}