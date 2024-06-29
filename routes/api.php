<?php

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;


// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

// Register

Route::post('register', [ApiController::class, 'register']);
Route::post('login', [ApiController::class, 'login']);
Route::get('categories', [CategoryController::class, 'index']);
Route::get('products', [ProductController::class, 'index']);

Route::group(['middleware' => 'auth:sanctum'], function () {
    // profile
    Route::get('profile', [ApiController::class, 'profile']);
    Route::get('logout', [ApiController::class, 'logout']);

    // for role = admin
    Route::post('categories', [CategoryController::class, 'store'])->middleware('isAdmin');
    Route::patch('categories/{id}', [CategoryController::class, 'update'])->middleware('isAdmin');
    Route::delete('categories/{id}', [CategoryController::class, 'destroy'])->middleware('isAdmin');

    Route::post('products', [ProductController::class, 'store'])->middleware('isAdmin');
});

// Register, Login, Profile and Logout
