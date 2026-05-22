<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DamageReport extends Model
{
    protected $fillable = [

        'inventory_item_id',

        'inventory_usage_id',

        'reported_by',

        'severity',

        'issue_type',

        'description',

        'status',

        'estimated_cost',

        'reported_at',
    ];

    protected $casts = [

        'reported_at' =>
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

    public function usage()
    {
        return $this->belongsTo(
            InventoryUsage::class,
            'inventory_usage_id'
        );
    }

    public function reporter()
    {
        return $this->belongsTo(
            User::class,
            'reported_by'
        );
    }

    public function repairs()
    {
        return $this->hasMany(
            Repair::class
        );
    }

    public function writeOffs()
    {
        return $this->hasMany(
            WriteOff::class
        );
    }
}
