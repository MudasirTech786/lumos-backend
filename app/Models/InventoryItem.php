<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'model',
        'sku',
        'serial_number',
        'track_serial',
        'type',
        'quantity',
        'minimum_quantity',
        'purchased_from',
        'purchase_date',
        'purchase_price',
        'warranty_expiry',
        'is_rentable',
        'is_returnable',
        'status',
        'notes',
    ];

    public function category()
    {
        return $this->belongsTo(
            InventoryCategory::class
        );
    }

    public function movements()
    {
        return $this->hasMany(
            InventoryMovement::class,
            'item_id'
        );
    }

    public function usages()
    {
        return $this->hasMany(
            InventoryUsage::class
        );
    }

    public function repairs()
    {
        return $this->hasMany(
            Repair::class
        );
    }

    public function inspections()
    {
        return $this->hasMany(
            Inspection::class
        );
    }

    public function writeOffs()
    {
        return $this->hasMany(
            WriteOff::class
        );
    }
}
