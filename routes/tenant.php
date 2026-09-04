<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\TenantEmailVerificationController;
use App\Http\Controllers\Auth\TenantLogoutController;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Features\UserImpersonation;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomainOrSubdomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

Route::middleware([
    'web',
    'universal',
    'tenant.subscription',
    InitializeTenancyByDomainOrSubdomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {
    Route::livewire('/subscription-expired', 'pages::tenants.subscription-expired')->name('tenant.subscription.expired');
    Route::livewire('/', 'pages::tenants.home')->name('tenant.home');
    Route::livewire('/list', 'pages::tenants.list')->name('tenant.list');
    Route::livewire('/events/{event:slug}', 'pages::tenants.view')->name('tenant.event.view');
    Route::livewire('/about', 'pages::tenants.about')->name('tenant.about');
    Route::livewire('/notenant', 'pages::tenants.notenant')->name('tenant.notenant');

    Route::get('/impersonate/{token}', function ($token) {
        return UserImpersonation::makeResponse($token);
    });

    Route::middleware(['auth', 'verified'])->group(function () {
        Route::livewire('/mypage', 'pages::tenants.mypage')->name('tenant.my-page');
        Route::livewire('/profile', 'pages::tenants.profile')->name('tenant.profile');
    });

    Route::middleware(['auth', 'verified', 'role:admin'])->group(function () {

        Route::livewire('/dashboard', 'pages::dashboard.home')->name('dashboard');
        Route::livewire('/dashboard/events', 'pages::dashboard.events')->name('dashboard.events');
        Route::livewire('/dashboard/users', 'pages::dashboard.users')->name('dashboard.users');
        Route::livewire('/dashboard/users/{user:id}/registrations', 'pages::dashboard.users.[User].registrations')->name('dashboard.users.registrations');
        Route::livewire('/dashboard/registration/{eventRegistration:id}/view', 'pages::dashboard.registration')->name('dashboard.registration.view');
        Route::livewire('/dashboard/events/create', 'pages::dashboard.events.create')->name('dashboard.events.create');
        Route::livewire('/dashboard/events/{event:slug}/update', 'pages::dashboard.events.[Event].update')->name('dashboard.events.update');
        Route::livewire('/dashboard/events/{event:slug}', 'pages::dashboard.events.[Event]')->name('dashboard.events.show');
        Route::livewire('/dashboard/events/{event:slug}/registrations', 'pages::dashboard.events.[Event].registrations')->name('dashboard.events.registrations.show');
        Route::livewire('/dashboard/events/{event:slug}/export', 'pages::dashboard.events.[Event].export')->name('dashboard.events.registrations.export');
        Route::livewire('/dashboard/profile/edit', 'pages::dashboard.profile.edit')->name('profile.edit');
        Route::livewire('/dashboard/reports', 'pages::dashboard.reports')->name('reports.index');
        Route::livewire('/dashboard/settings', 'pages::dashboard.settings')->name('settings.index');
        Route::livewire('/dashboard/subscription', 'pages::dashboard.subscription')->name('dashboard.subscription');
    });

    Route::livewire('/auth/login', 'pages::auth.login')->name('login');
    Route::livewire('/auth/register', 'pages::auth.register')->name('register');
    Route::livewire('/auth/forget-password', 'pages::auth.password.reset')->name('password.request');
    Route::livewire('/auth/forget-reset/{token}', 'pages::auth.password.[token]')->name('password.reset');

    Route::middleware('auth')->group(function () {

        Route::livewire('/auth/verify', 'pages::auth.verify')->name('verification.notice');
        Route::get('email/verify/{id}/{hash}', TenantEmailVerificationController::class)
            ->middleware('signed')
            ->name('tenant.verification.verify');

        Route::post('t/logout', TenantLogoutController::class)
            ->name('tenant.logout');
    });
});
