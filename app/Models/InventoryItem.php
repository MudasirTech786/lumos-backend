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

    protected $appends = [
        'calculated_available',
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

    public function getCalculatedAvailableAttribute()
    {
        $reserved = $this->usages()
            ->whereIn('status', [
                'reserved',
                'checked_out',
                'in_use',
                'partially_returned',
            ])
            ->get()
            ->sum(function ($usage) {
                return
                    $usage->quantity
                    -
                    ($usage->returned_quantity ?? 0)
                    -
                    ($usage->lost_quantity ?? 0);
            });

        return max($this->quantity - $reserved, 0);
    }
}
