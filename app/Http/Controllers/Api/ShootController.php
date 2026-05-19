<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shoot;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\CrewMember;

class ShootController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | LIST
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        return Shoot::with('crewMembers', 'logistics',)
            ->latest()
            ->get();
    }

    /*
|--------------------------------------------------------------------------
| CALENDAR
|--------------------------------------------------------------------------
*/

    public function calendar()
    {
        return Shoot::select(

            'id',

            'title',

            'start_datetime',

            'end_datetime',

            'status',

            'location'

        )->get();
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

            'start_datetime' => 'nullable|date',

            'end_datetime' =>
            'nullable|date|after:start_datetime',

            'status' =>
            'nullable|in:planned,scheduled,active,completed,cancelled',
        ]);

        $shoot = Shoot::create([

            'title' => $request->title,

            'slug' => Str::slug(
                $request->title . '-' . time()
            ),

            'client_name' => $request->client_name,

            'location' => $request->location,

            'start_datetime' => $request->start_datetime,

            'end_datetime' => $request->end_datetime,

            'status' => $request->status ?? 'planned',

            'notes' => $request->notes,

            'created_by' => Auth::id(),
        ]);

        return response()->json([

            'message' => 'Shoot created successfully',

            'shoot' => $shoot,
        ]);
    }


    public function updateStatus(
        Request $request,
        Shoot $shoot
    ) {

        $request->validate([

            'status' =>
            'required|in:planned,scheduled,active,completed,cancelled',
        ]);

        $shoot->update([

            'status' => $request->status,
        ]);

        return response()->json([

            'message' =>
            'Shoot status updated successfully',

            'shoot' => $shoot,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW
    |--------------------------------------------------------------------------
    */

    public function show(Shoot $shoot)
    {
        $shoot->syncStatus();

        $shoot->load([
            'crewMembers',
            'logistics'
        ]);

        return response()->json([

            ...$shoot->toArray(),

            'crew_members' =>
            $shoot->crewMembers,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        Shoot $shoot
    ) {
        $request->validate([

            'title' => 'required|string|max:255',

            'start_datetime' => 'nullable|date',

            'end_datetime' =>
            'nullable|date|after:start_datetime',

            'status' =>
            'nullable|in:planned,scheduled,active,completed,cancelled',
        ]);

        $shoot->update([

            'title' => $request->title,

            'client_name' => $request->client_name,

            'location' => $request->location,

            'start_datetime' => $request->start_datetime,

            'end_datetime' => $request->end_datetime,

            'status' => $request->status,

            'notes' => $request->notes,
        ]);

        return response()->json([

            'message' => 'Shoot updated successfully',

            'shoot' => $shoot,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | ASSIGN CREW
    |--------------------------------------------------------------------------
    */

    public function assignCrew(
        Request $request,
        Shoot $shoot
    ) {

        $request->validate([

            'crew_members' => 'required|array',
        ]);

        $syncData = [];

        foreach ($request->crew_members as $crew) {

            /*
            |--------------------------------------------------------------------------
            | CONFLICT DETECTION
            |--------------------------------------------------------------------------
            */

            $conflict = DB::table('shoot_crew')

                ->join(
                    'shoots',
                    'shoots.id',
                    '=',
                    'shoot_crew.shoot_id'
                )

                ->where(
                    'shoot_crew.crew_member_id',
                    $crew['id']
                )

                ->when(

                    $crew['call_time'] &&
                        $crew['wrap_time'],

                    function ($query) use ($crew) {

                        $query->where(function ($q) use ($crew) {

                            $q->whereBetween(
                                'shoots.start_datetime',
                                [
                                    $crew['call_time'],
                                    $crew['wrap_time']
                                ]
                            )

                                ->orWhereBetween(
                                    'shoots.end_datetime',
                                    [
                                        $crew['call_time'],
                                        $crew['wrap_time']
                                    ]
                                );
                        });
                    }
                )

                ->exists();

            if ($conflict) {

                return response()->json([

                    'message' =>
                    'Crew member already scheduled during this time.',
                ], 422);
            }

            /*
            |--------------------------------------------------------------------------
            | ASSIGNMENT DATA
            |--------------------------------------------------------------------------
            */

            $syncData[$crew['id']] = [

                'position' =>
                $crew['position'] ?? null,

                'call_time' =>
                $crew['call_time'] ?? null,

                'wrap_time' =>
                $crew['wrap_time'] ?? null,

                'rate' =>
                $crew['rate'] ?? null,

                'status' => 'assigned',

                'notes' =>
                $crew['notes'] ?? null,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | SYNC CREW MEMBERS
        |--------------------------------------------------------------------------
        */

        $shoot->crewMembers()
            ->syncWithoutDetaching($syncData);

        return response()->json([

            'message' =>
            'Crew assigned successfully',
        ]);
    }

    public function removeCrew(
        Shoot $shoot,
        CrewMember $crew
    ) {

        $shoot->crewMembers()
            ->detach($crew->id);

        return response()->json([

            'message' =>
            'Crew removed successfully',
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

            'message' =>
            'Shoot deleted successfully',
        ]);
    }
}
