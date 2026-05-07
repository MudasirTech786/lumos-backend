<?php

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\WorkspaceAppController;
use App\Http\Controllers\Api\CrewController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\LeaveController;

Route::get('/user', function (Request $request) {

    return $request->user();
})->middleware('auth:sanctum');


// AUTH ROUTES
Route::post(
    '/register',
    [AuthController::class, 'register']
);

Route::post(
    '/login',
    [AuthController::class, 'login']
);


// PROTECTED ROUTES
Route::middleware('auth:sanctum')->group(function () {

    // AUTH
    Route::post(
        '/logout',
        [AuthController::class, 'logout']
    );

    Route::get(
        '/me',
        [AuthController::class, 'me']
    );


    // USERS
    Route::apiResource(
        'users',
        UserController::class
    );

    // GET ROLES LIST
    Route::get(
        '/roles-list',
        [UserController::class, 'roles']
    );


    // ROLES
    Route::apiResource(
        'roles',
        RoleController::class
    );

    // PERMISSIONS
    Route::apiResource(
        'permissions',
        PermissionController::class
    );


    // PROFILE
    Route::post(
        '/profile',
        [ProfileController::class, 'update']
    );


    // WORKSPACE APPS
    Route::get(
        '/workspace-apps',
        [WorkspaceAppController::class, 'index']
    );

    Route::post(
        '/workspace-apps',
        [WorkspaceAppController::class, 'store']
    );

    Route::put(
        '/workspace-apps/{id}',
        [WorkspaceAppController::class, 'update']
    );

    Route::delete(
        '/workspace-apps/{id}',
        [WorkspaceAppController::class, 'destroy']
    );


    // CREW
    Route::apiResource(
        'crew',
        CrewController::class
    );


    // EMPLOYEES
    Route::apiResource(
        'employees',
        EmployeeController::class
    );


    // LEAVES
    Route::apiResource(
        'leaves',
        LeaveController::class
    )->parameters([
        'leaves' => 'leave'
    ]);
});
