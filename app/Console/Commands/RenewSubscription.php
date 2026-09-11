<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use Illuminate\Console\Command;

class RenewSubscription extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'renew-subscription';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Renew expired active subscriptions';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $subscriptions = Subscription::query()
            ->where('status', 'active')
            ->where('ends_at', '<=', now())
            ->get();

        $renewed = 0;
        $failed = 0;
        foreach ($subscriptions as $subscription) {
            $this->line("Renewing subscription ID: {$subscription->id} for tenant ID: {$subscription->tenant_id}");
            if ($subscription->renew()) {
                $renewed++;
            } else {
                $failed++;
                $this->warn("  Failed to renew subscription ID: {$subscription->id}");
            }
        }

        $this->info("Renewed {$renewed} subscription(s).");
        if ($failed > 0) {
            $this->warn("{$failed} subscription(s) could not be renewed.");
        }

        return self::SUCCESS;
    }
}
