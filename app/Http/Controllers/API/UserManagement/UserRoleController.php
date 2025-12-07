<?php

namespace App\Http\Controllers\API\UserManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserManagement\Users;
use App\Models\UserManagement\Role;

class UserRoleController extends Controller
{

    // Assign a role to a user
 
    public function assignRole(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role_id' => 'required|exists:roles,id',
        ]);

        $user = Users::findOrFail($validated['user_id']);
        $user->role_id = $validated['role_id'];
        $user->save();

        return response()->json([
            'message' => 'Role assigned successfully.',
            'user' => $user->load('role'),
        ]);
    }

     //role for specific user

    public function getUserRole($user_id)
    {
        $user = Users::with('role')->findOrFail($user_id);

        return response()->json([
            'user' => $user->username,
            'role' => $user->role ? $user->role->role_name : 'No role assigned',
        ]);
    }
}
