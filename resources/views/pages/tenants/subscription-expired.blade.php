<?php

use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.ea-yoga')]  class extends Component
{
    public function isExpired()
    {
        $tenant = tenant();
        if (! $tenant) {
            return true;
        }

        return ! \App\Services\SubscriptionService::can($tenant, 'access-site');
    }
};
?>

<x-slot name="title">
    {{ __('Subscription Expired') }}
</x-slot>

<x-slot name="tenant_name">
    {{ \App\Models\Setting::get('site_name') ?? 'Site' }}
</x-slot>

<div class="flex min-h-screen items-center justify-center bg-gray-50 dark:bg-gray-900 py-12 px-4">
    <div class="w-full max-w-lg rounded-xl bg-white dark:bg-gray-800 shadow-lg p-8 text-center">
        <div class="flex justify-center mb-6">

            <x-heroicon-s-exclamation-triangle class="w-12 h-12 text-amber-500 mr-2 size-5" />
        </div>

        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-4">
            {{ __('Your Subscription Has Expired') }}
        </h1>

        <p class="text-gray-600 dark:text-gray-300 mb-6">
            {{ __('Your account access has been suspended because your plan is no longer active or has expired.') }}
        </p>

        <p class="text-gray-600 dark:text-gray-300 mb-8">
            {{ __('Please contact the site owner or renew your subscription to restore access.') }}
        </p>

        @auth
        <form method="POST" action="{{ route('tenant.logout') }}">
            @csrf
            <button type="submit"
                class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-6 py-3 text-sm font-semibold text-white transition-colors hover:bg-blue-700">
                {{ __('Logout') }}
            </button>
        </form>
        @else
        <a href="{{ route('login') }}"
            class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-6 py-3 text-sm font-semibold text-white transition-colors hover:bg-blue-700">
            {{ __('Login') }}
        </a>
        @endauth
    </div>
</div>