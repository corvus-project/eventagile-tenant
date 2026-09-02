<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Services\SubscriptionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantHasActiveSubscription
{
    /**
     * Handle an incoming request.
     *
     * When a tenant's subscription is not active (or has expired), all tenant
     * site access is blocked except for auth/password/impersonate flows and
     * the subscription-expired landing page.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = tenant();

        if (! $tenant) {
            return $next($request);
        }

        if ($this->isAllowed($request)) {
            return $next($request);
        }

        $result = SubscriptionService::canWithReason($tenant, 'access-site');

        if ($result['allowed']) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => $result['reason']], 403);
        }

        return redirect()->route('tenant.subscription.expired');
    }

    protected function isAllowed(Request $request): bool
    {
        if ($request->is('auth/*', 'impersonate/*', 'subscription-expired', 'subscription-expired/*')) {
            return true;
        }

        $routeName = $request->route()?->getName();

        return in_array($routeName, [
            'login',
            'register',
            'password.request',
            'password.reset',
            'verification.notice',
            'tenant.verification.verify',
            'tenant.logout',
            'tenant.subscription.expired',
        ], true);
    }
}
