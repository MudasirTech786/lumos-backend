<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    protected $fillable = [
        'employee_id',
        'basic_salary',
        'total_bonus',
        'total_deductions',
        'net_salary',
        'month',
        'year'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function items()
    {
        return $this->hasMany(PayrollItem::class);
    }

    public function payslip()
    {
        return $this->hasOne(Payslip::class);
    }
}
