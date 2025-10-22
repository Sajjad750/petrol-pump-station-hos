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
        Schema::create('pump_transactions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->nullable()->unique();

            // BOS fields (mirroring BOS structure)
            $table->string('pts2_device_id')->nullable();
            $table->string('pts_id', 50)->index();
            $table->integer('request_id')->nullable();
            $table->dateTime('date_time_start')->nullable();
            $table->dateTime('date_time_end')->nullable();
            $table->integer('pts_pump_id')->nullable();
            $table->integer('pts_nozzle_id')->nullable();
            $table->integer('pts_fuel_grade_id')->nullable();
            $table->integer('pts_tank_id')->nullable();
            $table->integer('transaction_number')->nullable();
            $table->float('volume')->nullable();
            $table->float('tc_volume')->nullable();
            $table->float('price')->nullable();
            $table->float('amount')->nullable();
            $table->float('starting_totalizer')->nullable();
            $table->float('total_volume')->nullable();
            $table->float('total_amount')->nullable();
            $table->text('tag')->nullable();
            $table->integer('pts_user_id')->nullable();
            $table->text('pts_configuration_id')->nullable();
            $table->unsignedBigInteger('shift_id')->nullable()->index();
            $table->string('mode_of_payment')->nullable()->index();

            // HOS-specific additions
            $table->foreignId('station_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('bos_transaction_id')->comment('Original BOS transaction ID');
            $table->uuid('bos_uuid')->nullable()->comment('Original BOS UUID');
            $table->timestamp('synced_at')->nullable();
            $table->timestamp('created_at_bos')->nullable()->comment('Original creation time in BOS');
            $table->timestamp('updated_at_bos')->nullable()->comment('Original update time in BOS');

            $table->timestamps();

            // Indexes
            $table->index(['station_id', 'date_time_start']);
            $table->index('date_time_start');
            $table->index('bos_transaction_id');

            // Unique constraint to prevent duplicates
            $table->unique(['station_id', 'bos_transaction_id'], 'unique_station_bos_transaction');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pump_transactions');
    }
};
