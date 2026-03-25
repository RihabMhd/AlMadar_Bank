<?php

use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TransferController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\Admin\AdminAccountController;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login',    [AuthController::class, 'login']);
});

Route::middleware('auth:api')->group(function () {

    Route::post('auth/refresh', [AuthController::class, 'refresh']);
    Route::post('auth/logout',  [AuthController::class, 'logout']);

    Route::get('users/me',            [ProfileController::class, 'me']);
    Route::put('users/me',            [ProfileController::class, 'updateProfile']);
    Route::patch('users/me/password', [ProfileController::class, 'updatePassword']);

    Route::get('accounts',                                    [AccountController::class, 'index']);
    Route::post('accounts',                                   [AccountController::class, 'store']);
    Route::get('accounts/{id}',                               [AccountController::class, 'show']);
    Route::post('accounts/{id}/co-owners',                    [AccountController::class, 'addMember']);
    Route::delete('accounts/{id}/co-owners/{userId}',         [AccountController::class, 'removeMember']);
    Route::patch('accounts/{id}/convert',                     [AccountController::class, 'convertAccount']);
    Route::patch('accounts/{id}/closure-consent',             [AccountController::class, 'acceptClosure']);
    Route::delete('accounts/{id}',                            [AccountController::class, 'requestClosure']);

    Route::prefix('transfers')->group(function () {
        Route::post('/', [TransferController::class, 'store']);
        Route::get('/{id}', [TransferController::class, 'show']);
    });

    Route::get('/accounts/{id}/transactions', [TransactionController::class, 'indexByAccount']);
    Route::get('/transactions/{id}', [TransactionController::class, 'show']);

    Route::prefix('admin/accounts')->group(function () {
        Route::get('/', [AdminAccountController::class, 'index']);
        Route::patch('/{id}/block', [AdminAccountController::class, 'block']);
        Route::patch('/{id}/unblock', [AdminAccountController::class, 'unblock']);
        Route::patch('/{id}/close', [AdminAccountController::class, 'close']);
    });
});
