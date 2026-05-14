<?php

namespace App\Services;

use App\Models\Employee;

class EmployeeService
{
    /*
    |--------------------------------------------------------------------------
    | CREATE EMPLOYEE
    |--------------------------------------------------------------------------
    */

    public function create(array $data)
    {
        // GET LAST EMPLOYEE

        $lastEmployee =
            Employee::latest('id')->first();

        // NEXT ID

        $nextId = $lastEmployee
            ? $lastEmployee->id + 1
            : 1;

        // AUTO GENERATE EMPLOYEE CODE

        $data['employee_code'] =
            'EMP-' .
            str_pad(
                $nextId,
                4,
                '0',
                STR_PAD_LEFT
            );

        return Employee::create($data);
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE EMPLOYEE
    |--------------------------------------------------------------------------
    */

    public function update(
        Employee $employee,
        array $data
    ) {

        // PREVENT MANUAL CODE CHANGE

        unset($data['employee_code']);

        $employee->update($data);

        return $employee;
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE EMPLOYEE
    |--------------------------------------------------------------------------
    */

    public function delete(Employee $employee)
    {
        return $employee->delete();
    }
}