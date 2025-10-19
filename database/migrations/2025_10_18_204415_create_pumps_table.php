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
        Schema::create('pumps', function (Blueprint $table) {
            $table->id();

            // HOS-specific additions
            $table->foreignId('station_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('bos_pump_id')->comment('Original BOS pump ID');
            $table->uuid('bos_uuid')->nullable()->comment('Original BOS UUID');

            // BOS fields
            $table->string('name');
            $table->string('pump_id');
            $table->boolean('is_self_service')->nullable();
            $table->integer('nozzles_count')->nullable();
            $table->string('status')->nullable();
            $table->integer('pts_pump_id');
            $table->integer('pts_port_id')->nullable();
            $table->integer('pts_address_id')->nullable();

            // Sync tracking
            $table->timestamp('synced_at')->nullable();
            $table->timestamp('created_at_bos')->nullable()->comment('Original creation time in BOS');
            $table->timestamp('updated_at_bos')->nullable()->comment('Original update time in BOS');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('station_id');
            $table->index('status');
            $table->index('bos_pump_id');

            // Unique constraint to prevent duplicates
            $table->unique(['station_id', 'bos_pump_id'], 'unique_station_bos_pump');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pumps');
    }
};
