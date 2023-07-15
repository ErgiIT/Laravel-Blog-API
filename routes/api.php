<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\ShareController;
use App\Http\Controllers\UserController;
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
Route::get("/guest", [PostController::class, "index"])->middleware([]);
Route::post("/login", [AuthController::class, "login"]);
Route::post("/register", [AuthController::class, "register"]);
Route::post('/refresh', [AuthController::class, 'refreshToken']);



//Private Routes

Route::group(["middleware" => ["auth:sanctum"]], function () {
    Route::group(["middleware" => ["verified"]], function () {
        Route::get("/posts/{own?}", [PostController::class, "index"]);
        Route::get("/post/{post}", [PostController::class, "show"]);
        Route::post("/posts", [PostController::class, 'store']);
        Route::patch("/posts/{id}", [PostController::class, 'update']);
        Route::delete("/posts/{id}", [PostController::class, 'destroy']);
        Route::post("posts/{id}/comments", [CommentController::class, "store"]);
        Route::get("posts/{id}/comments", [CommentController::class, "index"]);
        Route::get("comments/{id}", [CommentController::class, "show"]);
        Route::put("comments/{id}", [CommentController::class, "update"]);
        Route::delete("comments/{id}", [CommentController::class, "destroy"]);
        Route::get("posts/{id}/ratings", [RatingController::class, "index"]);
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
Route::get("/users", [UserController::class, "index"]);
Route::get("/users/{id}", [UserController::class, "show"]);
Route::post("/users", [UserController::class, 'store']);
Route::patch("/users/{id}", [UserController::class, 'update']);
Route::delete("/users/{id}", [UserController::class, 'destroy']);

//Verification Routes
Route::get('email/verify/{id}', [UserController::class, "verify"])->name('verification.verify'); // Make sure to keep this as your route name
Route::post('email/resend', [UserController::class, 'resend'])->name('verification.resend');

//Forgot Password
Route::post('forgot-password', [UserController::class, 'forgotPassword']);
Route::post('reset-password', [UserController::class, 'reset']);
