<?php

namespace App\Services\Payroll;

use App\Models\Shoot;
use App\Models\Payroll;
use App\Models\PayrollItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Employee;
use App\Models\Leave;

class PayrollService
{
    public function generateCrewPayroll(
        string $startDate,
        string $endDate
    ) {
        return DB::transaction(function () use (
            $startDate,
            $endDate
        ) {

            /*
            |--------------------------------------------------------------------------
            | Get Completed Shoots
            |--------------------------------------------------------------------------
            */

            $shoots = Shoot::with('crewMembers')
                ->where('status', 'completed')
                ->whereBetween(
                    'start_datetime',
                    [
                        $startDate,
                        $endDate
                    ]
                )
                ->get();

            /*
            |--------------------------------------------------------------------------
            | Create Payroll Header
            |--------------------------------------------------------------------------
            */

            $payroll = Payroll::create([

                'reference' =>
                'CP-' . now()->timestamp,

                'type' =>
                'crew',

                'period_start' =>
                $startDate,

                'period_end' =>
                $endDate,

                'status' =>
                'draft',

                'generated_by' =>
                Auth::user()->id,

                'gross_amount' =>
                0,

                'deduction_amount' =>
                0,

                'bonus_amount' =>
                0,

                'net_amount' =>
                0

            ]);

            /*
            |--------------------------------------------------------------------------
            | Create Payroll Items
            |--------------------------------------------------------------------------
            */

            foreach ($shoots as $shoot) {

                foreach (
                    $shoot->crewMembers
                    as $crew
                ) {

                    PayrollItem::create([

                        'payroll_id' =>
                        $payroll->id,

                        'shoot_id' =>
                        $shoot->id,

                        'person_type' =>
                        'crew',

                        'person_id' =>
                        $crew->id,

                        'description' =>
                        $shoot->title,

                        'quantity' =>
                        1,

                        'rate' =>
                        $crew->rate_per_shift,

                        'gross_amount' =>
                        $crew->rate_per_shift,

                        'deduction_amount' =>
                        0,

                        'bonus_amount' =>
                        0,

                        'net_amount' =>
                        $crew->rate_per_shift,

                        'is_paid' =>
                        false

                    ]);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Calculate Totals
            |--------------------------------------------------------------------------
            */

            $grossAmount =
                $payroll->items()
                ->sum('gross_amount');

            $deductions =
                $payroll->items()
                ->sum('deduction_amount');

            $bonuses =
                $payroll->items()
                ->sum('bonus_amount');

            $netAmount =
                $grossAmount
                -
                $deductions
                +
                $bonuses;

            /*
            |--------------------------------------------------------------------------
            | Update Payroll Header
            |--------------------------------------------------------------------------
            */

            $payroll->update([

                'gross_amount' =>
                $grossAmount,

                'deduction_amount' =>
                $deductions,

                'bonus_amount' =>
                $bonuses,

                'net_amount' =>
                $netAmount

            ]);

            /*
            |--------------------------------------------------------------------------
            | Return Payroll With Items
            |--------------------------------------------------------------------------
            */

            return $payroll->load(
                'items'
            );
        });
    }


    public function generateEmployeePayroll(
        string $startDate,
        string $endDate
    ) {
        return DB::transaction(function () use (
            $startDate,
            $endDate
        ) {
            $employees = Employee::where(
                'status',
                'active'
            )->get();

            $payroll = Payroll::create([

                'reference' =>
                'EP-' . now()->timestamp,

                'type' =>
                'employee',

                'period_start' =>
                $startDate,

                'period_end' =>
                $endDate,

                'status' =>
                'draft',

                'generated_by' =>
                Auth::id(),

                'gross_amount' =>
                0,

                'deduction_amount' =>
                0,

                'bonus_amount' =>
                0,

                'net_amount' =>
                0

            ]);

            foreach (
                $employees as $employee
            ) {

                $salary =
                    $employee->base_salary ?? 0;

                $leaveDays =
                    Leave::where(
                        'employee_id',
                        $employee->id
                    )
                    ->where(
                        'status',
                        'approved'
                    )
                    ->whereBetween(
                        'start_date',
                        [
                            $startDate,
                            $endDate
                        ]
                    )
                    ->count();

                $dailyRate =
                    $salary / 30;

                $deduction =
                    $dailyRate * $leaveDays;

                $netSalary =
                    $salary - $deduction;

                PayrollItem::create([

                    'payroll_id' =>
                    $payroll->id,

                    'person_type' =>
                    'employee',

                    'person_id' =>
                    $employee->id,

                    'description' =>
                    $employee->name,

                    'quantity' =>
                    1,

                    'rate' =>
                    $salary,

                    'gross_amount' =>
                    $salary,

                    'deduction_amount' =>
                    $deduction,

                    'bonus_amount' =>
                    0,

                    'net_amount' =>
                    $netSalary,

                    'is_paid' =>
                    false

                ]);
            }

            $grossAmount =
                $payroll->items()
                ->sum('gross_amount');

            $deductions =
                $payroll->items()
                ->sum('deduction_amount');

            $bonuses =
                $payroll->items()
                ->sum('bonus_amount');

            $netAmount =
                $grossAmount
                -
                $deductions
                +
                $bonuses;

            $payroll->update([

                'gross_amount' =>
                $grossAmount,

                'deduction_amount' =>
                $deductions,

                'bonus_amount' =>
                $bonuses,

                'net_amount' =>
                $netAmount

            ]);

            return $payroll->load(
                'items'
            );
        });
    }
}
