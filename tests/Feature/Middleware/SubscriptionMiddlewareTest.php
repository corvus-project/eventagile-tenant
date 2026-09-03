<?php

use App\Http\Middleware\EnsureTenantHasActiveSubscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;

uses(RefreshDatabase::class);

it('allows requests through when no tenant context is initialized', function () {
    $middleware = new EnsureTenantHasActiveSubscription;
    $next = fn ($request) => response('ok', 200);

    $request = Request::create('/auth/login', 'GET');

    $response = $middleware->handle($request, $next);

    expect($response->getStatusCode())->toBe(200)
        ->and($response->getContent())->toBe('ok');
});
