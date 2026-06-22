<?php

use App\Enums\RegistrationStatus;
use App\Models\Event;
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
        Schema::create('event_registrations', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->foreignIdFor(Event::class, 'event_id')->constrained()->onDelete('cascade');
            $table->foreignIdFor(User::class, 'user_id')->constrained()->onDelete('cascade');
            $table->boolean('is_attending')->default(false);
            $table->timestamp('registered_at')->useCurrent();
            $table->enum('status', array_column(RegistrationStatus::cases(), 'value'))->default(RegistrationStatus::PENDING->value);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_registrations');
    }
};
