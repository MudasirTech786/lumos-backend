<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\User;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Hash;

use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    // 📌 GET USERS
    public function index(Request $request)
    {
        $query = User::with('roles');

        // SEARCH
        if ($request->filled('search')) {

            $search = $request->search;

            $query->where(function ($q) use ($search) {

                $q->where(
                    'name',
                    'like',
                    "%{$search}%"
                )

                    ->orWhere(
                        'email',
                        'like',
                        "%{$search}%"
                    );
            });
        }

        $users = $query
            ->latest()
            ->paginate(10);

        return response()->json([

            'users' => $users
        ]);
    }

    // 📌 CREATE USER
    public function store(Request $request)
    {
        $request->validate([

            'name' => 'required|string',

            'email' => 'required|email|unique:users',

            'password' => 'required|min:6',

            'role' => 'required|exists:roles,name',
        ]);

        $user = User::create([

            'name' => $request->name,

            'email' => $request->email,

            'password' => Hash::make(
                $request->password
            ),
        ]);

        // ASSIGN ROLE
        $user->assignRole(
            $request->role
        );

        return response()->json([

            'message' => 'User created',

            'user' => $user->load('roles')
        ]);
    }

    // 📌 SHOW SINGLE USER
    public function show($id)
    {
        return response()->json([

            'user' => User::with('roles')
                ->findOrFail($id)
        ]);
    }

    // 📌 UPDATE USER
    // 📌 UPDATE USER
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // ✅ PROTECT SUPER ADMIN
        if ($user->name === 'Super Admin') {
            return response()->json([
                'message' => 'Super Admin cannot be edited'
            ], 403);
        }

        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $id,
            'role' => 'required|exists:roles,name',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        // UPDATE ROLE
        $user->syncRoles([
            $request->role
        ]);

        // UPDATE PASSWORD IF PROVIDED
        if ($request->password) {
            $user->update([
                'password' => Hash::make(
                    $request->password
                )
            ]);
        }

        return response()->json([
            'message' => 'User updated',
            'user' => $user->load('roles')
        ]);
    }

    // 📌 DELETE USER
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // ✅ PROTECT SUPER ADMIN
        if ($user->name === 'Super Admin') {
            return response()->json([
                'message' => 'Super Admin cannot be deleted'
            ], 403);
        }

        $user->delete();

        return response()->json([
            'message' => 'User deleted'
        ]);
    }

    // 📌 GET ROLES
    public function roles()
    {
        return response()->json([

            'roles' => Role::select(
                'id',
                'name'
            )->get()
        ]);
    }
}
