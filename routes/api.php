<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;


Route::prefix('v1')->group(function () {
    Route::post('user/create', [UserController::class, 'create']);
    Route::post('user/login', [UserController::class, 'login']);
});