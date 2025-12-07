<?php

namespace App\Http\Controllers\API\UserManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Auth\AuthService;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    // view all user
    public function index()
    {
        return $this->authService->getAllusers();
    }

    // // view a specific user
    public function show($uuid)
    {
        return $this->authService->getUserById($uuid);
    }

    // Register a new User
    public function register(Request $request) { 
        return $this->authService->register($request); 
    }

    // Login
    public function login(Request $request) { 
        return $this->authService->login($request);
    }
    
    public function user(Request $request)
    {
        return response()->json($request->user());
    }
    
    // Logout
    public function logout(Request $request) {
        return $this->authService->logout($request);
    }

    // Update
    public function update(Request $request, $uuid) {
        return $this->authService->update($request, $uuid);
    }

    // Delete 
    public function destroy($id) {
        return $this->authService->destroy($id);
    }

    // Forgot Password
    public function forgetPassword(Request $request) {
        return $this->authService->forgetPassword($request);
    }

    // Change Password
    public function changePassword(Request $request) {
        return $this->authService->changePassword($request);
    }

    // Toggle active/inactive status
    public function toggle($id)
    {
        return $this->authService->toggleStatus($id);
    }
}
