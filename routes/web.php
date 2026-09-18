<?php

use App\Http\Controllers\Auth\LogoutController;
use Illuminate\Support\Facades\Route;


Route::get('/debug', function () {})->name('tenant.debug');

Route::middleware('auth')->group(function () {
    Route::post('logout', LogoutController::class)
        ->name('logout');
});
