<?php

use App\Enums\EventStatus;
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
        Schema::create('events', function (Blueprint $table) {
            $table->id();

            $table->text('title');
            $table->text('description')->nullable();
            $table->text('full_description')->nullable();
            $table->string('slug')->unique()->nullable();
            $table->string('registration_code')->unique()->nullable();
            $table->dateTime('start_time');
            $table->dateTime('registration_deadline')->nullable();
            $table->text('location')->nullable();
            $table->text('organizer')->nullable();
            $table->integer('capacity')->default(0);
            $table->boolean('is_public')->default(true);
            $table->foreignIdFor(User::class, 'organizer_id')->constrained()->onDelete('cascade');
            $table->enum('status', array_column(EventStatus::cases(), 'value'))->default(EventStatus::PENDING->value);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
