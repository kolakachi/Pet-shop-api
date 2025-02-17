<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\FileController;
use App\Http\Controllers\Api\ProductController;
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
        Route::get('user/orders', [UserController::class, 'getOrders']);

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
            Route::get('/user-listing', [AdminController::class, 'userListing']);
            Route::put('/user-edit/{uuid}', [AdminController::class, 'editUser']);
            Route::delete('/user-delete/{uuid}', [AdminController::class, 'deleteUser']);
        });
    });

    Route::middleware([AdminMiddleware::class])->group(function () {
        Route::get('categories', [CategoryController::class, 'index']);
        Route::get('category/{uuid}', [CategoryController::class, 'get']);
        Route::post('category/create', [CategoryController::class, 'store']);
        Route::put('category/{uuid}', [CategoryController::class, 'update']);
        Route::patch('category/{uuid}', [CategoryController::class, 'update']);
        Route::delete('category/{uuid}', [CategoryController::class, 'delete']);

        Route::post('/product/create', [ProductController::class, 'store']);
        Route::put('/product/{uuid}', [ProductController::class, 'update']);
        Route::delete('/product/{uuid}', [ProductController::class, 'delete']);
    });

    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/product/{uuid}', [ProductController::class, 'get']);
});
