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
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();

            // BOS fields (mirroring BOS structure)
            $table->dateTime('start_time')->nullable();
            $table->dateTime('start_time_utc')->nullable();
            $table->dateTime('end_time')->nullable();
            $table->dateTime('end_time_utc')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->text('notes')->nullable();
            $table->enum('close_type', ['manual', 'auto'])->default('manual');
            $table->enum('status', ['started', 'completed'])->default('started');
            $table->dateTime('auto_close_time')->nullable();
            $table->timestamp('auto_close_time_utc')->nullable();

            // HOS-specific additions
            $table->foreignId('station_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('bos_shift_id')->comment('Original BOS shift ID');
            $table->timestamp('synced_at')->nullable();
            $table->timestamp('created_at_bos')->nullable()->comment('Original creation time in BOS');
            $table->timestamp('updated_at_bos')->nullable()->comment('Original update time in BOS');

            $table->timestamps();

            // Indexes
            $table->index(['station_id', 'start_time']);
            $table->index(['station_id', 'end_time']);
            $table->index(['station_id', 'status']);
            $table->index('user_id');
            $table->index('start_time');
            $table->index('end_time');
            $table->index('status');
            $table->index('close_type');
            $table->index('bos_shift_id');

            // Unique constraint to prevent duplicates
            $table->unique(['station_id', 'bos_shift_id'], 'unique_station_bos_shift');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};
