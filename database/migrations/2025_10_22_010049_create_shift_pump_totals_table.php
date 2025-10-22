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
        Schema::create('shift_pump_totals', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->nullable()->unique();

            $table->foreignId('station_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('shift_id')->nullable();
            $table->unsignedBigInteger('pump_id');
            $table->unsignedBigInteger('nozzle_id');
            $table->unsignedBigInteger('fuel_grade_id');
            $table->decimal('volume', 10, 3)->default(0);
            $table->decimal('amount', 10, 2)->default(0);
            $table->integer('transaction_count')->default(0);
            $table->string('user')->nullable();
            $table->string('type')->nullable();
            $table->timestamp('recorded_at')->nullable();
            $table->timestamp('synced_at')->nullable();

// HOS-specific additions
            $table->unsignedBigInteger('bos_shift_pump_total_id');
            $table->string('bos_uuid')->nullable();
            $table->unsignedBigInteger('bos_shift_id')->nullable()->comment('Original BOS shifts ID');

            $table->timestamp('created_at_bos')->nullable();
            $table->timestamp('updated_at_bos')->nullable();

            $table->timestamps();

            $table->index(['station_id', 'bos_shift_pump_total_id']);
            $table->index(['shift_id', 'pump_id']);
            $table->index('recorded_at');
            $table->index('bos_shift_id');
            $table->index(['bos_shift_id','station_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shift_pump_totals');
    }
};
