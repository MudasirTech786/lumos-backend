<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Http\Controllers\Controller;

use App\Models\InventoryMovement;

class InventoryMovementController extends Controller
{
    public function index()
    {
        return response()->json(

            InventoryMovement::with([

                'item',

            ])
            ->latest()
            ->get()

        );
    }
}