<?php

namespace App\Http\Controllers\API\UserManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserManagement\Role;
use App\Models\UserManagement\Permission;

class HasPermissionController extends Controller
{
    
    // Assign a permission to a role
    
    public function assignPermission(Request $request)
    {
        $validated = $request->validate([
            'role_id' => 'required|exists:roles,id',
            'permission_id' => 'required|exists:permissions,id',
        ]);

        $role = Role::findOrFail($validated['role_id']);
        $role->permissions()->attach($validated['permission_id']);

        return response()->json([
            'message' => 'Permission assigned successfully.',
            'role' => $role->load('permissions'),
        ]);
    }

    
    // Revoke a permission from a role
    
    public function revokePermission(Request $request)
    {
        $validated = $request->validate([
            'role_id' => 'required|exists:roles,id',
            'permission_id' => 'required|exists:permissions,id',
        ]);

        $role = Role::findOrFail($validated['role_id']);
        $role->permissions()->detach($validated['permission_id']);

        return response()->json([
            'message' => 'Permission revoked successfully.',
            'role' => $role->load('permissions'),
        ]);
    }

    
    // List all permissions assigned to a specific role
    
    public function getRolePermissions($role_id)
    {
        $role = Role::with('permissions')->findOrFail($role_id);

        return response()->json([
            'role' => $role->role_name,
            'permissions' => $role->permissions,
        ]);
    }
}
