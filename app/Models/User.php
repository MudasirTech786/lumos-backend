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
}
