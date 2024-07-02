<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\FileController;
use App\Http\Controllers\Api\UserController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\JwtMiddleware;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::middleware([JwtMiddleware::class])->group(function () {
        Route::get('user', [UserController::class, 'getUser']);
        Route::delete('user', [UserController::class, 'deleteUser']);
        Route::put('user/edit', [UserController::class, 'edit']);
        Route::get('user/logout', [UserController::class, 'logout']);

        Route::post('file/upload', [FileController::class, 'upload']);
        Route::get('file/{uuid}', [FileController::class, 'download']);
    });
    Route::post('user/create', [UserController::class, 'create']);
    Route::post('user/login', [UserController::class, 'login']);
    Route::post('user/forgot-password', [UserController::class, 'forgotPassword']);
    Route::post('user/reset-password-token', [UserController::class, 'resetPasswordToken']);

    Route::prefix('admin')->group(function () {
        Route::post('/login', [AdminController::class, 'login']);
        Route::middleware([AdminMiddleware::class])->group(function () {
            Route::post('/create', [AdminController::class, 'create']);
            Route::get('/logout', [AdminController::class, 'logout']);
        });
    });
});
