<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    // 📌 GET ALL PERMISSIONS (with search + pagination)
    public function index(Request $request)
{
    $query = Permission::query();

    if ($request->filled('search')) {
        $query->where('name', 'like', "%{$request->search}%");
    }

    // ✅ SAFE FULL LIST MODE
    if ($request->boolean('all')) {
        return response()->json([
            'permissions' => $query->orderBy('name')->get()
        ]);
    }

    $sort = $request->get('sort', 'id');
    $order = $request->get('order', 'desc');

    $permissions = $query->orderBy($sort, $order)->paginate(10);

    return response()->json([
        'permissions' => $permissions->items(),
        'meta' => [
            'current_page' => $permissions->currentPage(),
            'last_page' => $permissions->lastPage(),
        ]
    ]);
}

    // 📌 CREATE PERMISSION
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:permissions,name',
        ]);

        $permission = Permission::create([
            'name' => $request->name,
        ]);

        return response()->json([
            'message' => 'Permission created',
            'permission' => $permission
        ]);
    }

    // 📌 SHOW SINGLE
    public function show($id)
    {
        return Permission::findOrFail($id);
    }

    // 📌 UPDATE
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

    // 📌 DELETE
    public function destroy($id)
    {
        Permission::findOrFail($id)->delete();

        return response()->json([
            'message' => 'Permission deleted'
        ]);
    }
}
