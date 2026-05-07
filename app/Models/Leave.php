<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Leave extends Model
{
    protected $table = 'leaves';

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [

        'employee_id',

        'leave_type',

        'start_date',

        'end_date',

        'total_days',

        'reason',

        'status',
    ];

    protected $casts = [

        'start_date' => 'date',

        'end_date' => 'date',
    ];

    // =========================
    // EMPLOYEE
    // =========================
    public function employee()
    {
        return $this->belongsTo(
            Employee::class,
            'employee_id',
            'id'
        );
    }
}