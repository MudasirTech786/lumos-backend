<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shoot;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class ShootController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | LIST
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        return Shoot::latest()->get();
    }

    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $request->validate([

            'title' => 'required|string|max:255',
        ]);

        $shoot = Shoot::create([

            'title' => $request->title,

            'slug' => Str::slug(
                $request->title . '-' . time()
            ),

            'client_name' => $request->client_name,

            'location' => $request->location,

            'shoot_date' => $request->shoot_date,

            'status' => $request->status ?? 'planned',

            'notes' => $request->notes,

            'created_by' => Auth::id(),
        ]);

        return response()->json([
            'message' => 'Shoot created successfully',
            'shoot' => $shoot
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW
    |--------------------------------------------------------------------------
    */

    public function show(Shoot $shoot)
    {
        return $shoot->load('crewMembers');
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    public function update(Request $request, Shoot $shoot)
    {
        $request->validate([

            'title' => 'required|string|max:255',
        ]);

        $shoot->update([

            'title' => $request->title,

            'client_name' => $request->client_name,

            'location' => $request->location,

            'shoot_date' => $request->shoot_date,

            'status' => $request->status,

            'notes' => $request->notes,
        ]);

        return response()->json([
            'message' => 'Shoot updated successfully',
            'shoot' => $shoot
        ]);
    }

    public function assignCrew(
        Request $request,
        Shoot $shoot
    ) {
        $request->validate([

            'crew_members' => 'required|array',
        ]);

        /*
    |--------------------------------------------------------------------------
    | SYNC CREW MEMBERS
    |--------------------------------------------------------------------------
    */

        $syncData = [];

        foreach (
            $request->crew_members
            as $crewId
        ) {

            $syncData[$crewId] = [

                'status' => 'assigned',
            ];
        }

        $shoot->crewMembers()->syncWithoutDetaching(
            $syncData
        );

        return response()->json([

            'message' =>
            'Crew assigned successfully',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */

    public function destroy(Shoot $shoot)
    {
        $shoot->delete();

        return response()->json([
            'message' => 'Shoot deleted successfully'
        ]);
    }
}
