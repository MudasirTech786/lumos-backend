<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrewMember extends Model
{
    protected $casts = [
        'skills' => 'array',
        'joining_date' => 'date',
        'is_active' => 'boolean',
    ];

    protected $fillable = [
        'name',
        'email',
        'phone',
        'designation',

        'rate_per_shift',
        'basic_salary',
        'hourly_rate',

        'commission',
        'home_allowance',
        'fuel_allowance',
        'others',

        'employment_type',
        'skills',

        'joining_date',
        'address',
        'cnic',
        'emergency_contact',

        'profile_photo',

        'notes',

        'is_active'
    ];

    public function shoots()
    {
        return $this->belongsToMany(
            Shoot::class,
            'shoot_crew'
        )
            ->withPivot([
                'position',
                'call_time',
                'wrap_time',
                'rate',
                'status',
                'notes',
            ])
            ->withTimestamps();
    }
}
