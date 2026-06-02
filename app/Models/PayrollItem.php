<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollItem extends Model
{
    protected $fillable = [

        'payroll_id',

        'shoot_id',

        'person_type',

        'person_id',

        'description',

        'quantity',

        'rate',

        'gross_amount',

        'deduction_amount',

        'bonus_amount',

        'net_amount',

        'is_paid',

        'paid_at'
    ];

    public function payroll()
    {
        return $this->belongsTo(
            Payroll::class
        );
    }

    public function shoot()
    {
        return $this->belongsTo(
            Shoot::class
        );
    }
}