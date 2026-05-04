<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $query = Role::with('permissions');

        if ($request->search) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        $roles = $query->orderBy(
            $request->sort ?? 'id',
            $request->order ?? 'desc'
        )->paginate(10);

        return response()->json([
            'roles' => $roles->items(),
            'meta' => [
                'current_page' => $roles->currentPage(),
                'last_page' => $roles->lastPage(),
            ]
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name',
            'permissions' => 'array'
        ]);

        $role = Role::create([
            'name' => $request->name
        ]);

        $role->permissions()->sync($request->permissions ?? []);

        $this->clearRoleCache($role);

        return response()->json([
            'message' => 'Role created',
            'role' => $role->load('permissions')
        ]);
    }

    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $request->validate([
            'name' => 'required|string|unique:roles,name,' . $id,
            'permissions' => 'array'
        ]);

        $role->update([
            'name' => $request->name
        ]);

        $role->permissions()->sync($request->permissions ?? []);

        $this->clearRoleCache($role);

        return response()->json([
            'message' => 'Role updated',
            'role' => $role->load('permissions')
        ]);
    }

    public function destroy($id)
    {
        $role = Role::findOrFail($id);

        $role->permissions()->detach();
        $role->delete();

        $this->clearRoleCache($role);

        return response()->json([
            'message' => 'Role deleted'
        ]);
    }

    private function clearRoleCache($role)
    {
        foreach ($role->users as $user) {
            Cache::forget("user_permissions_{$user->id}");
        }
    }
}