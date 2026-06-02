<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShootExpense extends Model
{
    protected $fillable = [

        'shoot_id',

        'category',

        'description',

        'amount',

        'receipt',

        'created_by'
    ];

    protected $casts = [

        'amount' => 'decimal:2'
    ];

    public function shoot()
    {
        return $this->belongsTo(Shoot::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class,'created_by');
    }
}