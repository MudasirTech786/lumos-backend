<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // 📌 GET USERS (SEARCH + PAGINATION)
    public function index(Request $request)
    {
        $query = User::query();

        // 🔎 SEARCH
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%");
            });
        }

        // 📄 PAGINATION
        $users = $query->latest()->paginate(10);

        return response()->json([
            'users' => $users->items(), // data only
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ]
        ]);
    }

    // 📌 CREATE USER
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'role_id' => 'required|exists:roles,id'
        ]);

        $user = User::create([
            'role_id' => $request->role_id,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'message' => 'User created',
            'user' => $user
        ]);
    }

    // 📌 GET SINGLE USER
    public function show($id)
    {
        return response()->json([
            'user' => User::findOrFail($id)
        ]);
    }

    // 📌 UPDATE USER
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $id,
            'role_id' => 'required|exists:roles,id',
        ]);

        // 🔐 GET ROLE NAME
        $newRole = \App\Models\Role::find($request->role_id);

        // 🚫 BLOCK CHANGING ADMIN USER (by name or role)
        if ($user->name === 'Admin' && $newRole->name !== 'Super Admin') {
            return response()->json([
                'message' => 'Admin user can only have Super Admin role'
            ], 403);
        }

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'role_id' => $request->role_id,
        ]);

        return response()->json([
            'message' => 'User updated',
            'user' => $user->load('role')
        ]);
    }

    // 📌 DELETE USER
    public function destroy($id)
    {
        User::findOrFail($id)->delete();

        return response()->json([
            'message' => 'User deleted'
        ]);
    }
}
