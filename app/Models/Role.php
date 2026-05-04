<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Role extends Model
{
    protected $fillable = ['name'];

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permissions');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    protected static function booted()
    {
        static::saved(function ($role) {
            foreach ($role->users as $user) {
                Cache::forget("user_permissions_{$user->id}");
            }
        });
    }
}
