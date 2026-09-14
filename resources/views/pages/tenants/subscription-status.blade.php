<?php $__env->startSection('title', 'Subscription Status'); ?>
<?php $__env->startSection('tenant_name', config('app.name')); ?>

<div class="mx-auto max-w-4xl py-8 px-4">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Subscription Status</h1>

    @if ($subscription)
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4">
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $subscription->plan_name ?? 'No Plan' }}</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Status: <span class="font-medium <?php echo $subscription->status === 'active' ? 'text-green-600' : 'text-red-600'; ?>">
                        {{ ucfirst($subscription->status) }}
                    </span>
                </p>
            </div>
            @if($trial)
            <div class="mt-3 sm:mt-0 bg-blue-50 dark:bg-blue-900/30 text-blue-800 dark:text-blue-200 px-4 py-2 rounded-lg text-sm">
                Trial Period
            </div>
            @endif
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
            <div>
                <p class="text-gray-500 dark:text-gray-400">Starts At</p>
                <p class="text-gray-900 dark:text-white font-medium">{{ $subscription->starts_at?->format('F j, Y H:i') }}</p>
            </div>
            <div>
                <p class="text-gray-500 dark:text-gray-400">Ends At</p>
                <p class="text-gray-900 dark:text-white font-medium">{{ $subscription->ends_at?->format('F j, Y H:i') }}</p>
            </div>
            @if($trialEndsAt)
            <div>
                <p class="text-gray-500 dark:text-gray-400">Trial Ends</p>
                <p class="text-gray-900 dark:text-white font-medium">{{ $trialEndsAt->format('F j, Y H:i') }}</p>
            </div>
            @endif
            <div>
                <p class="text-gray-500 dark:text-gray-400">Last Payment</p>
                <p class="text-gray-900 dark:text-white font-medium">
                    {{ $lastPaymentDate?->format('F j, Y H:i') ?? 'N/A' }}
                    @if($lastPaymentStatus)
                        <span class="ml-1 text-xs <?php echo $lastPaymentStatus === 'succeeded' ? 'text-green-600' : 'text-red-600'; ?>">
                            ({{ ucfirst($lastPaymentStatus) }})
                        </span>
                    @endif
                </p>
            </div>
            @if($renewalStatus)
            <div>
                <p class="text-gray-500 dark:text-gray-400">Renewal Status</p>
                <p class="text-gray-900 dark:text-white font-medium">{{ ucfirst($renewalStatus) }}</p>
            </div>
            @endif
        </div>
    </div>
    @else
    <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800/30 rounded-xl p-6 mb-6">
        <p class="text-yellow-800 dark:text-yellow-200">No active subscription found.</p>
    </div>
    @endif

    {{-- Access Warning --}}
    @if (! $canAccess)
    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800/30 rounded-xl p-6 mb-6">
        <h3 class="text-red-800 dark:text-red-200 font-bold mb-2">Access Suspended</h3>
        <p class="text-red-700 dark:text-red-300">{{ $accessReason }}</p>
    </div>
    @elseif ($renewalStatus === 'payment_failed')
    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800/30 rounded-xl p-6 mb-6">
        <h3 class="text-red-800 dark:text-red-200 font-bold mb-2">Payment Failed</h3>
        <p class="text-red-700 dark:text-red-300">Your subscription renewal payment has failed. Please contact support to update your payment method.</p>
    </div>
    @elseif ($renewalStatus === 'pending')
    <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800/30 rounded-xl p-6 mb-6">
        <h3 class="text-yellow-800 dark:text-yellow-200 font-bold mb-2">Renewal In Progress</h3>
        <p class="text-yellow-700 dark:text-yellow-300">We are processing your subscription renewal. Your access is not affected.</p>
    </div>
    @endif

    {{-- Payment History --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Recent Payment History</h3>

        @if ($paymentHistory->isNotEmpty())
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="text-left py-2 text-gray-500 dark:text-gray-400">Date</th>
                        <th class="text-left py-2 text-gray-500 dark:text-gray-400">Amount</th>
                        <th class="text-left py-2 text-gray-500 dark:text-gray-400">Type</th>
                        <th class="text-left py-2 text-gray-500 dark:text-gray-400">Status</th>
                        <th class="text-left py-2 text-gray-500 dark:text-gray-400">ID</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($paymentHistory as $txn)
                    <tr class="border-b border-gray-100 dark:border-gray-700/50">
                        <td class="py-2 text-gray-700 dark:text-gray-300">{{ $txn->created_at?->format('M j, Y H:i') }}</td>
                        <td class="py-2 text-gray-700 dark:text-gray-300">{{ $txn->amount }} {{ $txn->currency }}</td>
                        <td class="py-2 text-gray-700 dark:text-gray-300">{{ ucfirst($txn->type) }}</td>
                        <td class="py-2">
                            <span class="px-2 py-0.5 rounded text-xs font-medium
                                {{ $txn->status === 'succeeded' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $txn->status === 'failed' ? 'bg-red-100 text-red-800' : '' }}
                                {{ $txn->status === 'retrying' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                {{ $txn->status === 'pending' ? 'bg-gray-100 text-gray-800' : '' }}
                                {{ $txn->status === 'permanently_failed' ? 'bg-red-100 text-red-800' : '' }}
                            ">
                                {{ ucfirst(str_replace('_', ' ', $txn->status)) }}
                            </span>
                        </td>
                        <td class="py-2 text-gray-500 dark:text-gray-500 font-mono text-xs">{{ $txn->transaction_id }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p class="text-gray-500 dark:text-gray-400">No payment history available.</p>
        @endif
    </div>

    <div class="mt-6 text-center">
        <a href="mailto:support@example.com" class="text-primary hover:underline">Contact Support</a>
    </div>
</div>
