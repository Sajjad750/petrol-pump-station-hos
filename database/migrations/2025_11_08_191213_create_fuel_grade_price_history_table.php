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
        Schema::create('fuel_grade_price_history', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('fuel_grade_id');
            $table->decimal('old_price', 10, 3)->nullable();
            $table->decimal('new_price', 10, 3)->nullable();
            $table->string('change_type')->nullable();
            $table->dateTime('effective_at')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('changed_by')->nullable();
            $table->string('changed_by_user_name')->nullable();
            $table->string('status')->nullable();
            $table->string('source_system')->nullable();

            // HOS-specific additions
            $table->foreignId('station_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('bos_price_history_id')->comment('Original BOS fuel_grade_price_history ID');
            $table->uuid('bos_uuid')->nullable()->comment('Original BOS UUID');
            $table->timestamp('synced_at')->nullable();
            $table->timestamp('created_at_bos')->nullable()->comment('Original creation time in BOS');
            $table->timestamp('updated_at_bos')->nullable()->comment('Original update time in BOS');

            $table->timestamps();

            // Indexes
            $table->index(['station_id', 'bos_price_history_id', 'bos_uuid'], 'fuel_grade_price_history_station_id_bos_history_id_uuid_index');
            $table->index(['station_id', 'fuel_grade_id']);
            $table->index(['station_id', 'source_system']);
            $table->index('source_system');
            $table->index('bos_price_history_id');
            $table->index('bos_uuid');

            // Unique constraint to prevent duplicates
            $table->unique(['station_id', 'bos_price_history_id'], 'unique_station_bos_fuel_grade_price_history');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fuel_grade_price_history');
    }
};
