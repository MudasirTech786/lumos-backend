<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetScanLog extends Model
{
    protected $fillable = [

        'inventory_asset_id',

        'user_id',

        'action',

        'notes',
    ];

    public function asset()
    {
        return $this->belongsTo(
            InventoryAsset::class,
            'inventory_asset_id'
        );
    }

    public function user()
    {
        return $this->belongsTo(
            User::class
        );
    }
}