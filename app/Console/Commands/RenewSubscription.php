<?php

namespace App\Console\Commands;

use App\Models\PaymentTransaction;
use App\Models\Subscription;
use App\Services\PaymentService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RenewSubscription extends Command
{
    protected $signature = 'renew-subscription';
    protected $description = 'Renew expired active subscriptions';

    public function handle(): int
    {
        $payment = app(PaymentService::class);
        $renewed = 0;
        $failed = 0;
        $retried = 0;

        $this->processRetryingPayments($payment, $retried);

        $subscriptions = Subscription::query()
            ->where('ends_at', '<=', now())
            ->whereNotIn('renewal_status', ['completed', 'payment_failed'])
            ->get();

        Log::info("Found {$subscriptions->count()} subscription(s) to renew.");
        $this->info("Found {$subscriptions->count()} subscription(s) to renew.");

        foreach ($subscriptions as $subscription) {
            $this->line("Renewing subscription ID: {$subscription->id} for tenant ID: {$subscription->tenant_id}");

            if ($subscription->renewWithPayment($payment)) {
                $renewed++;
                $this->info("  Renewed subscription ID: {$subscription->id}");
            } else {
                $latestTransaction = $subscription->payments()->latest()->first();
                if ($latestTransaction && $latestTransaction->isRetrying()) {
                    $retried++;
                    $this->warn("  Payment retryable for subscription ID: {$subscription->id} - will retry later");
                } else {
                    $failed++;
                    $this->warn("  Failed to renew subscription ID: {$subscription->id}");
                }
            }
        }

        $this->info("Renewed {$renewed} subscription(s).");
        if ($retried > 0) {
            $this->warn("{$retried} subscription(s) will be retried.");
        }
        if ($failed > 0) {
            $this->warn("{$failed} subscription(s) could not be renewed.");
        }

        Log::info("Renewed {$renewed} subscription(s). Retried: {$retried}. Failed: {$failed}.");

        return self::SUCCESS;
    }

    private function processRetryingPayments(PaymentService $payment, int &$retried): void
    {
        $retryingTransactions = PaymentTransaction::query()
            ->where('status', 'retrying')
            ->where('next_retry_at', '<=', now())
            ->where('retry_count', '<', 3)
            ->get();

        foreach ($retryingTransactions as $transaction) {
            $subscription = $transaction->subscription;
            if (! $subscription) {
                $transaction->status = 'permanently_failed';
                $transaction->failure_code = 'subscription_not_found';
                $transaction->failure_message = 'Subscription no longer exists';
                $transaction->save();
                continue;
            }

            $this->line("Retrying payment for subscription ID: {$subscription->id}");
            $result = $payment->charge(
                $subscription->stripe_customer_id,
                $subscription->amount,
                $subscription->currency,
                ['subscription_id' => $subscription->id, 'retry' => true]
            );

            $transaction->retry_count++;

            if ($result['success']) {
                $transaction->status = 'succeeded';
                $transaction->transaction_id = $result['transaction_id'];
                $transaction->save();

                $subscription->last_payment_date = now();
                $subscription->last_payment_status = 'succeeded';
                $subscription->payment_gateway = 'fake';
                $subscription->renewal_status = null;
                $subscription->save();

                PaymentTransaction::create([
                    'tenant_id' => $subscription->tenant_id,
                    'subscription_id' => $subscription->id,
                    'transaction_id' => $result['transaction_id'],
                    'type' => 'renewal',
                    'amount' => $subscription->amount,
                    'currency' => $subscription->currency,
                    'status' => 'succeeded',
                    'gateway' => 'fake',
                    'metadata' => $result['metadata'] ?? [],
                ]);

                if ($subscription->renew()) {
                    $retried++;
                    $this->info("  Retry successful for subscription ID: {$subscription->id}");
                    Log::info("Payment retry succeeded for subscription ID: {$subscription->id}");
                }
            } else {
                $transaction->failure_code = $result['code'] ?? 'payment_declined';
                $transaction->failure_message = $result['message'] ?? 'Payment failed';

                if ($result['retryable'] ?? false) {
                    $transaction->status = 'retrying';
                    $retryIntervals = config('payment.retry_intervals', [300, 900, 2700]);
                    $interval = $retryIntervals[min($transaction->retry_count - 1, count($retryIntervals) - 1)] ?? 2700;
                    $transaction->next_retry_at = now()->addSeconds($interval);
                    $transaction->save();

                    $subscription->renewal_status = 'pending';
                    $subscription->save();

                    $this->warn("  Retry pending for subscription ID: {$subscription->id} - next attempt in {$interval}s");
                } else {
                    $transaction->status = 'permanently_failed';
                    $subscription->renewal_status = 'payment_failed';
                    $subscription->last_payment_status = 'failed';
                    $subscription->save();

                    Log::warning("Payment permanently failed for subscription ID: {$subscription->id}: {$transaction->failure_message}");
                    $this->warn("  Payment permanently failed for subscription ID: {$subscription->id}");
                }
            }
        }
    }
}
