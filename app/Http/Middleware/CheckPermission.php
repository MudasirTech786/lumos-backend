<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CheckPermission
{
    public function handle(Request $request, Closure $next, $permission)
{
    $user = $request->user();

    if (!$user) {
        return response()->json(['message' => 'Unauthenticated'], 401);
    }

    // 🔥 SUPER ADMIN BYPASS (FIXED PROPERLY)
    if ($user->role && strtolower($user->role->name) === 'super admin') {
        return $next($request);
    }

    $hasPermission = $user->role?->permissions
        ->pluck('name')
        ->contains($permission);

    if (!$hasPermission) {
        return response()->json(['message' => 'Forbidden'], 403);
    }

    return $next($request);
}
}