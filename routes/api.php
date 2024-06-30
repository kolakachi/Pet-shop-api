<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\JwtMiddleware;
use App\Http\Controllers\Api\UserController;


Route::prefix('v1')->group(function () {
    Route::middleware([JwtMiddleware::class])->group(function () {
        Route::get('user', [UserController::class, 'getUser']);
        Route::delete('user', [UserController::class, 'deleteUser']);
        Route::get('user/logout', [UserController::class, 'logout']);
    });
    Route::post('user/create', [UserController::class, 'create']);
    Route::post('user/login', [UserController::class, 'login']);
});