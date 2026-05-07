<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Leave;
use App\Models\Employee;
use App\Models\User;

use Carbon\Carbon;

class LeaveController extends Controller
{
    // =========================
    // LIST LEAVES
    // =========================
    public function index(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $query = Leave::with('employee');

        // SEARCH
        if ($request->search) {

            $query->whereHas(
                'employee',
                function ($q) use ($request) {

                    $q->where(
                        'name',
                        'LIKE',
                        "%{$request->search}%"
                    );
                }
            );
        }

        // STATUS FILTER
        if ($request->status) {

            $query->where(
                'status',
                $request->status
            );
        }

        // =========================
        // NON HR USERS
        // ONLY THEIR LEAVES
        // =========================
        if (!$user->can('leaves.approve')) {

            $employee = Employee::where(
                'user_id',
                $user->id
            )->first();

            if ($employee) {

                $query->where(
                    'employee_id',
                    $employee->id
                );
            } else {

                $query->whereRaw('1 = 0');
            }
        }

        return response()->json([

            'leaves' => $query
                ->latest()
                ->paginate(10)

        ]);
    }

    // =========================
    // SHOW
    // =========================
    public function show(Leave $leave)
    {
        return response()->json([

            'leave' => $leave
                ->load('employee')

        ]);
    }

    // =========================
    // CREATE LEAVE
    // =========================
    public function store(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $validated = $request->validate([

            'employee_id' => 'nullable|exists:employees,id',

            'leave_type' => 'required|string',

            'start_date' => 'required|date',

            'end_date' => 'required|date|after_or_equal:start_date',

            'reason' => 'nullable|string',

            'status' => 'nullable|in:pending,approved,rejected',
        ]);

        // =========================
        // NON HR USER
        // =========================
        if (!$user->can('leaves.approve')) {

            $employee = Employee::where(
                'user_id',
                $user->id
            )->first();

            if (!$employee) {

                return response()->json([

                    'message' => 'Employee profile not linked to this user'

                ], 422);
            }

            // FORCE EMPLOYEE
            $validated['employee_id'] =
                $employee->id;

            // FORCE PENDING
            $validated['status'] =
                'pending';
        }

        // =========================
        // TOTAL DAYS
        // =========================
        $start = Carbon::parse(
            $validated['start_date']
        );

        $end = Carbon::parse(
            $validated['end_date']
        );

        $validated['total_days'] =
            $start->diffInDays($end) + 1;

        $leave = Leave::create($validated);

        return response()->json([

            'message' => 'Leave created',

            'leave' => $leave
                ->load('employee')

        ]);
    }

    // =========================
    // UPDATE LEAVE
    // =========================
    public function update(Request $request, Leave $leave)
{
    $validated = $request->validate([

        'employee_id' => 'nullable|exists:employees,id',

        'leave_type' => 'nullable|string',

        'start_date' => 'nullable|date',

        'end_date' => 'nullable|date',

        'reason' => 'nullable|string',

        'status' => 'nullable|in:pending,approved,rejected',
    ]);

    $leave->fill($validated);

    // RECALCULATE DAYS
    if (
        $leave->start_date &&
        $leave->end_date
    ) {

        $start = \Carbon\Carbon::parse(
            $leave->start_date
        );

        $end = \Carbon\Carbon::parse(
            $leave->end_date
        );

        $leave->total_days =
            $start->diffInDays($end) + 1;
    }

    $leave->save();

    return response()->json([

        'message' => 'Leave updated',

        'leave' => Leave::with('employee')
            ->where('id', $leave->id)
            ->first()

    ]);
}

    // =========================
    // DELETE LEAVE
    // =========================
    public function destroy(Leave $leave)
    {
        /** @var User $user */
        $user = Auth::user();

        // =========================
        // NON HR USERS
        // =========================
        if (!$user->can('leaves.delete')) {

            $employee = Employee::where(
                'user_id',
                $user->id
            )->first();

            if (
                !$employee ||
                $leave->employee_id !== $employee->id
            ) {

                return response()->json([

                    'message' => 'Unauthorized'

                ], 403);
            }

            // ONLY PENDING DELETABLE
            if ($leave->status !== 'pending') {

                return response()->json([

                    'message' => 'Only pending leave can be deleted'

                ], 403);
            }
        }

        $leave->delete();

        return response()->json([

            'message' => 'Deleted'

        ]);
    }
}
