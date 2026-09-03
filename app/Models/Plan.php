<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

class Plan extends Model
{
    use CentralConnection, HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'currency',
        'interval',
        'interval_count',
        'stripe_price_id',
        'features',
        'limitations',
        'is_active',
    ];

    protected $casts = [
        'features' => 'array',
        'limitations' => 'array',
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function getFeaturesAttribute($value)
    {
        return $this->castJson($value);
    }

    public function getLimitationsAttribute($value)
    {
        return $this->castJson($value);
    }

    public function limitation(string $key, int|float|null $default = 0): int|float|null
    {
        $limitations = $this->limitations ?? [];

        $value = $limitations[$key] ?? null;

        if (is_int($value) || is_float($value)) {
            return $value;
        }

        return $default;
    }

    public function hasUnlimited(string $key): bool
    {
        $value = ($this->limitations ?? [])[$key] ?? null;

        return $value === null || $value === -1;
    }

    protected function castJson($value)
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_null($value)) {
            return null;
        }

        return json_decode($value, true);
    }
}
