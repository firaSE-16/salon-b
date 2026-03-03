<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->foreignId('staff_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('user_id')->nullable(); // Users are in central DB, no FK constraint

            // Customer info (for guest bookings)
            $table->string('customer_name')->nullable();
            $table->string('customer_phone', 40)->nullable();
            $table->string('customer_email', 120)->nullable();

            $table->timestamp('start_at');
            $table->timestamp('end_at');
            $table->enum('status', ['pending', 'confirmed', 'completed', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['salon_id', 'staff_id', 'start_at']);
            $table->index(['salon_id', 'status', 'start_at']);
            $table->index(['staff_id', 'start_at', 'end_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};

