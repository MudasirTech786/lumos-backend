<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Models\User;

use Illuminate\Support\Facades\Hash;

use Spatie\Permission\Models\Role;

class AuthController extends Controller
{
    // REGISTER
    public function register(Request $request)
    {
        $request->validate([

            'name' => 'required|string|max:255',

            'email' => 'required|email|unique:users',

            'password' => 'required|min:6'
        ]);

        // CREATE USER
        $user = User::create([

            'name' => $request->name,

            'email' => $request->email,

            'password' => Hash::make(
                $request->password
            ),
        ]);

        // ASSIGN VIEWER ROLE IF EXISTS
        if (Role::where('name', 'viewer')->exists()) {

            $user->assignRole('viewer');
        }

        // CREATE TOKEN
        $token = $user
            ->createToken('api-token')
            ->plainTextToken;

        return response()->json([

            'message' => 'User registered successfully',

            'user' => $user->load('roles'),

            'token' => $token

        ], 201);
    }

    // LOGIN
    public function login(Request $request)
    {
        $request->validate([

            'email' => 'required|email',

            'password' => 'required'
        ]);

        $user = User::where(
            'email',
            $request->email
        )->first();

        if (
            !$user ||
            !Hash::check(
                $request->password,
                $user->password
            )
        ) {

            return response()->json([

                'message' => 'Invalid credentials'

            ], 401);
        }

        // GENERATE TOKEN
        $token = $user
            ->createToken('api-token')
            ->plainTextToken;

        return response()->json([

            'message' => 'Login successful',

            'token' => $token,

            'user' => $user,

            'roles' => $user
                ->getRoleNames()
                ->toArray(),

            'permissions' => $user
                ->getAllPermissions()
                ->pluck('name')
                ->toArray(),
        ]);
    }

    // LOGOUT
    public function logout(Request $request)
    {
        $request
            ->user()
            ->currentAccessToken()
            ->delete();

        return response()->json([

            'message' => 'Logged out successfully'

        ]);
    }

    // AUTH USER
    public function me(Request $request)
    {
        if (!$request->user()) {

            return response()->json([

                'message' => 'Unauthenticated'

            ], 401);
        }

        $user = $request->user()->load([
            'roles',
            'employee'
        ]);

        return response()->json([

            'user' => $user,

            'roles' => $user
                ->getRoleNames()
                ->toArray(),

            'permissions' => $user
                ->getAllPermissions()
                ->pluck('name')
                ->toArray(),
        ]);
    }
}
