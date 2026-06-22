<?php

use App\Enums\PlanInterval;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Tenant::class, 'tenant_id')->constrained()->onDelete('cascade');


            $table->string('stripe_customer_id')->nullable();
            $table->string('stripe_subscription_id')->nullable();
            $table->string('stripe_price_id')->nullable();

            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->dateTime('trial_ends_at')->nullable();
            $table->string('status')->default('active'); // e.g., active, canceled, past_due
            $table->string('cancellation_reason')->nullable();
            $table->string('cancellation_requested_by')->nullable(); // e.g., user, admin
            $table->text('notes')->nullable();
            $table->string('billing_address')->nullable();
            $table->string('payment_method')->nullable(); // e.g., card, bank_transfer
            $table->string('currency')->default('usd');
            $table->decimal('amount', 10, 2)->default(0.00);
            $table->enum('interval', array_column(PlanInterval::cases(), 'value'))->default(PlanInterval::MONTH->value); // e.g., month, year
            $table->integer('interval_count')->default(1);
            $table->string('tax_rate')->nullable();
            $table->string('coupon')->nullable();
            $table->string('discount')->nullable();
            $table->string('next_billing_date')->nullable();
            $table->string('last_payment_date')->nullable();
            $table->string('last_payment_status')->nullable(); // e.g., succeeded, failed
            $table->string('payment_gateway')->nullable(); // e.g., stripe, paypal
            $table->string('external_id')->nullable(); // ID from external systems


            $table->string('plan_name')->nullable();
            $table->string('plan_description')->nullable();
            $table->foreignIdFor(Plan::class)->constrained()->onDelete('cascade');
            $table->string('plan_features')->nullable(); // JSON or comma-separated features
            $table->json('plan_limitations')->nullable(); // JSON or comma-separated limitations



            $table->string('renewal_status')->nullable(); // e.g., pending, completed
            $table->string('renewal_date')->nullable();
            $table->string('cancellation_date')->nullable();
            $table->string('reactivation_date')->nullable();
            $table->string('source')->nullable(); // e.g., web, mobile
            $table->string('utm_parameters')->nullable(); // JSON or comma-separated UTM params
            $table->string('referral_code')->nullable();
            $table->string('affiliate_id')->nullable();
            $table->string('metadata')->nullable(); // JSON for any additional data
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
