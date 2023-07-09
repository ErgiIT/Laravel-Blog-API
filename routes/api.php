<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\NewPasswordController;
use App\Http\Controllers\PostsController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\ShareController;
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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

//Public Routes
Route::get("/guest", [PostsController::class, "index"])->middleware([]);
Route::post("/login", [AuthController::class, "login"]);
Route::post("/register", [AuthController::class, "register"]);


//Private Routes

Route::group(["middleware" => ["auth:sanctum"]], function () {
    Route::group(["middleware" => ["verified"]], function () {
        Route::get("/posts/{id?}", [PostsController::class, "index"]);
        Route::get("/post/{post}", [PostsController::class, "show"]);
        Route::post("/posts", [PostsController::class, 'store']);
        Route::patch("/posts/{id}", [PostsController::class, 'update']);
        Route::delete("/posts/{id}", [PostsController::class, 'destroy']);
        Route::post("posts/{id}/comments", [CommentController::class, "store"]);
        Route::get("comments/{id}", [CommentController::class, "show"]);
        Route::put("comments/{id}", [CommentController::class, "update"]);
        Route::delete("comments/{id}", [CommentController::class, "destroy"]);
        Route::get("ratings/{id}", [RatingController::class, "show"]);
        Route::post("posts/{id}/ratings", [RatingController::class, "upsert"]);
        Route::delete("ratings/{id}", [RatingController::class, "destroy"]);
        Route::post("posts/{id}/shares", [ShareController::class, "store"]);
        Route::patch('posts/shares/{id}', [ShareController::class, 'update']);
        Route::delete('posts/shares/{id}', [ShareController::class, 'destroy']);
    });
    Route::post("/logout", [AuthController::class, "logout"]);
});

//Users Routes
Route::resource("/users", UsersController::class);

//Verification Routes
Route::get('email/verify/{id}', [VerificationController::class, "verify"])->name('verification.verify'); // Make sure to keep this as your route name
Route::post('email/resend', [VerificationController::class, 'resend'])->name('verification.resend');

//Forgot Password
Route::post('forgot-password', [NewPasswordController::class, 'forgotPassword']);
Route::post('reset-password', [NewPasswordController::class, 'reset']);
