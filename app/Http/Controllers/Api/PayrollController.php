<?php

namespace App\Http\Controllers\Api;

use App\Models\Payroll;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Payroll\PayrollService;
use Illuminate\Support\Facades\Auth;
use App\Services\Finance\FinanceService;
use App\Models\Shoot;
use App\Services\NotificationService;

class PayrollController
extends Controller
{
    public function index()
    {
        return Payroll::latest()
            ->paginate();
    }

    public function show(
        Payroll $payroll
    ) {
        return $payroll
            ->load('items');
    }

    public function
    generateCrewPayroll(
        Request $request,
        PayrollService $service
    ) {
        return $service
            ->generateCrewPayroll(

                $request->start_date,

                $request->end_date

            );
    }

    public function generateEmployeePayroll(
        Request $request,
        PayrollService $service
    ) {
        return $service
            ->generateEmployeePayroll(

                $request->start_date,

                $request->end_date

            );
    }

    public function approve(
        Payroll $payroll
    ) {
        $payroll->update([

            'status' =>
            'approved',

            'approved_by' =>
            Auth::user()->id

        ]);

        $notification = app(NotificationService::class);
        $notification->sendToPermission([
            'title' => 'Payroll Approved',
            'message' => 'Payroll "' . $payroll->reference . '" (' . ucfirst($payroll->type) . ') has been approved.',
            'module' => 'finance',
            'type' => 'success',
            'priority' => 'high',
            'action_url' => '/dashboard/finance/payrolls',
            'related_model' => 'Payroll',
            'related_id' => $payroll->id,
            'created_by' => Auth::user()->id,
        ], 'finance.view');

        return response()->json([

            'message' =>
            'Payroll approved.'

        ]);
    }

    public function markPaid(
        Payroll $payroll
    ) {
        $payroll->update([

            'status' =>
            'paid',

            'paid_at' =>
            now()

        ]);

        $payroll->items()
            ->update([

                'is_paid' =>
                true,

                'paid_at' =>
                now()

            ]);

        $notification = app(NotificationService::class);
        $notification->sendToPermission([
            'title' => 'Payroll Paid',
            'message' => 'Payroll "' . $payroll->reference . '" ($' . number_format($payroll->net_amount, 2) . ') has been marked as paid.',
            'module' => 'finance',
            'type' => 'success',
            'priority' => 'high',
            'action_url' => '/dashboard/finance/payrolls',
            'related_model' => 'Payroll',
            'related_id' => $payroll->id,
            'created_by' => Auth::user()->id,
        ], 'finance.view');

        return response()->json([

            'message' =>
            'Payroll paid.'

        ]);
    }

    public function items(
        Payroll $payroll
    ) {
        return $payroll->items;
    }

    public function reports()
    {
        $shoots = Shoot::with([
            'crewMembers',
            'logistics'
        ])->get();

        $revenue = 0;
        $cost = 0;
        $profit = 0;

        $crewCost = 0;
        $logisticsCost = 0;
        $inventoryCost = 0;
        $repairCost = 0;
        $expenseCost = 0;

        foreach ($shoots as $shoot) {

            $shootCrewCost =
                $shoot->crewMembers
                ->sum('rate_per_shift');

            $shootLogisticsCost =
                $shoot->logistics
                ->sum('estimated_cost');

            $shootExpenseCost =
                \App\Models\ShootExpense::where(
                    'shoot_id',
                    $shoot->id
                )->sum('amount');

            $shootInventoryCost = 0;
            $shootRepairCost = 0;

            $shootTotalCost =
                $shootCrewCost +
                $shootLogisticsCost +
                $shootExpenseCost +
                $shootInventoryCost +
                $shootRepairCost;

            $shootRevenue =
                (float) (
                    $shoot->client_invoice_amount
                    ?? 0
                );

            $shootProfit =
                $shootRevenue -
                $shootTotalCost;

            $revenue +=
                $shootRevenue;

            $cost +=
                $shootTotalCost;

            $profit +=
                $shootProfit;

            $crewCost +=
                $shootCrewCost;

            $logisticsCost +=
                $shootLogisticsCost;

            $inventoryCost +=
                $shootInventoryCost;

            $repairCost +=
                $shootRepairCost;

            $expenseCost +=
                $shootExpenseCost;
        }

        $payrollPaid =
            Payroll::where(
                'status',
                'paid'
            )->sum('net_amount');

        $payrollPending =
            Payroll::where(
                'status',
                '!=',
                'paid'
            )->sum('net_amount');

        return response()->json([

            'totals' => [

                'revenue' =>
                $revenue,

                'cost' =>
                $cost,

                'profit' =>
                $profit,

                'payroll_paid' =>
                $payrollPaid,

                'payroll_pending' =>
                $payrollPending,

            ],

            'breakdown' => [

                'crew' =>
                $crewCost,

                'logistics' =>
                $logisticsCost,

                'inventory' =>
                $inventoryCost,

                'repairs' =>
                $repairCost,

                'expenses' =>
                $expenseCost,

            ]

        ]);
    }
}
