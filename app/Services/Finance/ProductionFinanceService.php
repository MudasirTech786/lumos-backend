<?php

namespace App\Services\Finance;

use App\Models\Shoot;
use Carbon\Carbon;

class ProductionFinanceService
{
    public function calculate(
        Shoot $shoot
    ) {
        $days = 1;

        if (
            $shoot->start_datetime &&
            $shoot->end_datetime
        ) {

            $days =
                Carbon::parse(
                    $shoot->start_datetime
                )
                ->startOfDay()
                ->diffInDays(
                    Carbon::parse(
                        $shoot->end_datetime
                    )->startOfDay()
                ) + 1;
        }

        $crewCost =
            $shoot->crewMembers
            ->sum(function ($member) use ($days) {

                $dailyRate =
                    $member->pivot->rate
                    ??
                    $member->rate_per_shift
                    ??
                    0;

                return $dailyRate * $days;
            });

        $logisticsCost =
            $shoot->logistics
            ->sum('estimated_cost');

        $expenseCost =
            $shoot->expenses
            ->sum('amount');

        $inventoryCost =
            $shoot->inventoryUsages
            ->sum(function ($usage) use ($days) {

                return ($usage->quantity ?? 0)
                    *
                    ($usage->item->daily_rental_value ?? 0)
                    *
                    $days;
            });

        $repairCost = 0;

        $totalCost =
            $crewCost +
            $logisticsCost +
            $inventoryCost +
            $expenseCost +
            $repairCost;

        return [

            'crew_cost' => $crewCost,

            'logistics_cost' => $logisticsCost,

            'inventory_cost' => $inventoryCost,

            'shoot_days' => $days,

            'repair_cost' => $repairCost,

            'expense_cost' => $expenseCost,

            'total_cost' => $totalCost,

            'revenue' =>
            $shoot->client_invoice_amount,

            'profit' =>
            $shoot->client_invoice_amount
                - $totalCost
        ];
    }
}
