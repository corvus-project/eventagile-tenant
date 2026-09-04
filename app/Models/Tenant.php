<?php

namespace App\Models;

use App\Services\SubscriptionService;
use Illuminate\Support\Facades\Log;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Models\Domain;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase;
    use HasDomains;

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'user_id',
            'name',
            'email',
            'is_active',
        ];
    }

    public function domains()
    {
        return $this->hasMany(Domain::class);
    }

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function activeSubscription()
    {
        return $this->hasOne(Subscription::class)
            ->ofMany('starts_at', 'max')
            ->whereIn('status', ['active', 'cancelled', 'canceled'])
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            });
    }

    public function adminUsers()
    {
        return $this->run(function () {
            $adminUsers = User::whereHas('roles', function ($query) {
                $query->where('slug', 'admin');
            })->get();

            return $adminUsers;
        });
    }

    public function able(string $action)
    {
        Log::info('Checking ability for user ID: '.$this->id.' and action: '.$action);

        return app(SubscriptionService::class)->can($this, $action);
    }

    public function getPrimaryDomainAttribute()
    {
        return $this->domains()->first()->domain;
    }
}
