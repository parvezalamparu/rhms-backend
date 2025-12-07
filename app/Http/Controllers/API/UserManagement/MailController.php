<?php

namespace App\Http\Controllers\API\UserManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendRandomPasswordMail;
use App\Helpers\PasswordHelper;

class MailController extends Controller
{
    public function sendRandomPasswordThroughMail(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        // generate random password
        $password = PasswordHelper::generateRandomPassword(8);

        // send mail
        Mail::to($request->email)->send(new SendRandomPasswordMail($request->email, $password));

        return response()->json([
            'message' => 'Account created successfully and your password is- ',
            'password' => $password
        ], 200);
    }
}
