<?php

use App\Enums\PlanInterval;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Services\SubscriptionService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Mary\Traits\Toast;

new #[Layout('layouts.admin')] class extends Component
{
    use Toast;

    public ?Subscription $currentSubscription = null;

    public ?Plan $currentPlan = null;

    public function mount(): void
    {
        $tenant = tenant();

        if (! $tenant instanceof Tenant) {
            return;
        }

        $this->currentSubscription = Subscription::query()
            ->where('tenant_id', $tenant->id)
            ->latest('starts_at')
            ->first();

        if ($this->currentSubscription && $this->currentSubscription->plan_id) {
            $this->currentPlan = Plan::query()->find($this->currentSubscription->plan_id);
        }
    }

    #[Computed]
    public function plans()
    {
        return Plan::query()
            ->where('is_active', true)
            ->orderBy('price')
            ->get();
    }

    #[Computed]
    public function subscriptionHistory()
    {
        $tenant = tenant();

        if (! $tenant instanceof Tenant) {
            return collect();
        }

        return Subscription::query()
            ->where('tenant_id', $tenant->id)
            ->latest('starts_at')
            ->get();
    }

    #[Computed]
    public function usage(): array
    {
        $tenant = tenant();

        if (! $tenant instanceof Tenant) {
            return [
                'events' => 0,
                'registrations' => 0,
            ];
        }

        $service = app(SubscriptionService::class);

        $maxEvents = $service->maxEvents($tenant);
        $maxRegistrations = $service->maxRegistrations($tenant);

        $eventsCount = Event::query()->count();
        $registrationsCount = EventRegistration::query()
            ->where('is_attending', true)
            ->count();

        return [
            'events' => [
                'used' => $eventsCount,
                'max' => $maxEvents,
                'unlimited' => $maxEvents === null,
            ],
            'registrations' => [
                'used' => $registrationsCount,
                'max' => $maxRegistrations,
                'unlimited' => $maxRegistrations === null,
            ],
        ];
    }

    public function formatPrice(?float $price, string $currency = 'GBP'): string
    {
        if ($price === null || (float) $price === 0.0) {
            return 'Free';
        }

        $symbol = match (strtoupper($currency)) {
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            default => strtoupper($currency) . ' ',
        };

        return $symbol . number_format($price, 2);
    }

    public function formatInterval(int $count, string $interval): string
    {
        $unit = PlanInterval::tryFrom($interval)?->label() ?? $interval;

        if ((int) $count === 1) {
            return "per {$unit}";
        }

        return "every {$count} {$unit}s";
    }

    public function planFeatures(?Plan $plan): array
    {
        if (! $plan || ! is_array($plan->features)) {
            return [];
        }

        return $plan->features;
    }

    public function planLimitations(?Plan $plan): array
    {
        if (! $plan || ! is_array($plan->limitations)) {
            return [];
        }

        return $plan->limitations;
    }
};
?>

<x-slot name="title">
    {{ __('Subscription') }}
</x-slot>

<x-slot name="header">
    <h2 class="text-lg font-semibold leading-tight text-gray-800 dark:text-gray-200">
        {{ __('Subscription') }}
    </h2>
</x-slot>

