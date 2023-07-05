<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Traits\HttpResponses; // Add this line

class VerificationController extends Controller
{
    use HttpResponses; // Add this line

    public function verify($user_id, Request $request)
    {
        if (!$request->hasValidSignature()) {
            return $this->error(["msg" => "Invalid/Expired URL provided."], "Unauthorized", 401);
        }

        $user = User::findOrFail($user_id);

        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        return $this->success(["msg" => "Email verified successfully"]);
    }

    public function resend(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where("email", $request->email)->first();

        if ($user->hasVerifiedEmail()) {
            return $this->error(["msg" => "Email already verified."], "Bad Request", 400);
        }

        $user->sendEmailVerificationNotification();

        return $this->success(["msg" => "Email verification link sent to your email address"]);
    }
}
