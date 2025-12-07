<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next, $permission)
    {
        $user = $request->user();

        // If no user or no role, deny
        if (!$user || !$user->role) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Get all permissions for this user's role
        $permissions = $user->role->permissions->pluck('permission_name')->toArray();

        // Check if required permission is in user’s permissions
        if (!in_array($permission, $permissions)) {
            return response()->json(['error' => 'Forbidden: Missing Permission'], 403);
        }

        return $next($request);
    }
}
