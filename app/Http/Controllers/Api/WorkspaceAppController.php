<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WorkspaceApp;

class WorkspaceAppController extends Controller
{
    // GET APPS (with optional tab filter)
    public function index(Request $request)
    {
        $query = WorkspaceApp::query();

        if ($request->has('tab') && $request->tab) {
            $query->where('workspace_tab', $request->tab);
        }

        return response()->json([
            'apps' => $query->latest()->get()
        ]);
    }

    // CREATE APP
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'category' => 'nullable',
            'url' => 'required|url',
            'logo' => 'nullable',
            'workspace_tab' => 'required|in:tab1,tab2,tab3',
        ]);

        $app = WorkspaceApp::create($data);

        return response()->json([
            'message' => 'App created',
            'app' => $app
        ]);
    }

    // UPDATE APP (IMPORTANT for tab reassignment)
    public function update(Request $request, $id)
    {
        $app = WorkspaceApp::findOrFail($id);

        $data = $request->validate([
            'name' => 'sometimes',
            'category' => 'sometimes',
            'url' => 'sometimes|url',
            'logo' => 'sometimes',
            'workspace_tab' => 'sometimes|in:tab1,tab2,tab3',
        ]);

        $app->update($data);

        return response()->json([
            'message' => 'App updated',
            'app' => $app
        ]);
    }

    // DELETE APP
    public function destroy($id)
    {
        $app = WorkspaceApp::findOrFail($id);
        $app->delete();

        return response()->json([
            'message' => 'App deleted successfully'
        ]);
    }
}
