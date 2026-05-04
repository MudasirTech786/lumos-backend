<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\PermissionController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// For AuthController API routes, we have:
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    // User management routes
    Route::apiResource('users', UserController::class);

    // Roles and Permissions routes
    Route::apiResource('roles', RoleController::class);
    Route::apiResource('permissions', PermissionController::class);
});
