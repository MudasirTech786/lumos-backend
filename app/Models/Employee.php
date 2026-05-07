<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = [

        'user_id',

        'employee_code',

        'name',
        'email',
        'phone',

        'department',
        'designation',

        'base_salary',

        'hire_date',

        'status',

        'cnic',
        'address',
        'emergency_contact',

        'profile_photo',
    ];

    protected $casts = [

        'hire_date' => 'date',
    ];

    public function leaves()
    {
        return $this->hasMany(Leave::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
