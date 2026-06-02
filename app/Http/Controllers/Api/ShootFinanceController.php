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
    )
    {
        return response()->json(

            $finance->calculate(
                $shoot
            )

        );
    }
}