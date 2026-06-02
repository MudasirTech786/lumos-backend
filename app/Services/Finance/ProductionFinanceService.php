<?php

namespace App\Services\Finance;

use App\Models\Shoot;

class ProductionFinanceService
{
    public function calculate(
        Shoot $shoot
    )
    {
        $crewCost =
            $shoot->crewMembers
            ->sum('rate_per_shift');

        $logisticsCost =
            $shoot->logistics
            ->sum('estimated_cost');

        $expenseCost =
            $shoot->expenses
            ->sum('amount');

        $inventoryCost = 0;

        $repairCost = 0;

        $totalCost =
            $crewCost +
            $logisticsCost +
            $expenseCost;

        return [

            'crew_cost' => $crewCost,

            'logistics_cost' => $logisticsCost,

            'inventory_cost' => $inventoryCost,

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