<?php

namespace App\Http\Controllers\API\UserManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserManagement\Permission;

class PermissionController extends Controller
{
    public function createPermission(Request $request)
    {
        $validated = $request->validate([
            'permission_name' => 'required|string|max:255|unique:permissions,permission_name',
            'is_active' => 'required|boolean',
        ]);

        $permission = Permission::create([
            'permission_name' => $validated['permission_name'],
        ]);

        return response()->json([
            'message' => 'Permission created successfully.',
            'permission' => $permission,
        ]);
    }

}
