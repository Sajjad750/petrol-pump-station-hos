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
        Schema::create('payment_mode_wise_summaries', function (Blueprint $table) {
            $table->id();

            // BOS fields (mirroring BOS structure)
            $table->unsignedBigInteger('shift_id');
            $table->string('mop', 255);
            $table->decimal('volume', 12, 2)->default(0.00);
            $table->decimal('amount', 12, 2)->default(0.00);

            // HOS-specific additions
            $table->foreignId('station_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('bos_payment_mode_wise_summary_id')->comment('Original BOS payment mode wise summary ID');
            $table->timestamp('synced_at')->nullable();
            $table->timestamp('created_at_bos')->nullable()->comment('Original creation time in BOS');
            $table->timestamp('updated_at_bos')->nullable()->comment('Original update time in BOS');

            $table->timestamps();

            // Indexes
            $table->index(['station_id', 'shift_id']);
            $table->index(['station_id', 'mop']);
            $table->index('shift_id');
            $table->index('mop');
            $table->index('bos_payment_mode_wise_summary_id', 'idx_pmws_bos_id');

            // Unique constraint to prevent duplicates
            $table->unique(['station_id', 'bos_payment_mode_wise_summary_id'], 'unique_station_bos_payment_mode_wise_summary');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_mode_wise_summaries');
    }
};
