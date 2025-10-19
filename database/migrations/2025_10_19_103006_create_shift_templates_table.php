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
        Schema::create('shift_templates', function (Blueprint $table) {
            $table->id();

            // BOS fields (mirroring BOS structure)
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('pts2_device_id')->nullable();
            $table->time('end_time');
            $table->string('timezone', 50);

            // HOS-specific additions
            $table->foreignId('station_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('bos_shift_template_id')->comment('Original BOS shift template ID');
            $table->uuid('bos_uuid')->nullable()->comment('Original BOS UUID');
            $table->timestamp('synced_at')->nullable();
            $table->timestamp('created_at_bos')->nullable()->comment('Original creation time in BOS');
            $table->timestamp('updated_at_bos')->nullable()->comment('Original update time in BOS');

            $table->timestamps();

            // Indexes
            $table->index(['station_id', 'end_time']);
            $table->index('end_time');
            $table->index('timezone');
            $table->index('pts2_device_id');
            $table->index('bos_shift_template_id');

            // Unique constraint to prevent duplicates
            $table->unique(['station_id', 'bos_shift_template_id'], 'unique_station_bos_shift_template');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shift_templates');
    }
};
