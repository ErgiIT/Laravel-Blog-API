<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\ResetPasswordRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use App\Traits\HttpResponses; // Add this line

class NewPasswordController extends Controller
{
    use HttpResponses; // Add this line

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status == Password::RESET_LINK_SENT) {
            return $this->success([
                'status' => __($status)
            ]);
        }

        return $this->error([
            'message' => __($status)
        ], "Error occurred", 500);
    }

    public function reset(ResetPasswordRequest $request)
    {
        $request->validated($request->all());

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                $user->tokens()->delete();

                event(new PasswordReset($user));
            }
        );

        if ($status == Password::PASSWORD_RESET) {
            return $this->success([
                'message' => 'Password reset successfully'
            ]);
        }

        return $this->error([
            'message' => __($status)
        ], "Error occurred", 500);
    }
}
