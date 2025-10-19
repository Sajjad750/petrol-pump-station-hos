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
        Schema::create('fuel_grades', function (Blueprint $table) {
            $table->id();

            // BOS fields (mirroring BOS structure)
            $table->uuid('uuid')->unique();
            $table->string('pts_fuel_grade_id', 255)->nullable();
            $table->string('name', 255);
            $table->decimal('price', 8, 2);
            $table->decimal('scheduled_price', 8, 3)->nullable()->comment('Scheduled future price');
            $table->dateTime('scheduled_at')->nullable()->comment('When to apply scheduled_price (UTC)');
            $table->decimal('expansion_coefficient', 8, 5)->nullable()->comment('Thermal coefficient of expansion at 15 °C (up to 5 decimal places)');
            $table->tinyInteger('blend_tank1_id')->unsigned()->nullable()->comment('First tank ID for blended fuel (0 = no tank, range 1-255)');
            $table->tinyInteger('blend_tank1_percentage')->unsigned()->nullable()->comment('Blend percentage from first tank (1-99)');
            $table->tinyInteger('blend_tank2_id')->unsigned()->nullable()->comment('Second tank ID for blended fuel (0 = no tank, range 1-255)');

            // HOS-specific additions
            $table->foreignId('station_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('bos_fuel_grade_id')->comment('Original BOS fuel grade ID');
            $table->uuid('bos_uuid')->nullable()->comment('Original BOS UUID');
            $table->timestamp('synced_at')->nullable();
            $table->timestamp('created_at_bos')->nullable()->comment('Original creation time in BOS');
            $table->timestamp('updated_at_bos')->nullable()->comment('Original update time in BOS');

            $table->timestamps();

            // Indexes
            $table->index(['station_id', 'name']);
            $table->index(['station_id', 'price']);
            $table->index('name');
            $table->index('price');
            $table->index('pts_fuel_grade_id');
            $table->index('scheduled_at');
            $table->index('bos_fuel_grade_id');

            // Unique constraint to prevent duplicates
            $table->unique(['station_id', 'bos_fuel_grade_id'], 'unique_station_bos_fuel_grade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fuel_grades');
    }
};
