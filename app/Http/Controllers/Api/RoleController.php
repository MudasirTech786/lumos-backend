<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use Spatie\Permission\Models\Role;

use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    // GET ROLES
    public function index(Request $request)
    {
        $query = Role::with('permissions');

        // SEARCH
        if ($request->search) {

            $query->where(
                'name',
                'like',
                "%{$request->search}%"
            );
        }

        $roles = $query
            ->latest()
            ->paginate(10);

        return response()->json([

            'roles' => $roles
        ]);
    }

    // CREATE ROLE
    public function store(Request $request)
    {
        $request->validate([

            'name' => 'required|string|unique:roles,name',

            'permissions' => 'nullable|array',
        ]);

        // CREATE ROLE
        $role = Role::create([

            'name' => $request->name,

            'guard_name' => 'web',
        ]);

        // ASSIGN PERMISSIONS
        if ($request->permissions) {

            $permissions = Permission::whereIn(
                'id',
                $request->permissions
            )->pluck('name');

            $role->syncPermissions(
                $permissions
            );
        }

        return response()->json([

            'message' => 'Role created',

            'role' => $role->load('permissions')
        ]);
    }

    // SHOW ROLE
    public function show($id)
    {
        return response()->json([

            'role' => Role::with('permissions')
                ->findOrFail($id)
        ]);
    }

    // UPDATE ROLE
    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $request->validate([

            'name' => 'required|string|unique:roles,name,' . $id,

            'permissions' => 'nullable|array',
        ]);

        // UPDATE ROLE
        $role->update([

            'name' => $request->name,
        ]);

        // SYNC PERMISSIONS
        $permissions = Permission::whereIn(
            'id',
            $request->permissions ?? []
        )->pluck('name');

        $role->syncPermissions(
            $permissions
        );

        return response()->json([

            'message' => 'Role updated',

            'role' => $role->load('permissions')
        ]);
    }

    // DELETE ROLE
    public function destroy($id)
    {
        Role::findOrFail($id)
            ->delete();

        return response()->json([

            'message' => 'Role deleted'
        ]);
    }
}