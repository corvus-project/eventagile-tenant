<?php

namespace App\Http\Controllers;

use App\Models\PaymentTransaction;
use App\Models\Subscription;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;

class SubscriptionStatusController extends Controller
{
    public function show(Request $request, SubscriptionService $subscriptionService)
    {
        $user = $request->user();
        $tenant = tenant();

        if (! $tenant) {
            return redirect()->route('tenant.notenant');
        }

        $subscription = Subscription::query()
            ->where('tenant_id', $tenant->id)
            ->latest('starts_at')
            ->first();

        $paymentHistory = PaymentTransaction::query()
            ->where('tenant_id', $tenant->id)
            ->latest('created_at')
            ->limit(10)
            ->get();

        $service = app(SubscriptionService::class);
        $canResult = $service->canWithReason($tenant, 'access-site');

        return view('pages.tenants.subscription-status', [
            'subscription' => $subscription,
            'paymentHistory' => $paymentHistory,
            'canAccess' => $canResult['allowed'],
            'accessReason' => $canResult['reason'] ?? '',
            'trial' => $canResult['trial'] ?? false,
            'trialEndsAt' => $canResult['trial_ends_at'] ?? null,
            'gracePeriod' => $canResult['grace_period'] ?? false,
            'subscriptionStatus' => $subscription ? $subscription->status : 'no_subscription',
            'renewalStatus' => $subscription ? $subscription->renewal_status : null,
            'lastPaymentStatus' => $subscription ? $subscription->last_payment_status : null,
            'lastPaymentDate' => $subscription ? $subscription->last_payment_date : null,
        ]);
    }
}
