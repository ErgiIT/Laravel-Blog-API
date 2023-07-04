<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\NewPasswordController;
use App\Http\Controllers\PostsController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\VerificationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

//Public Routes
Route::post("/login", [AuthController::class, "login"]);
Route::post("/register", [AuthController::class, "register"]);

//Private Routes

Route::group(["middleware" => ["auth:sanctum"]], function () {
    Route::group(["middleware" => ["verified"]], function () {
        Route::resource("/posts", PostsController::class);
    });
    Route::post("/logout", [AuthController::class, "logout"]);
});

//Users Routes
Route::resource("/users", UsersController::class);


Route::get('email/verify/{id}', [VerificationController::class, "verify"])->name('verification.verify'); // Make sure to keep this as your route name
Route::post('email/resend', [VerificationController::class, 'resend'])->name('verification.resend');

Route::post('forgot-password', [NewPasswordController::class, 'forgotPassword']);
Route::post('reset-password', [NewPasswordController::class, 'reset']);
