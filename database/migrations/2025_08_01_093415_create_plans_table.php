<?php

use App\Enums\PlanInterval;
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
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('currency', 3)->default('gbp');
            $table->enum('interval', array_column(PlanInterval::cases(), 'value'))->default(PlanInterval::MONTH->value); // e.g., month, year
            $table->integer('interval_count')->default(1);
            $table->string('stripe_price_id')->nullable();
            $table->json('features')->nullable(); // JSON or comma-separated features
            $table->json('limitations')->nullable(); // JSON or comma-separated limitations
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
