<?php

namespace App\Http\Repositories;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;


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
        if (!Auth::attempt($data)) {
            throw new \Exception('Credentials do not match', 401);
        }

        $user = Auth::user();
        $refreshToken = Str::random(60);
        $expiry = now()->addWeeks(2);

        $accessToken = $user->createToken('access_token');
        $accessToken->accessToken->expires_at = now()->addHour(1); // Set token expiration time (e.g., 1 minute)
        $accessToken->accessToken->save();

        $user->refresh_token = hash('sha256', $refreshToken);
        $user->refresh_token_expiry = $expiry;
        $user->save();

        return [$user, "Access Token: " . $accessToken->plainTextToken, "Refresh Token: " . $refreshToken];
    }


    public function refreshToken(array $data)
    {
        $refreshToken = $data["refresh_token"];

        // Find the user by the refresh token
        $user = User::where('refresh_token', hash('sha256', $refreshToken))->first();

        if (!$user) {
            throw new \Exception('Invalid refresh token', 401);
        }

        if (now()->gt($user->refresh_token_expiry)) {
            // Refresh token has expired
            $user->refresh_token = null;
            $user->refresh_token_expiry = null;
            $user->save();

            throw new \Exception('Refresh token has expired. Please log in again', 401);
        }

        // Revoke previous access tokens
        $user->tokens()->where('name', 'access_token')->delete();

        // Create and save a new access token
        $accessToken = $user->createToken('access_token');
        $accessToken->accessToken->expires_at = now()->addHour(1);
        $accessToken->accessToken->save();

        return "Access token: " . $accessToken->plainTextToken;
    }

    public function logout()
    {
        Auth::user()->currentAccessToken()->delete();
    }
}
