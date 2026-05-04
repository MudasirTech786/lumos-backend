<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Cache;

class User extends Authenticatable
{
    use HasApiTokens;

    protected $fillable = [
        'role_id',
        'name',
        'email',
        'password',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function getAllPermissionsAttribute()
    {
        return $this->role?->permissions ?? collect();
    }
}
