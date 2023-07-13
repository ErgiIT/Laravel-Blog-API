<?php

namespace App\Http\Controllers;

use App\Http\Repositories\UserRepository;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\ResendEmailVerificationRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;

class UserController extends Controller
{
    use HttpResponses;

    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function index()
    {
        try {
            $users = $this->userRepository->index();

            return $this->success($users);
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), $e->getCode());
        }
    }

    public function show($id)
    {
        try {
            $users = $this->userRepository->show($id);

            return $this->success($users);
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), $e->getCode());
        }
    }

    public function update($id, UpdateUserRequest $request)
    {
        try {
            $validatedData = $request->validated();

            $comment = $this->userRepository->update($id, $validatedData);

            return $this->success($comment, 'User updated successfully', 201);
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), $e->getCode());
        }
    }

    public function destroy($id)
    {
        try {
            $this->userRepository->destroy($id);

            return $this->success(null, "User deleted successfully", 201);
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), $e->getCode());
        }
    }

    public function forgotPassword(ForgotPasswordRequest $request)
    {
        try {
            $validatedData = $request->validated();

            $forgotPassword = $this->userRepository->forgotPassword($validatedData);

            return $this->success($forgotPassword, 'Reset link sent to your email successfully', 201);
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), $e->getCode());
        }
    }

    public function reset(ResetPasswordRequest $request)
    {
        try {
            $validatedData = $request->validated();

            $resetPassword = $this->userRepository->reset($validatedData);

            return $this->success($resetPassword, 'Password reset successfully', 201);
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), $e->getCode());
        }
    }

    public function verify($id, Request $request)
    {
        try {
            $validatedData = [
                "hasValidSignature" => $request->hasValidSignature()
            ];

            $verify = $this->userRepository->verify($id, $validatedData);
            return $this->success($verify, 'User verified successfully', 201);
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), $e->getCode());
        }
    }

    public function resend(ResendEmailVerificationRequest $request)
    {
        try {
            $validatedData = $request->validated();

            $verify = $this->userRepository->resend($validatedData);
            return $this->success($verify, 'Email resent successfully', 201);
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), $e->getCode());
        }
    }
}
