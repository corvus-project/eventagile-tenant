<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'subscription_id',
        'transaction_id',
        'type',
        'amount',
        'currency',
        'status',
        'gateway',
        'payment_method',
        'response',
        'metadata',
        'failure_code',
        'failure_message',
        'retry_count',
        'next_retry_at',
    ];

    protected $casts = [
        'response' => 'array',
        'metadata' => 'array',
        'next_retry_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public function subscription(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function tenant(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed' || $this->status === 'permanently_failed';
    }

    public function isSucceeded(): bool
    {
        return $this->status === 'succeeded';
    }

    public function isRetrying(): bool
    {
        return $this->status === 'retrying';
    }
}
