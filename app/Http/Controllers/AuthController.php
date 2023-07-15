<?php

namespace App\Http\Controllers;

use App\Http\Repositories\AuthRepository;
use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\RefreshTokenRequest;
use App\Http\Requests\StoreUserRequest;
use App\Traits\HttpResponses;


class AuthController extends Controller
{
    use HttpResponses;

    protected $authRepository;

    public function __construct(AuthRepository $authRepository)
    {
        $this->authRepository = $authRepository;
    }


    public function register(StoreUserRequest $request)
    {
        try {
            $validatedData = $request->validated();

            $user = $this->authRepository->register($validatedData);

            return $this->success($user, 'User created successfully', 201);
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), $e->getCode());
        }
    }

    public function login(LoginUserRequest $request)
    {
        try {
            $validatedData = $request->validated();

            $user = $this->authRepository->login($validatedData);

            return $this->success($user, 'User logged in successfully', 201);
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), $e->getCode());
        }
    }

    public function refreshToken(RefreshTokenRequest $request)
    {
        try {
            $validatedData = $request->validated();

            $user = $this->authRepository->refreshToken($validatedData);

            return $this->success($user, 'User access token refreshed successfully', 201);
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), $e->getCode());
        }
    }

    public function logout()
    {
        try {
            $logout = $this->authRepository->logout();

            return $this->success($logout, "User logged out successfully", 200);
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), $e->getCode());
        }
    }
}
