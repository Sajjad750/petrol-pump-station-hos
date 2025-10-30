<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('hos_commands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('station_id')->constrained()->onDelete('cascade');
            $table->string('command_type', 100)->comment('update_fuel_grade_price, schedule_fuel_grade_price etc');
            $table->json('command_data')->comment('Command payload with fuel grade ID, price, scheduled_at, etc.');
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamp('executed_at')->nullable();
            $table->timestamp('acknowledged_at')->nullable();
            $table->integer('retry_count')->default(0);
            $table->timestamps();

            $table->index(['station_id', 'status']);
            $table->index(['status', 'created_at']);
            $table->index(['command_type', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hos_commands');
    }
};
