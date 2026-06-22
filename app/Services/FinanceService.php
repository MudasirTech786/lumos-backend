<?php

namespace App\Services;

use App\Models\Shoot;
use Carbon\Carbon;

class FinanceService
{
    public static function calculateShootCost(
        Shoot $shoot
    ): float {

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

        /*
        |--------------------------------------------------------------------------
        | CREW COST
        |--------------------------------------------------------------------------
        */

        $crewCost = 0;

        foreach (
            $shoot->crewMembers
            as $member
        ) {

            $rate =
                $member->pivot->rate
                ??
                $member->rate_per_shift
                ??
                0;

            $crewCost +=
                $rate * $days;
        }

        /*
        |--------------------------------------------------------------------------
        | LOGISTICS COST
        |--------------------------------------------------------------------------
        */

        $logisticsCost =
            $shoot->logistics
                ->sum('estimated_cost');

        /*
        |--------------------------------------------------------------------------
        | INVENTORY COST
        |--------------------------------------------------------------------------
        */

        $inventoryCost = 0;

        foreach (
            $shoot->inventoryUsages
            as $usage
        ) {

            $dailyRate =
                $usage->item
                    ->daily_rental_value
                ??
                0;

            $inventoryCost +=
                (
                    ($usage->quantity ?? 0)
                    *
                    $dailyRate
                    *
                    $days
                );
        }

        /*
        |--------------------------------------------------------------------------
        | EXPENSES
        |--------------------------------------------------------------------------
        */

        $expenseCost =
            $shoot->expenses
                ->sum('amount');

        /*
        |--------------------------------------------------------------------------
        | TOTAL
        |--------------------------------------------------------------------------
        */

        return
            $crewCost
            +
            $logisticsCost
            +
            $inventoryCost
            +
            $expenseCost;
    }
}