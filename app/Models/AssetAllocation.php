<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetAllocation extends Model
{
    protected $fillable = [
        'inventory_asset_id',
        'shoot_id',
        'allocated_by',
        'assigned_to',
        'allocated_at',
        'returned_at',
        'status',
        'notes',
    ];

    protected $casts = [
        'allocated_at' => 'datetime',
        'returned_at' => 'datetime',
    ];

    public function asset()
    {
        return $this->belongsTo(InventoryAsset::class, 'inventory_asset_id');
    }

    public function shoot()
    {
        return $this->belongsTo(Shoot::class);
    }

    public function allocator()
    {
        return $this->belongsTo(User::class, 'allocated_by');
    }

    public function assignedUser()
    {
        return $this->belongsTo(
            User::class,
            'assigned_to'
        );
    }
}
