<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\TransferController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\AdminAccountController;
use App\Http\Controllers\Api\AdminAccountingController;


Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login',    [AuthController::class, 'login']);
});

Route::middleware('auth:api')->group(function () {

    Route::prefix('auth')->group(function () {
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::post('logout',  [AuthController::class, 'logout']);
        Route::get('me',       [AuthController::class, 'me']);
    });

    Route::prefix('users/me')->group(function () {
        Route::get('/',          [ProfileController::class, 'me']);
        Route::put('/',          [ProfileController::class, 'updateProfile']);
        Route::patch('password', [ProfileController::class, 'updatePassword']);
        Route::delete('/',       [ProfileController::class, 'destroy']);
    });

    Route::prefix('accounts')->group(function () {
        Route::get('/',    [AccountController::class, 'index']);
        Route::post('/',   [AccountController::class, 'store']);
        Route::get('{id}', [AccountController::class, 'show']);

        Route::post('{id}/co-owners',            [AccountController::class, 'addCoHolder']);
        Route::get('{id}/co-owners',             [AccountController::class, 'listCoHolders']);   
        Route::delete('{id}/co-owners/{userId}', [AccountController::class, 'removeCoHolder']);

        Route::post('{id}/guardian',             [AccountController::class, 'assignGuardian']);

        Route::patch('{id}/convert',             [AccountController::class, 'convert']);

        Route::delete('{id}',                    [AccountController::class, 'requestClosure']);

        Route::get('{id}/transactions',          [TransactionController::class, 'indexByAccount']);
    });

    Route::prefix('transfers')->group(function () {
        Route::get('/',    [TransferController::class, 'index']);  
        Route::post('/',   [TransferController::class, 'store']);
        Route::get('{id}', [TransferController::class, 'show']);
    });

    Route::get('transactions/{id}', [TransactionController::class, 'show']);

    Route::middleware('admin')->group(function () {

        Route::prefix('admin/accounts')->group(function () {
            Route::get('/',              [AdminAccountController::class, 'index']);
            Route::get('{id}',           [AdminAccountController::class, 'show']);   
            Route::patch('{id}/block',   [AdminAccountController::class, 'block']);
            Route::patch('{id}/unblock', [AdminAccountController::class, 'unblock']);
            Route::patch('{id}/close',   [AdminAccountController::class, 'close']);
        });

        Route::prefix('admin/accounting')->group(function () {
            Route::post('monthly-routine', [AdminAccountingController::class, 'runMonthlyRoutine']); 
        });

    });
});