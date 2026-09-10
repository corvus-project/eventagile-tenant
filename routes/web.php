<?php

use Illuminate\Support\Facades\Route;

Route::get('/home', function () {
    return redirect()->route('tenant.home');
})->name('tenant.home');
