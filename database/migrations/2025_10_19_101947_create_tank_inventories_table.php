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
        Schema::create('tank_inventories', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->nullable()->unique();

            // BOS fields (mirroring BOS structure)
            $table->unsignedBigInteger('request_id')->nullable();
            $table->string('pts_id', 255)->nullable();
            $table->integer('tank');
            $table->integer('fuel_grade_id')->nullable();
            $table->string('fuel_grade_name', 20)->nullable();
            $table->string('configuration_id', 8)->nullable();
            $table->dateTime('snapshot_datetime')->nullable();
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
            $table->unsignedBigInteger('bos_tank_inventory_id')->comment('Original BOS tank inventory ID');
            $table->uuid('bos_uuid')->nullable()->comment('Original BOS UUID');
            $table->timestamp('synced_at')->nullable();
            $table->timestamp('created_at_bos')->nullable()->comment('Original creation time in BOS');
            $table->timestamp('updated_at_bos')->nullable()->comment('Original update time in BOS');

            $table->timestamps();

            // Indexes
            $table->index(['station_id', 'snapshot_datetime']);
            $table->index('snapshot_datetime');
            $table->index('tank');
            $table->index('fuel_grade_id');
            $table->index('bos_tank_inventory_id');

            // Unique constraint to prevent duplicates
            $table->unique(['station_id', 'bos_tank_inventory_id'], 'unique_station_bos_tank_inventory');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tank_inventories');
    }
};
