<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Models\Employee;

use App\Services\EmployeeService;

use App\Http\Requests\StoreEmployeeRequest;

class EmployeeController extends Controller
{
    public function __construct(
        protected EmployeeService $employeeService
    ) {}

    // LIST
    public function index(Request $request)
    {
        $query = Employee::with('user');

        // SEARCH
        if ($request->search) {

            $query->where(function ($q) use ($request) {

                $q->where(
                    'name',
                    'LIKE',
                    "%{$request->search}%"
                )

                    ->orWhere(
                        'employee_code',
                        'LIKE',
                        "%{$request->search}%"
                    )

                    ->orWhere(
                        'department',
                        'LIKE',
                        "%{$request->search}%"
                    )

                    ->orWhere(
                        'designation',
                        'LIKE',
                        "%{$request->search}%"
                    );
            });
        }

        // STATUS FILTER
        if ($request->status) {

            $query->where(
                'status',
                $request->status
            );
        }

        return response()->json([

            'employees' => $query
                ->latest()
                ->paginate(10),

        ]);
    }

    // SHOW
    public function show(Employee $employee)
    {
        return response()->json([

            'employee' => $employee->load('user')

        ]);
    }

    // STORE
    public function store(
        StoreEmployeeRequest $request
    ) {

        $employee = $this->employeeService->create(
            $request->validated()
        );

        return response()->json([

            'message' => 'Employee created',

            'employee' => $employee->load('user')

        ], 201);
    }

    // UPDATE
    public function update(
        StoreEmployeeRequest $request,
        Employee $employee
    ) {

        $employee = $this->employeeService->update(
            $employee,
            $request->validated()
        );

        return response()->json([

            'message' => 'Updated',

            'employee' => $employee->load('user')

        ]);
    }

    // DELETE
    public function destroy(Employee $employee)
    {
        $this->employeeService->delete(
            $employee
        );

        return response()->json([

            'message' => 'Deleted'

        ]);
    }
}
