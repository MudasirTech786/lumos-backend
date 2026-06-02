<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    protected $fillable = [

        'reference',

        'type',

        'period_start',

        'period_end',

        'status',

        'generated_by',

        'gross_amount',

        'deduction_amount',

        'bonus_amount',

        'net_amount'

    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function payslip()
    {
        return $this->hasOne(Payslip::class);
    }

    public function items()
    {
        return $this->hasMany(
            PayrollItem::class
        );
    }
}
