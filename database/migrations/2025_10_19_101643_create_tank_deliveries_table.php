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
        Schema::create('tank_deliveries', function (Blueprint $table) {
            $table->id();

            // BOS fields (mirroring BOS structure)
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('request_id');
            $table->string('pts_id', 255);
            $table->string('pts_delivery_id', 255)->nullable();
            $table->integer('tank');
            $table->integer('fuel_grade_id')->nullable();
            $table->string('fuel_grade_name', 20)->nullable();
            $table->string('configuration_id', 8)->nullable();
            $table->dateTime('start_datetime')->nullable();
            $table->float('start_product_height')->nullable();
            $table->float('start_water_height')->nullable();
            $table->float('start_temperature')->nullable();
            $table->float('start_product_volume')->nullable();
            $table->float('start_product_tc_volume')->nullable();
            $table->float('start_product_density')->nullable();
            $table->float('start_product_mass')->nullable();
            $table->dateTime('end_datetime')->nullable();
            $table->float('end_product_height')->nullable();
            $table->float('end_water_height')->nullable();
            $table->float('end_temperature')->nullable();
            $table->float('end_product_volume')->nullable();
            $table->float('end_product_tc_volume')->nullable();
            $table->float('end_product_density')->nullable();
            $table->float('end_product_mass')->nullable();
            $table->float('received_product_volume')->nullable();
            $table->float('absolute_product_height')->nullable();
            $table->float('absolute_water_height')->nullable();
            $table->float('absolute_temperature')->nullable();
            $table->float('absolute_product_volume')->nullable();
            $table->float('absolute_product_tc_volume')->nullable();
            $table->float('absolute_product_density')->nullable();
            $table->float('absolute_product_mass')->nullable();
            $table->float('pumps_dispensed_volume')->nullable();
            $table->json('probe_data')->nullable();

            // HOS-specific additions
            $table->foreignId('station_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('bos_tank_delivery_id')->comment('Original BOS tank delivery ID');
            $table->uuid('bos_uuid')->nullable()->comment('Original BOS UUID');
            $table->timestamp('synced_at')->nullable();
            $table->timestamp('created_at_bos')->nullable()->comment('Original creation time in BOS');
            $table->timestamp('updated_at_bos')->nullable()->comment('Original update time in BOS');

            $table->timestamps();

            // Indexes
            $table->index(['station_id', 'start_datetime']);
            $table->index(['station_id', 'end_datetime']);
            $table->index('start_datetime');
            $table->index('end_datetime');
            $table->index('tank');
            $table->index('fuel_grade_id');
            $table->index('bos_tank_delivery_id');

            // Unique constraint to prevent duplicates
            $table->unique(['station_id', 'bos_tank_delivery_id'], 'unique_station_bos_tank_delivery');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tank_deliveries');
    }
};
