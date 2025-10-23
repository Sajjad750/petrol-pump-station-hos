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
        Schema::create('tank_measurements', function (Blueprint $table) {
            $table->id();

            // BOS fields (mirroring BOS structure)
            $table->uuid('uuid')->nullable()->unique();
            $table->integer('request_id')->nullable();
            $table->string('pts_id', 50)->index();
            $table->dateTime('date_time')->nullable();
            $table->integer('tank')->nullable();
            $table->integer('fuel_grade_id')->nullable();
            $table->string('fuel_grade_name')->nullable();
            $table->string('status')->nullable();
            $table->json('alarms')->nullable();
            $table->float('product_height')->nullable();
            $table->float('water_height')->nullable();
            $table->float('temperature')->nullable();
            $table->float('product_volume')->nullable();
            $table->float('water_volume')->nullable();
            $table->float('product_ullage')->nullable();
            $table->float('product_tc_volume')->nullable();
            $table->float('product_density')->nullable();
            $table->float('product_mass')->nullable();
            $table->float('tank_filling_percentage')->nullable();
            $table->integer('configuration_id')->nullable();

            // HOS-specific additions
            $table->foreignId('station_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('bos_tank_measurement_id')->comment('Original BOS tank measurement ID');
            $table->uuid('bos_uuid')->nullable()->comment('Original BOS UUID');
            $table->timestamp('synced_at')->nullable();
            $table->timestamp('created_at_bos')->nullable()->comment('Original creation time in BOS');
            $table->timestamp('updated_at_bos')->nullable()->comment('Original update time in BOS');

            $table->timestamps();

            // Indexes
            $table->index(['station_id', 'date_time']);
            $table->index('date_time');
            $table->index('tank');
            $table->index('bos_tank_measurement_id');

            // Unique constraint to prevent duplicates
            $table->unique(['station_id', 'bos_tank_measurement_id'], 'unique_station_bos_tank_measurement');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tank_measurements');
    }
};
