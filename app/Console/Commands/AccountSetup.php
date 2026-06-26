<?php

namespace App\Console\Commands;

use App\Events\OnboardTenant;
use App\Models\AccountSetup as ModelsAccountSetup;
use App\Models\Tenant;
use App\Models\User;
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
        $accounts = ModelsAccountSetup::where('action', 'SETUP')->get();
        foreach ($accounts as $account) {
            $tenant = $this->create_tenant($account);
            if ($tenant) {
                event(new OnboardTenant($tenant));
                $account->action = 'FINISHED';
                $account->save();
            }
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
                'domain' => str_slug($account->domain),
            ]);

            Log::debug('Tenant Creation: ', [
                'user' => $user->id,
                'tenant' => $tenant->id,
                'domain' =>  $account->domain
            ]);
            return $tenant;
        }
        return null;
    }
}