<div class="flex flex-col flex-1">
    <div class="flex flex-col flex-1 pb-5 mx-auto w-full">
        <div class="relative flex-1 w-full">

            {{-- Current Subscription --}}
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg dark:bg-gray-900/50 dark:border dark:border-gray-200/10 p-6 mb-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                        {{ __('Current Subscription') }}
                    </h3>
                    @if($currentSubscription && $currentSubscription->isActive())
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                        {{ __('Active') }}
                    </span>
                    @elseif($currentSubscription)
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                        {{ ucfirst($currentSubscription->status ?? 'inactive') }}
                    </span>
                    @else
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                        {{ __('No subscription') }}
                    </span>
                    @endif
                </div>

                @if($currentSubscription)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Plan') }}</dt>
                        <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100">
                            {{ $currentSubscription->plan_name ?? '—' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Status') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                            {{ ucfirst($currentSubscription->status) }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Starts At') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                            {{ optional($currentSubscription->starts_at)->format('F j, Y') ?? '—' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Ends At') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                            {{ optional($currentSubscription->ends_at)->format('F j, Y') ?? __('No end date') }}
                        </dd>
                    </div>
                    @if($currentSubscription->amount)
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Amount') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                            {{ $this->formatPrice($currentSubscription->amount, $currentSubscription->currency ?? 'GBP') }}
                        </dd>
                    </div>
                    @endif
                    @if($currentSubscription->interval)
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Billing Interval') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                            {{ $this->formatInterval($currentSubscription->interval_count ?? 1, $currentSubscription->interval) }}
                        </dd>
                    </div>
                    @endif
                    @if($currentSubscription->next_billing_date)
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Next Billing') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                            {{ $currentSubscription->next_billing_date->format('F j, Y') }}
                        </dd>
                    </div>
                    @endif
                    @if($currentSubscription->trial_ends_at)
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Trial Ends') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                            {{ $currentSubscription->trial_ends_at->format('F j, Y') }}
                        </dd>
                    </div>
                    @endif
                </div>
                @else
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('You do not have a subscription yet. Choose a plan below to get started.') }}
                </p>
                @endif
            </div>

            {{-- Usage --}}
            @if($currentSubscription)
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg dark:bg-gray-900/50 dark:border dark:border-gray-200/10 p-6 mb-6">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                    {{ __('Usage') }}
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @php
                    $events = $this->usage['events'];
                    $registrations = $this->usage['registrations'];
                    $eventsPct = $events['unlimited'] ? 0 : ($events['max'] > 0 ? min(100, ($events['used'] / $events['max']) * 100) : 0);
                    $regsPct = $registrations['unlimited'] ? 0 : ($registrations['max'] > 0 ? min(100, ($registrations['used'] / $registrations['max']) * 100) : 0);
                    @endphp

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Events') }}</span>
                            <span class="text-sm text-gray-600 dark:text-gray-400">
                                {{ $events['used'] }}
                                @if($events['unlimited'])
                                / {{ __('Unlimited') }}
                                @else
                                / {{ $events['max'] }}
                                @endif
                            </span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                            @if($events['unlimited'])
                            <div class="bg-green-600 h-2 rounded-full" style="width: 100%"></div>
                            @else
                            <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $eventsPct }}%"></div>
                            @endif
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Registrations') }}</span>
                            <span class="text-sm text-gray-600 dark:text-gray-400">
                                {{ $registrations['used'] }}
                                @if($registrations['unlimited'])
                                / {{ __('Unlimited') }}
                                @else
                                / {{ $registrations['max'] }}
                                @endif
                            </span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                            @if($registrations['unlimited'])
                            <div class="bg-green-600 h-2 rounded-full" style="width: 100%"></div>
                            @else
                            <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $regsPct }}%"></div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Available Plans --}}
            <div class="mb-6">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                    {{ __('Available Plans') }}
                </h3>

                @if($this->plans->isEmpty())
                <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6 text-sm text-gray-600 dark:text-gray-400">
                    {{ __('No plans are currently available.') }}
                </div>
                @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($this->plans as $plan)
                    @php
                    $isCurrent = $currentPlan && $currentPlan->id === $plan->id;
                    $features = $this->planFeatures($plan);
                    $limitations = $this->planLimitations($plan);
                    @endphp
                    <div @class([ 'bg-white dark:bg-gray-800 shadow sm:rounded-lg dark:bg-gray-900/50 dark:border dark:border-gray-200/10 p-6 flex flex-col' , 'ring-2 ring-blue-500'=> $isCurrent,
                        ])>
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ $plan->name }}
                            </h4>
                            @if($isCurrent)
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                {{ __('Current') }}
                            </span>
                            @endif
                        </div>

                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                            {{ $plan->description }}
                        </p>

                        <div class="mb-4">
                            <span class="text-3xl font-bold text-gray-900 dark:text-white">
                                {{ $this->formatPrice((float) $plan->price, $plan->currency ?? 'GBP') }}
                            </span>
                            @if((float) $plan->price > 0)
                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                / {{ formatInterval($plan->interval_count ?? 1, $plan->interval ?? 'month') }}
                            </span>
                            @endif
                        </div>

                        @if(!empty($features))
                        <ul class="text-sm text-gray-700 dark:text-gray-300 space-y-1 mb-4 flex-1">
                            @foreach($features as $feature)
                            <li class="flex items-start">
                                <svg class="w-4 h-4 mr-2 mt-0.5 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span>{{ $feature }}</span>
                            </li>
                            @endforeach
                        </ul>
                        @endif

                        @if(!empty($limitations))
                        <div class="text-xs text-gray-500 dark:text-gray-400 mb-4">
                            <div class="font-medium mb-1">{{ __('Limits:') }}</div>
                            <ul class="space-y-0.5">
                                @foreach($limitations as $key => $value)
                                <li>
                                    {{ Str::headline($key) }}:
                                    @if($value === -1 || $value === 'unlimited')
                                    {{ __('Unlimited') }}
                                    @elseif(is_bool($value))
                                    {{ $value ? __('Yes') : __('No') }}
                                    @else
                                    {{ $value }}
                                    @endif
                                </li>
                                @endforeach
                            </ul>
                        </div>
                        @endif

                        <div class="mt-auto">
                            @if($isCurrent)
                            <button type="button" disabled class="w-full inline-flex justify-center items-center px-4 py-2 text-sm font-medium text-gray-400 bg-gray-100 dark:bg-gray-700 dark:text-gray-400 rounded-md cursor-not-allowed">
                                {{ __('Current Plan') }}
                            </button>
                            @else
                            <button type="button" class="w-full inline-flex justify-center items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                {{ __('Switch to this plan') }}
                            </button>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- Subscription History --}}
            @if($this->subscriptionHistory->count() > 0)
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg dark:bg-gray-900/50 dark:border dark:border-gray-200/10 p-6">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                    {{ __('Subscription History') }}
                </h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">{{ __('Plan') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">{{ __('Status') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">{{ __('Starts') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">{{ __('Ends') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">{{ __('Amount') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                            @foreach($this->subscriptionHistory as $sub)
                            <tr>
                                <td class="px-4 py-4 text-sm text-gray-900 dark:text-gray-100">
                                    {{ $sub->plan_name ?? '—' }}
                                </td>
                                <td class="px-4 py-4 text-sm">
                                    <span @class([ 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold' , 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'=> $sub->isActive(),
                                        'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' => !$sub->isActive() && $sub->status === 'cancelled',
                                        'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200' => !$sub->isActive() && $sub->status !== 'cancelled',
                                        ])>
                                        {{ ucfirst($sub->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-300">
                                    {{ optional($sub->starts_at)->format('F j, Y') ?? '—' }}
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-300">
                                    {{ optional($sub->ends_at)->format('F j, Y') ?? '—' }}
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-300">
                                    @if($sub->amount)
                                    {{ $this->formatPrice((float) $sub->amount, $sub->currency ?? 'GBP') }}
                                    @else
                                    —
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

        </div>
    </div>
</div>