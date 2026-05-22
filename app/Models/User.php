<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

use Laravel\Sanctum\HasApiTokens;

use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasRoles;

    protected $fillable = [

        'name',

        'email',

        'password',
    ];

    protected $hidden = [

        'password',

        'remember_token',
    ];

    public function employee()
    {
        return $this->hasOne(Employee::class);
    }

    public function assignedInventoryUsages()
    {
        return $this->hasMany(
            InventoryUsage::class,
            'assigned_to'
        );
    }

    public function createdInventoryUsages()
    {
        return $this->hasMany(
            InventoryUsage::class,
            'created_by'
        );
    }
}
