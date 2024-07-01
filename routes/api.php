<?php

use App\Http\Controllers\Api\UserController;
use App\Http\Middleware\JwtMiddleware;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::middleware([JwtMiddleware::class])->group(function () {
        Route::get('user', [UserController::class, 'getUser']);
        Route::delete('user', [UserController::class, 'deleteUser']);
        Route::put('user/edit', [UserController::class, 'edit']);
        Route::get('user/logout', [UserController::class, 'logout']);
    });
    Route::post('user/create', [UserController::class, 'create']);
    Route::post('user/login', [UserController::class, 'login']);
    Route::post('user/forgot-password', [UserController::class, 'forgotPassword']);
    Route::post('user/reset-password-token', [UserController::class, 'resetPasswordToken']);
});
