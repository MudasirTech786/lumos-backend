<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\CrewMember;
use App\Models\User;

class Shoot extends Model
{
    use HasFactory;

    protected $fillable = [

        'title',
        'slug',
        'client_name',
        'location',
        'shoot_date',
        'status',
        'notes',
        'created_by',
    ];

    /*
    |--------------------------------------------------------------------------
    | CREW ASSIGNMENTS
    |--------------------------------------------------------------------------
    */

    public function crewMembers()
    {
        return $this->belongsToMany(
            CrewMember::class,
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

    public function creator()
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }
}
