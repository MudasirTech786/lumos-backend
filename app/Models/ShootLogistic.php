<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShootLogistic extends Model
{
    protected $fillable = [

        'shoot_id',

        'logistics_type',

        'transport_type',

        'vehicle',

        'driver_name',

        'pickup_location',

        'pickup_time',

        'dropoff_location',

        'dropoff_time',

        'vendor_name',

        'reference_number',

        'estimated_cost',

        'status',

        'notes',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIP
    |--------------------------------------------------------------------------
    */

    public function shoot()
    {
        return $this->belongsTo(
            Shoot::class
        );
    }
}
