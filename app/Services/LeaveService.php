<?php

namespace App\Services;

use App\Models\Leave;

use Carbon\Carbon;

class LeaveService
{
    public function create(array $data)
    {
        $data['total_days'] = Carbon::parse(
            $data['start_date']
        )->diffInDays(
            Carbon::parse($data['end_date'])
        ) + 1;

        return Leave::create($data);
    }

    public function update(
        Leave $leave,
        array $data
    ) {

        $data['total_days'] = Carbon::parse(
            $data['start_date']
        )->diffInDays(
            Carbon::parse($data['end_date'])
        ) + 1;

        $leave->update($data);

        return $leave;
    }

    public function delete(Leave $leave)
    {
        return $leave->delete();
    }
}
