<?php

namespace App\Http\Repositories;

use App\Http\Resources\UsersResource;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;

class UserRepository
{
    public function index()
    {
        $users = User::all();

        if (!$users) {
            throw new \Exception('Users not found', 404);
        }

        return UsersResource::collection($users);
    }

    public function show($id)
    {
        $user = User::find($id);

        if (!$user) {
            throw new \Exception('User not found', 404);
        }

        return new UsersResource($user);
    }


    public function update($id, array $data)
    {
        $user = User::find($id);

        if (!$user) {
            throw new \Exception('User not found', 404);
        }

        $user->fill($data);
        $user->save();

        return new UsersResource($user);
    }

    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            throw new \Exception('User not found', 404);
        }

        $user->delete();
    }

    public function forgotPassword(array $data)
    {
        $status = Password::sendResetLink(
            ['email' => $data['email']]
        );


        if ($status == Password::RESET_LINK_SENT) {
            return __($status);
        }

        throw new \Exception('Error occured', 500);
    }

    public function reset(array $data)
    {
        $status = Password::reset(
            [
                'email' => $data['email'],
                'password' => $data['password'],
                'password_confirmation' => $data['password_confirmation'],
                'token' => $data['token'],
            ],
            function ($user) use ($data) {
                $user->forceFill([
                    'password' => Hash::make($data["password"]),
                    'remember_token' => Str::random(60),
                ])->save();

                $user->tokens()->delete();

                event(new PasswordReset($user));
            }
        );

        if ($status == Password::PASSWORD_RESET) {
            return null;
        }

        throw new \Exception('Error occured', 500);
    }

    public function verify($id, array $data)
    {
        if (!$data["hasValidSignature"]) {
            throw new \Exception("Invalid/Expired URL provided.", 401);
        }

        $user = User::findOrFail($id);

        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        return 200;
    }

    public function resend(array $data)
    {
        $user = User::where("email", $data["email"])->first();

        if ($user->hasVerifiedEmail()) {
            throw new \Exception("Email already verified.", 400);
        }

        $user->sendEmailVerificationNotification();

        return null;
    }
}
