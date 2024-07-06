<?php

use KolaKachi\Bacs\Http\Controllers\BacsController;

Route::get('api/bacs-response', [BacsController::class, 'getBacsResponse']);
