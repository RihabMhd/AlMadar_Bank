<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProfileController;
use Illuminate\Support\Facades\Route;


Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login',    [AuthController::class, 'login']);
});


Route::middleware('auth:api')->group(function () {


    Route::post('auth/refresh', [AuthController::class, 'refresh']);
    Route::post('auth/logout',  [AuthController::class, 'logout']);

    Route::get('users/me',              [ProfileController::class, 'me']);
    Route::put('users/me',              [ProfileController::class, 'updateProfile']);
    Route::patch('users/me/password',   [ProfileController::class, 'updatePassword']);
});
