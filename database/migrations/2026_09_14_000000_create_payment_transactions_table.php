<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained()->nullOnDelete();
            $table->string('transaction_id')->unique();
            $table->string('type');
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('usd');
            $table->string('status')->default('pending');
            $table->string('gateway')->default('fake');
            $table->string('payment_method')->nullable();
            $table->json('response')->nullable();
            $table->json('metadata')->nullable();
            $table->string('failure_code')->nullable();
            $table->text('failure_message')->nullable();
            $table->unsignedInteger('retry_count')->default(0);
            $table->timestamp('next_retry_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
