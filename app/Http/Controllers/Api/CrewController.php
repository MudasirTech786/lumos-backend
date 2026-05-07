<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CrewMember;
use App\Http\Requests\StoreCrewRequest;
use App\Services\CrewService;

class CrewController extends Controller
{
    public function __construct(
        protected CrewService $crewService
    ) {}

    public function index(Request $request)
    {
        $query = CrewMember::query();

        // SEARCH
        if ($request->search) {

            $query->where(function ($q) use ($request) {

                $q->where('name', 'LIKE', "%{$request->search}%")
                    ->orWhere('phone', 'LIKE', "%{$request->search}%")
                    ->orWhere('designation', 'LIKE', "%{$request->search}%");
            });
        }

        // STATUS
        if ($request->filled('is_active')) {

            $query->where(
                'is_active',
                $request->is_active
            );
        }

        // EMPLOYMENT TYPE
        if ($request->employment_type) {

            $query->where(
                'employment_type',
                $request->employment_type
            );
        }

        return response()->json([
            'crew' => $query
                ->latest()
                ->paginate(10)
        ]);
    }

    public function show(CrewMember $crew)
    {
        return response()->json([
            'crew' => $crew
        ]);
    }

    public function store(StoreCrewRequest $request)
    {
        $crew = $this->crewService->create(
            $request->validated()
        );

        return response()->json([
            'message' => 'Crew created',
            'crew' => $crew
        ]);
    }

    public function update(StoreCrewRequest $request, CrewMember $crew)
    {
        $crew = $this->crewService->update(
            $crew,
            $request->validated()
        );

        return response()->json([
            'message' => 'Updated',
            'crew' => $crew
        ]);
    }

    public function destroy(CrewMember $crew)
    {
        $this->crewService->delete($crew);

        return response()->json([
            'message' => 'Deleted'
        ]);
    }
}
