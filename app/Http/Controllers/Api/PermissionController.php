<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    // GET PERMISSIONS
    public function index(Request $request)
    {
        $query = Permission::query();

        // SEARCH
        if ($request->filled('search')) {

            $query->where(
                'name',
                'like',
                "%{$request->search}%"
            );
        }

        // ALL MODE
        if ($request->boolean('all')) {

            return response()->json([

                'permissions' => $query
                    ->orderBy('name')
                    ->get()
            ]);
        }

        $permissions = $query
            ->latest()
            ->paginate(10);

        return response()->json([

            'permissions' => $permissions
        ]);
    }

    // CREATE
    public function store(Request $request)
    {
        $request->validate([

            'name' => 'required|string|unique:permissions,name',
        ]);

        $permission = Permission::create([

            'name' => $request->name,

            'guard_name' => 'web',
        ]);

        return response()->json([

            'message' => 'Permission created',

            'permission' => $permission
        ]);
    }

    // SHOW
    public function show($id)
    {
        return response()->json([

            'permission' => Permission::findOrFail($id)
        ]);
    }

    // UPDATE
    public function update(Request $request, $id)
    {
        $permission = Permission::findOrFail($id);

        $request->validate([

            'name' => 'required|string|unique:permissions,name,' . $id,
        ]);

        $permission->update([

            'name' => $request->name,
        ]);

        return response()->json([

            'message' => 'Permission updated',

            'permission' => $permission
        ]);
    }

    // DELETE
    public function destroy($id)
    {
        Permission::findOrFail($id)
            ->delete();

        return response()->json([

            'message' => 'Permission deleted'
        ]);
    }
}