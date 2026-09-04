<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

class Subscription extends Model
{
    use CentralConnection, HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'stripe_customer_id',
        'stripe_subscription_id',
        'stripe_price_id',
        'starts_at',
        'ends_at',
        'trial_ends_at',
        'status',
        'cancellation_reason',
        'cancellation_requested_by',
        'notes',
        'billing_address',
        'payment_method',
        'currency',
        'amount',
        'interval',
        'interval_count',
        'tax_rate',
        'coupon',
        'discount',
        'next_billing_date',
        'last_payment_date',
        'last_payment_status',
        'payment_gateway',
        'external_id',
        'plan_name',
        'plan_description',
        'plan_id',
        'plan_features',
        'plan_limitations',
        'renewal_status',
        'renewal_date',
        'cancellation_date',
        'reactivation_date',
        'source',
        'utm_parameters',
        'referral_code',
        'affiliate_id',
        'metadata',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'next_billing_date' => 'datetime',
        'last_payment_date' => 'datetime',
        'plan_limitations' => 'array',
        'plan_features' => 'array',
        'amount' => 'decimal:2',
    ];

    /**
     * A subscription is considered active when its status is "active" or
     * "cancelled" (in grace period) and the current time is within its
     * [starts_at, ends_at] window.
     */
    public function isActive(): bool
    {
        if (! $this->isAccessibleStatus()) {
            return false;
        }

        $now = now();

        if ($this->starts_at && $now->lt($this->starts_at)) {
            return false;
        }

        if ($this->ends_at && $now->gt($this->ends_at)) {
            return false;
        }

        return true;
    }

    public function isExpired(): bool
    {
        return ! $this->isActive();
    }

    public function isCancelled(): bool
    {
        return in_array($this->status, ['cancelled', 'canceled'], true);
    }

    /**
     * A subscription is in its grace period if it has been cancelled
     * but the user still has access until the ends_at date.
     */
    public function isInGracePeriod(): bool
    {
        return $this->isCancelled() && $this->ends_at && now()->lt($this->ends_at);
    }

    protected function isAccessibleStatus(): bool
    {
        return in_array($this->status, ['active', 'cancelled', 'canceled'], true);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['active', 'cancelled', 'canceled'])
            ->where(function ($q) {
                $q->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            })
            ->where(function ($q) {
                $q->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            });
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Read a single plan limitation, honouring "unlimited" markers
     * (null / -1 / "unlimited"). Returns $default when absent.
     */
    public function limitation(string $key, int|float|string|null $default = null): mixed
    {
        $planLimitations = $this->getPlanLimitations();
        $value = $planLimitations[$key] ?? $default;
        if (is_numeric($value) && (int) $value === -1) {
            return null;
        }

        if (in_array($value, ['unlimited', '-1', null], true)) {
            return null;
        }

        return $value;
    }

    private function getPlanLimitations(): ?array
    {
        return is_array($this->plan_limitations) ? $this->plan_limitations : json_decode($this->plan_limitations, true);
    }

    public function isUnlimited(string $key): bool
    {
        return is_null($this->limitation($key));
    }
}
