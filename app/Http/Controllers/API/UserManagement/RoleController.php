<?php

namespace App\Http\Controllers\API\UserManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserManagement\Role;
class RoleController extends Controller
{

    // Get all roles
    public function getAllRole()
    {
        $roles = Role::all();

        return response()->json([
            'data' => $roles,
        ], 200);
    }

    public function createRole(Request $request)
    {
        $validate = $request->validate([
            'role_name' => 'required|string|max:255',
        ]);

        $role = Role::create([
            'role_name' => $validate['role_name'],
        ]);

        return response()->json([
            'message' => 'Role created successfully.',
            'role' => $role,
        ]);
    }

    // public function assignPermission(){

    // }

    // public function revokePermission(){
        
    // }
}
