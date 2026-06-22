<?php


use function Laravel\Folio\{middleware, name};

name('users.show');
middleware(['auth', 'verified', 'role:admin']);
?>

<x-layouts.admin>

    <x-slot name="title">
        {{ __('User Detail: ') . $user->name }}
    </x-slot>

    <x-slot name="header">
        <h2 class="text-lg font-semibold leading-tight text-gray-800 dark:text-gray-200">
            {{ __('User Detail: ') . $user->name }}
        </h2>
    </x-slot>


    <div class="bg-white dark:bg-gray-800 shadow rounded p-6">

        <div class="mb-4">
            <strong>{{ __('Name:') }}</strong> {{ $user->name }}
        </div>
        <div class="mb-4">
            <strong>{{ __('Email:') }}</strong> {{ $user->email }}
            @if ($user->email_verified_at)
            <span class="text-green-600 font-semibold">(Verified)</span>
            @else
            <span class="text-red-600 font-semibold">(Not Verified)</span>
            @endif

        </div>
        <div class="mb-4">
            <strong>{{ __('Role:') }}</strong> {{ $user->roles->pluck('name')->join(', ') }}
        </div>
        <div class="mb-4">
            <strong>{{ __('Created At:') }}</strong> {{ $user->created_at->format('F j, Y, g:i a') }}
        </div>
        <div class="mb-4">
            <strong>{{ __('Updated At:') }}</strong> {{ $user->updated_at->format('F j, Y, g:i a') }}
        </div>


        <h2>Subscriptions</h2>
        <div class="mb-4">
            @if ($user->subscriptions->isEmpty())
            <p>No subscriptions found.</p>
            @else
            <ul class="list-disc list-inside">
                @foreach ($user->subscriptions as $subscription)
                <li>
                    <strong>Plan:</strong> {{ $subscription->plan_name }} |
                    <strong>Status:</strong> {{ $subscription->status }} |
                    <strong>Started At:</strong> {{ $subscription->created_at->format('F j, Y') }} |
                    <strong>Ends At:</strong> {{ $subscription->ends_at ? $subscription->ends_at->format('F j, Y') : 'N/A' }}
                </li>
                @endforeach
            </ul>
            @endif  
    </div>


</x-layouts.admin>