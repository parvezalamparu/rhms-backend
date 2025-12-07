<?php

namespace App\Services\UserManagement;

use App\Models\UserManagement\Role;
use App\Models\UserManagement\Permission;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class HasPermissionService
{
    /**
     * Assign a permission to a role
     */
    public function assignPermission($roleId, $permissionId)
    {
        $role = Role::findOrFail($roleId);
        $permission = Permission::findOrFail($permissionId);

        // Check if already assigned
        if ($role->permissions()->where('permissions.id', $permissionId)->exists()) {
            return [
                'success' => false,
                'message' => 'Permission already assigned to this role.',
                'data' => $role->load('permissions'),
            ];
        }

        $role->permissions()->attach($permissionId);

        return [
            'success' => true,
            'message' => 'Permission assigned successfully.',
            'data' => $role->load('permissions'),
        ];
    }

    /**
     * Revoke a permission from a role
     */
    public function revokePermission($roleId, $permissionId)
    {
        $role = Role::findOrFail($roleId);
        $permission = Permission::findOrFail($permissionId);

        // Check if permission exists for role
        if (!$role->permissions()->where('permissions.id', $permissionId)->exists()) {
            return [
                'success' => false,
                'message' => 'Permission not found for this role.',
                'data' => $role->load('permissions'),
            ];
        }

        $role->permissions()->detach($permissionId);

        return [
            'success' => true,
            'message' => 'Permission revoked successfully.',
            'data' => $role->load('permissions'),
        ];
    }

    /**
     * Get all permissions of a role
     */
    public function getRolePermissions($roleId)
    {
        $role = Role::with('permissions')->findOrFail($roleId);

        return [
            'success' => true,
            'message' => 'Permissions retrieved successfully.',
            'data' => [
                'role' => $role->role_name,
                'permissions' => $role->permissions,
            ],
        ];
    }
}
