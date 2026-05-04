<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = [
        'department_id',
        'name',
        'email',
        'phone',
        'designation',
        'base_salary',
        'hire_date',
        'status'
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function leaves()
    {
        return $this->hasMany(Leave::class);
    }

    public function payrolls()
    {
        return $this->hasMany(Payroll::class);
    }
}
