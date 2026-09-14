<?php

namespace App\Console\Commands;

use App\Events\OnboardTenant;
use App\Models\AccountSetup as ModelsAccountSetup;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Helper;
use App\Services\PaymentService;
use App\Services\SubscriptionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AccountSetup extends Command
{
    protected $signature = 'account-setup';
    protected $description = 'Command description';

    public function handle(): int
    {
        $accounts = ModelsAccountSetup::whereIn('action', ['SETUP', 'RESETUP'])->get();
        $payment = app(PaymentService::class);
        $subscriptionService = app(SubscriptionService::class);

        Log::debug('Account setup command started. Number of accounts to process: ' . $accounts->count());
        foreach ($accounts as $account) {
            Log::debug('Account setup: ' . $account->user_id);
            $this->info('Account setup: ' . $account->user_id);

            if ($account->action === 'RESETUP') {
                $this->remove_tenant($account->user_id);
                Log::debug('Tenant information is removed: ' . $account->user_id);
                $this->info('Tenant information is removed: ' . $account->user_id);
            }

            $tenant = $this->create_tenant($account);
            if ($tenant) {
                if ($account->action === 'SETUP') {
                    $this->processPaymentForSetup($tenant, $account, $payment, $subscriptionService);
                }

                event(new OnboardTenant($tenant));
                $account->action = 'FINISHED';
                $account->save();
                $this->info('Tenant setup finished: ' . $tenant->id);
                Log::debug('Tenant setup finished: ' . $tenant->id);
            }
        }

        return self::SUCCESS;
    }

    private function processPaymentForSetup(Tenant $tenant, ModelsAccountSetup $account, PaymentService $payment, SubscriptionService $subscriptionService): void
    {
        $planId = $account->config['plan_id'] ?? null;
        $plan = $planId ? Plan::find($planId) : Plan::query()->where('is_active', true)->first();

        if (! $plan) {
            Log::warning('No plan found for account setup, creating trial subscription without payment.');
            return;
        }

        $result = $subscriptionService->createSubscriptionForTenant($tenant, $plan, $payment);

        if (! $result['success']) {
            Log::warning('Payment failed for account setup: ' . $result['error']);
            $account->error_message = $result['error'];
            $account->save();

            if ($this->isRetryableFailure($result['error'])) {
                Log::info('Payment failure is retryable, will retry on next run.');
            }
        }
    }

    private function isRetryableFailure(string $error): bool
    {
        $retryableMessages = ['gateway timeout', 'unavailable', 'maintenance', 'temporary'];
        foreach ($retryableMessages as $msg) {
            if (stripos($error, $msg) !== false) {
                return true;
            }
        }
        return false;
    }

    private function remove_tenant(int $user_id): void
    {
        $tenant = Tenant::where('user_id',  $user_id)->first();
        if ($tenant) {
            $tenant->domains()->delete();
            $tenant->delete();
        }
    }

    private function create_tenant(ModelsAccountSetup $account): ?Tenant
    {
        $user = User::find($account->user_id);
        if ($user) {
            $tenant = Tenant::create([
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_active' => true,
            ]);

            $tenant->domains()->create([
                'domain' => str_slug($account->domain) . '.' . parse_url(config('app.url'), PHP_URL_HOST)
            ]);

            Log::debug('Tenant Creation: ', [
                'user' => $user->id,
                'tenant' => $tenant->id,
                'domain' =>  $account->domain . '.' . Helper::appdomain()
            ]);
            return $tenant;
        }
        return null;
    }
}
