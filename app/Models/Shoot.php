<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\CrewMember;
use App\Models\User;
use Carbon\Carbon;

class Shoot extends Model
{
    use HasFactory;

    protected $fillable = [

        'title',

        'slug',

        'client_name',

        'location',

        'start_datetime',

        'end_datetime',

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
            'shoot_crew',
            'shoot_id',
            'crew_member_id'
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

    public function logistics()
    {
        return $this->hasMany(
            ShootLogistic::class
        );
    }

    public function syncStatus()
    {

        if ($this->status === 'cancelled') {
            return;
        }

        $now = Carbon::now();

        if (!$this->start_datetime) {

            if ($this->status !== 'planned') {

                $this->update([
                    'status' => 'planned'
                ]);
            }

            return;
        }

        if ($now->lt($this->start_datetime)) {

            if ($this->status !== 'scheduled') {

                $this->update([
                    'status' => 'scheduled'
                ]);
            }

            return;
        }

        if (
            $this->end_datetime &&
            $now->between(
                $this->start_datetime,
                $this->end_datetime
            )
        ) {

            if ($this->status !== 'active') {

                $this->update([
                    'status' => 'active'
                ]);
            }

            return;
        }

        if (
            $this->end_datetime &&
            $now->gt($this->end_datetime)
        ) {

            if ($this->status !== 'completed') {

                $this->update([
                    'status' => 'completed'
                ]);
            }

            return;
        }
    }

    public function inventoryUsages()
    {
        return $this->hasMany(
            InventoryUsage::class
        );
    }

    public function expenses()
    {
        return $this->hasMany(
            ShootExpense::class
        );
    }
}
