<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Free Plan',
                'slug' => 'free-plan',
                'description' => 'A free plan for individual users.',
                'price' => 0.00,
                'currency' => 'gbp',
                'interval' => 'month',
                'interval_count' => 1,
                'features' => json_encode(['Access to basic features', 'Email support']),
                'limitations' => json_encode(['max_events' => 3, 'max_registrations' => 50, 'sending_emails' => false]),
                'is_active' => true,
            ],
            [
                'name' => 'Pro Plan',
                'slug' => 'pro-plan',
                'description' => 'A pro plan for small teams.',
                'price' => 9.99,
                'currency' => 'gbp',
                'interval' => 'month',
                'interval_count' => 1,
                'features' => json_encode(['Access to all features', 'Priority email support', 'Team collaboration']),
                'limitations' => json_encode(['max_events' => 100, 'max_registrations' => 500, 'sending_emails' => true]),
                'is_active' => false,
            ],
            [
                'name' => 'Enterprise Plan',
                'slug' => 'enterprise-plan',
                'description' => 'An enterprise plan for large organizations.',
                'price' => 14.99,
                'currency' => 'gbp',
                'interval' => 'month',
                'interval_count' => 1,
                'features' => json_encode(['Dedicated account manager', '24/7 support', 'Custom integrations']),
                'limitations' => json_encode(['max_events' => -1, 'max_registrations' => -1, 'sending_emails' => true]),
                'is_active' => false,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(
                ['slug' => $plan['slug']],
                $plan
            );
        }
    }
}
