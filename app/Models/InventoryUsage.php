<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class InventoryUsage extends Model
{
    protected $fillable = [

        // =====================================
        // RELATIONS
        // =====================================

        'inventory_item_id',

        'usage_type',

        'shoot_id',
        'rental_order_id',

        'assigned_to',
        'created_by',

        // =====================================
        // QUANTITIES
        // =====================================

        'quantity',
        'returned_quantity',
        'damaged_quantity',
        'lost_quantity',
        // =====================================
        // STATUS
        // =====================================

        'status',

        // =====================================
        // DATES
        // =====================================

        'reserved_at',
        'checked_out_at',
        'returned_at',

        // =====================================
        // NOTES
        // =====================================

        'notes',
    ];

    protected $casts = [

        'reserved_at' => 'datetime',
        'checked_out_at' => 'datetime',
        'returned_at' => 'datetime',
    ];

    // =====================================
    // RELATIONS
    // =====================================

    public function item()
    {
        return $this->belongsTo(
            InventoryItem::class,
            'inventory_item_id'
        );
    }

    public function shoot()
    {
        return $this->belongsTo(
            Shoot::class
        );
    }

    public function assignedUser()
    {
        return $this->belongsTo(
            User::class,
            'assigned_to'
        );
    }

    public function creator()
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    // =====================================
    // HELPERS
    // =====================================

    public function isReturned()
    {
        return $this->status === 'returned';
    }

    public function isCheckedOut()
    {
        return $this->status === 'checked_out';
    }

    public function isReserved()
    {
        return $this->status === 'reserved';
    }

    public function damageReports()
    {
        return $this->hasMany(
            DamageReport::class,
            'inventory_usage_id'
        );
    }
}
