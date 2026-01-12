<?php

namespace App\Services\Auth;

use App\Models\UserManagement\Users;
use App\Models\UserManagement\HasPermission;
use App\Models\UserManagement\Permission;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Mail\SendRandomPasswordMail;
use App\Helpers\PasswordHelper;
use Illuminate\Http\Request;

class AuthService
{

    // Get all user (active + inactive)
    public function getAllUsers()
    {
        
        $users = Users::all();
        
        return response()->json([
            'data' => $users,
        ], 200);
    }

    // get a single user
    public function getUserById($uuid)
{
    $user = Users::where('uuid', $uuid)->first();

    if (!$user) {
        return response()->json(['message' => 'User not found'], 404);
    }

    return response()->json([
        'data' => $user,
    ], 200);
}

    //Create new user
    public function register($request)
    {
        if (!$request->user()) {
            return response()->json(['message' => 'Unauthorized. Only logged-in users can create new accounts.'], 403);
        }

        $validated = $request->validate([
            'role_id' => 'nullable|exists:roles,id',
            'employee_id' => 'nullable|string|max:50',
            'salutation' => 'required|string|max:5',
            'username' => 'required|string|max:50',
            'email' => 'required|string|email|unique:users,email',
            'father_name' => 'nullable|string|max:30',
            'mother_name' => 'nullable|string|max:30',
            'gender' => 'required|string|in:Male,Female,Other',
            'marital_status' => 'nullable|boolean',
            'dob' => 'nullable|date',
            'doj' => 'nullable|date',
            'phone_number' => 'required|string|size:10|unique:users,phone_number',
            'emg_number' => 'required|string|size:10',
            'user_photo' => 'nullable|image|max:2048',
            'current_address' => 'nullable|string|max:255',
            'permanent_address' => 'nullable|string|max:255',
            'qualification' => 'nullable|string|max:255',
            'experience' => 'nullable|string|max:255',
            'specialization' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:255',
            'pan_number' => 'required|string|max:30',
            'identification_name' => 'nullable|string|max:50',
            'identification_number' => 'nullable|string|max:20',
        ]);

        $photoPath = $request->hasFile('user_photo')
            ? $request->file('user_photo')->store('user_photos', 'public')
            : null;

        $randomPassword = PasswordHelper::generateRandomPassword();

        $user = Users::create([
            ...$validated,
            'user_photo' => $photoPath,
            'password' => Hash::make($randomPassword),
        ]);

        

        try {
            Mail::to($user->email)->send(new SendRandomPasswordMail($user->email, $randomPassword));
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'User created but failed to send email.',
                'error' => $e->getMessage(),
                'user' => $user,
            ], 201);
        }

        return response()->json(['message' => 'User registered successfully and password sent via email!', 
        'user' => $user,
    ], 201);
    }

    // Login
    public function login($request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        // check if user exist and password match
        $user = Users::where('email', $request->email)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid Credential!'], 404);
        }

        // check if user is active or not
        if (!$user->is_active) {
            return response()->json(['message' => 'Your account is inactive. Please contact admin.'], 403);
        }

        $permId = HasPermission::where('role_id', $user->role_id)->pluck('permission_id');
        $permissions = Permission::whereIn('id', $permId)->pluck('permission_name')->toArray();
        // $token = $user->createToken('auth_token')->plainTextToken;



        Auth::login($user);

        return response()->json([
            'message' => 'Login successful!',
            'user' => $user,
            'permission' => $permissions,
            // 'token' => $token,
        ], 200);
    }

    // logout
    // public function logout(Request $request)
    // {
    //     Auth::guard('web')->logout();
    //     $request->session()->invalidate();
    //     $request->session()->regenerateToken();

    //     return response()->json(['message' => 'Logged out']);
    // }

    public function logout(Request $request)
    {
        try {
            // Logout the user from auth guard
            Auth::guard('web')->logout();
            
            // Try to invalidate session only if it exists
            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }
            
            return response()->json(['message' => 'Logged out successfully'], 200);
            
        } catch (\Exception $e) {
            // Even if session operations fail, consider it a successful logout
            // since the user is already logged out from Auth::guard
            return response()->json(['message' => 'Logged out successfully'], 200);
        }
    }
    


    // Update a User
    public function update($request, $uuid)
{
    // Fix 1: Use where() with uuid column
    $user = Users::where('uuid', $uuid)->first();
    
    if (!$user) {
        return response()->json(['message' => 'User not found'], 404);
    }

    $validated = $request->validate([
        'role_id' => 'nullable|exists:roles,id',
        'employee_id' => 'nullable|string|max:50',
        'salutation' => 'nullable|string|max:5',
        'username' => 'nullable|string|max:50|unique:users,username,' . $uuid . ',uuid',
        'email' => 'nullable|string|email|unique:users,email,' . $uuid . ',uuid',
        'father_name' => 'nullable|string|max:30',
        'mother_name' => 'nullable|string|max:30',
        'gender' => 'nullable|string|in:Male,Female,Other',
        'marital_status' => 'nullable|boolean',
        'dob' => 'nullable|date',
        'doj' => 'nullable|date',
        'phone_number' => 'nullable|string|size:10|unique:users,phone_number,' . $uuid . ',uuid',
        'emg_number' => 'nullable|string|size:10',
        'user_photo' => 'nullable|image|max:2048',
        'current_address' => 'nullable|string|max:255',
        'permanent_address' => 'nullable|string|max:255',
        'qualification' => 'nullable|string|max:255',
        'experience' => 'nullable|string|max:255',
        'specialization' => 'nullable|string|max:255',
        'note' => 'nullable|string|max:255',
        'pan_number' => 'nullable|string|max:30',
        'identification_name' => 'nullable|string|max:50',
        'identification_number' => 'nullable|string|max:20',
    ]);

    if ($request->hasFile('user_photo')) {
        if ($user->user_photo && Storage::disk('public')->exists($user->user_photo)) {
            Storage::disk('public')->delete($user->user_photo);
        }
        $validated['user_photo'] = $request->file('user_photo')->store('user_photos', 'public');
    }

    $user->update($validated);

    return response()->json([
        'message' => 'User updated successfully!', 
        'data' => $user
    ], 200);
}

    // Delete a user
    public function destroy($id)
    {
        $user = Users::findOrFail($id);
        if ($user->user_photo && Storage::disk('public')->exists($user->user_photo)) {
            Storage::disk('public')->delete($user->user_photo);
        }
        $user->delete();
        return response()->json(['message' => 'User deleted successfully!'], 200);
    }


    // Forgot Password
    public function forgetPassword($request)
    {
        $request->validate(['email' => 'required|email']);
        $user = Users::where('email', $request->email)->first();

        if (!$user) return response()->json(['message' => 'Email not found'], 404);

        $newPassword = PasswordHelper::generateRandomPassword();
        $user->update(['password' => Hash::make($newPassword)]);

        Mail::to($user->email)->send(new SendRandomPasswordMail($user->email, $newPassword));

        return response()->json(['message' => 'A new password has been sent to your email address.'], 200);
    }


    // Change Password
    public function changePassword($request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Current password is incorrect.'], 400);
        }

        $user->update(['password' => Hash::make($request->new_password)]);
        return response()->json(['message' => 'Password changed successfully.'], 200);
    }

    // Toggle active/inactive status
    public function toggleStatus($id)
    {
        $users = Users::findOrFail($id);
        $users->is_active = !$users->is_active;
        $users->save();

        $status = $users->is_active ? 'activated' : 'deactivated';

        return response()->json([
            'message' => "Users {$status} successfully.",
            'data' => $users,
        ], 200);
    }
}
