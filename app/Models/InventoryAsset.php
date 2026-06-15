<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryAsset extends Model
{
    protected $fillable = [
        'inventory_item_id',
        'asset_code',
        'qr_uuid',
        'qr_image_path',
        'serial_number',
        'status',
        'notes',
    ];

    public function item()
    {
        return $this->belongsTo(
            InventoryItem::class,
            'inventory_item_id'
        );
    }

    public function logs()
    {
        return $this->hasMany(
            AssetScanLog::class
        )->latest();
    }

    public function allocations()
    {
        return $this->hasMany(AssetAllocation::class);
    }

    public function activeAllocation()
    {
        return $this->hasOne(
            AssetAllocation::class,
            'inventory_asset_id'
        )->where(
            'status',
            'allocated'
        );
    }
}
