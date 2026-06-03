<?php

namespace App\Http\Controllers\Api;

use App\Models\Shoot;
use App\Http\Controllers\Controller;
use App\Services\Finance\ProductionFinanceService;

class ShootFinanceController
extends Controller
{
    public function show(
        Shoot $shoot,
        ProductionFinanceService $finance
    ) {
        return response()->json(

            $finance->calculate(
                $shoot
            )

        );
    }

   public function details(
    Shoot $shoot
) {

    $shoot->load([
        'crewMembers',
        'logistics',
        'expenses',
        'inventoryUsages.item'
    ]);

    $days = 1;

    if (
        $shoot->start_datetime &&
        $shoot->end_datetime
    ) {

        $days =
            \Carbon\Carbon::parse(
                $shoot->start_datetime
            )
            ->startOfDay()
            ->diffInDays(
                \Carbon\Carbon::parse(
                    $shoot->end_datetime
                )->startOfDay()
            ) + 1;
    }

    $crew = $shoot->crewMembers->map(function ($member) use ($days) {

        $dailyRate =
            $member->pivot->rate
            ??
            $member->rate_per_shift
            ??
            0;

        return [

            'source'   => 'Crew',

            'name'     => $member->name,

            'position' => $member->pivot->position,

            'days'     => $days,

            'rate'     => $dailyRate,

            'amount'   => $dailyRate * $days,
        ];
    });

    $logistics = $shoot->logistics->map(function ($item) {

        return [

            'source'      => 'Logistics',

            'name'        => $item->vehicle,

            'description' => $item->transport_type,

            'amount'      => $item->estimated_cost ?? 0,
        ];
    });

    $inventory = $shoot->inventoryUsages->map(function ($usage) use ($days) {

        $dailyRate =
            $usage->item->daily_rental_value ?? 0;

        $cost =
            ($usage->quantity ?? 0)
            *
            $dailyRate
            *
            $days;

        return [

            'source'   => 'Inventory',

            'name'     => $usage->item->name ?? 'Item',

            'quantity' => $usage->quantity,

            'days'     => $days,

            'rate'     => $dailyRate,

            'amount'   => $cost,
        ];
    });

    $expenses = $shoot->expenses->map(function ($expense) {

        return [

            'source'      => 'Expense',

            'name'        => $expense->category,

            'description' => $expense->description,

            'amount'      => $expense->amount,
        ];
    });

    return response()->json([

        'shoot_days' => $days,

        'crew'       => $crew,

        'logistics'  => $logistics,

        'inventory'  => $inventory,

        'expenses'   => $expenses,
    ]);
}
}
