<?php

namespace App\Http\Repositories;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;


class AuthRepository
{
    public function register(array $data)
    {
        $user = User::create([
            "name" => $data['name'],
            "email" => $data['email'],
            "password" => Hash::make($data['password']),
        ]);

        $user->sendEmailVerificationNotification();

        return [$user, $user->createToken("Api Token of " . $user->name)->plainTextToken];
    }

    public function login(array $data)
    {
        if (!Auth::attempt(['email' => $data['email'], 'password' => $data['password']])) {
            throw new \Exception('Credentials do not match', 401);
        }


        $user = User::where("email", $data['email'])->first();

        return [$user, $user->createToken("Api Token of " . $user->name)->plainTextToken];
    }

    public function logout()
    {
        Auth::user()->currentAccessToken()->delete();
    }
}
