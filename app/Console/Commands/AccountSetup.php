<?php

namespace App\Console\Commands;

use App\Events\OnboardTenant;
use App\Models\AccountSetup as ModelsAccountSetup;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Helper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AccountSetup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'account-setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $accounts = ModelsAccountSetup::whereIn('action', ['SETUP', 'RESETUP'])->get();

        Log::debug('Account setup command started. Number of accounts to process: ' . $accounts->count());
        foreach ($accounts as $account) {

            Log::debug('Account setup: ' . $account->user_id);
            $this->info('Account setup: ' . $account->user_id);
            if ($account->action === 'RESETUP') {
                $this->remove_tenant($account->user_id);
                Log::debug('Tenant information is removed: ' . $account->useer_id);
                $this->info('Tenant information is removed: ' . $account->useer_id);
            }

            $tenant = $this->create_tenant($account);
            if ($tenant) {
                event(new OnboardTenant($tenant));
                $account->action = 'FINISHED';
                $account->save();
                $this->info('Tenant setup finished: ' . $tenant->id);
                Log::debug('Tenant setup finished: ' . $tenant->id);
            }
        }
    }

    private function remove_tenant(int $user_id)
    {
        $tenant = Tenant::where('user_id',  $user_id)->first();

        $tenant->domains()->delete();
        $tenant->delete();
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
                'domain' => str_slug($account->domain) . '.' . config('app.url'),
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
