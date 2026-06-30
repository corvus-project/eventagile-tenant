<?php

namespace App\Providers;

use App\Policies\EventPolicy;
use App\Policies\UserPolicy;
use App\Services\Helper;
use App\Services\VerifyEmailQueued;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Database\Eloquent\Model;
use Livewire\Livewire;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\User;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void {}

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventLazyLoading(
            fn($query) => $this->app->environment('local')
                ? logger()->warning('Lazy loading detected: ' . $query->toSql())
                : null
        );

        Model::handleLazyLoadingViolationUsing(function (Model $model, string $relation) {
            $class = $model::class;

            info("Attempted to lazy load [{$relation}] on model [{$class}].");
        });

        Gate::define('create-user', [UserPolicy::class, 'create']);
        Gate::define('update-user', [UserPolicy::class, 'update']);

        Gate::define('create-event', [EventPolicy::class, 'create']);
        Gate::define('update-event', [EventPolicy::class, 'update']);

        Gate::define('delete-event', [EventPolicy::class, 'delete']);
        Gate::define('view-event', [EventPolicy::class, 'view']);
        Gate::define('view-any-event', [EventPolicy::class, 'viewAny']);

        Gate::define('update-settings', function (User $user) {
            return $user->hasRole('admin') ?? false;
        });

        /*         if (app()->environment('local', 'staging') && !$this->isMigrationOrSeederCommand()) {
            DB::listen(function ($query) {
                File::append(
                    storage_path('/logs/query.log'),
                    $query->sql . ' [' . implode(', ', $query->bindings) . ']' . PHP_EOL
                );
            });
        } */
        RateLimiter::for('login', function (string $email, string $ip) {
            return Limit::perMinute(5)->by($email . $ip);
        });


        /*         VerifyEmail::toMailUsing(function (object $notifiable, string $url) {
            Log::debug('URL: ' . $url);
            Log::debug('Request Url:' . request()->url());
            Log::debug('notifiable', [$notifiable]);

            $tenant_domain = substr(request()->url(), 0, strpos(request()->url(), 'livewire/updat'));
            $verify_url = substr($url, strpos($url, 'email/verify'), strlen($url));

            $tenant_url = $tenant_domain  . $verify_url;
            Log::debug('Tenant URL: ' . $tenant_url);
            $verifiedUrl = Helper::tenantUrl();

            return (new MailMessage)
                ->subject('Verify Email Address')
                ->line('Click the button below to verify your email address.')
                ->action('Verify Email Address', $tenant_url);
        }); */
    }

    private function isMigrationOrSeederCommand(): bool
    {
        $command = request()->server('argv')[1] ?? '';
        return in_array($command, ['migrate', 'migrate:fresh', 'migrate:reset', 'db:seed']);
    }
}
