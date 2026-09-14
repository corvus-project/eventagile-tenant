<?php

use App\Models\Plan;
use App\Models\Tenant;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;

Route::get('/home', function () {
    return redirect()->route('tenant.home');
})->name('tenant.home');

Route::get('/debug', function () {})->name('tenant.debug');
