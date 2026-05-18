<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\Shoot;
use App\Models\ShootLogistic;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ShootLogisticController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | STORE / UPDATE
    |--------------------------------------------------------------------------
    */

    public function save(
        Request $request,
        Shoot $shoot
    ) {

        $request->validate([

            'transport_type' =>
            'nullable|string|max:255',

            'vehicle' =>
            'nullable|string|max:255',

            'driver_name' =>
            'nullable|string|max:255',

            'pickup_location' =>
            'nullable|string|max:255',

            'pickup_time' =>
            'nullable|date',

            'dropoff_location' =>
            'nullable|string|max:255',

            'dropoff_time' =>
            'nullable|date',

            'status' =>
            'nullable|in:pending,ready,in_transit,completed,delayed',

            'notes' =>
            'nullable|string',
        ]);

        $logistics = ShootLogistic::create([

            'shoot_id' => $shoot->id,

            'logistics_type' =>
            $request->logistics_type,

            'transport_type' =>
            $request->transport_type,

            'vehicle' =>
            $request->vehicle,

            'driver_name' =>
            $request->driver_name,

            'pickup_location' =>
            $request->pickup_location,

            'pickup_time' =>

            $request->pickup_time
                ? Carbon::parse(
                    $request->pickup_time
                )->format('Y-m-d H:i:s')
                : null,

            'dropoff_time' =>

            $request->dropoff_time
                ? Carbon::parse(
                    $request->dropoff_time
                )->format('Y-m-d H:i:s')
                : null,

            'dropoff_location' =>
            $request->dropoff_location,

            'vendor_name' =>
            $request->vendor_name,

            'reference_number' =>
            $request->reference_number,

            'estimated_cost' =>
            $request->estimated_cost,

            'status' =>
            $request->status ?? 'pending',

            'notes' =>
            $request->notes,
        ]);

        return response()->json([

            'message' =>
            'Logistics saved successfully',

            'logistics' =>
            $logistics,
        ]);
    }

    public function updateStatus(
        Request $request,
        ShootLogistic $logistic
    ) {

        $request->validate([

            'status' =>
            'nullable|in:pending,ready,in_transit,completed,delayed',
        ]);

        $logistic->update([

            'status' => $request->status,
        ]);

        return response()->json([

            'message' =>
            'Status updated',

            'logistic' =>
            $logistic,
        ]);
    }

    public function destroy(
        ShootLogistic $logistic
    ) {

        $logistic->delete();

        return response()->json([

            'message' =>
            'Logistics deleted successfully',
        ]);
    }
}
