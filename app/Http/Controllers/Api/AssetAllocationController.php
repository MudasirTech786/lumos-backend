<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InventoryAsset;
use App\Services\AssetAllocationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AssetAllocationController extends Controller
{
    public function allocate(
        Request $request,
        InventoryAsset $asset,
        AssetAllocationService $service
    ) {

        $request->validate([
            'shoot_id' => ['required', 'exists:shoots,id'],
            'notes' => ['nullable', 'string'],
        ]);

        $allocation = $service->allocate(
            $asset,
            $request->shoot_id,
            Auth::user()->id,
            $request->notes
        );

        return response()->json($allocation);
    }

    public function returnAsset(
        InventoryAsset $asset,
        AssetAllocationService $service
    ) {

        $allocation = $service->returnAsset(
            $asset,
            Auth::user()->id
        );

        return response()->json($allocation);
    }
}