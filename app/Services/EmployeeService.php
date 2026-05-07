<?php

namespace App\Services;

use App\Models\Employee;

class EmployeeService
{
    // CREATE
    public function create(array $data)
    {
        return Employee::create($data);
    }

    // UPDATE
    public function update(
        Employee $employee,
        array $data
    ) {

        $employee->update($data);

        return $employee;
    }

    // DELETE
    public function delete(Employee $employee)
    {
        return $employee->delete();
    }
}