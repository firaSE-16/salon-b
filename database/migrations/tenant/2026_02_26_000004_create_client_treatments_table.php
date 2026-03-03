<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('client_treatments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();

            $table->unsignedBigInteger('appointment_id')->nullable(); // FK constraint removed - appointments table migration may not exist yet
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();

            $table->timestamp('performed_at')->nullable();
            $table->text('notes')->nullable();

            $table->unsignedBigInteger('performed_by_user_id')->nullable(); // Users are in central DB, no FK constraint

            $table->timestamps();

            $table->index(['client_id','performed_at']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('client_treatments');
    }
};
