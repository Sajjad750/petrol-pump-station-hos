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
        Schema::create('product_wise_summaries', function (Blueprint $table) {
            $table->id();

            // BOS fields (mirroring BOS structure)
            $table->unsignedBigInteger('shift_id');
            $table->unsignedBigInteger('fuel_grade_id');
            $table->decimal('volume', 12, 2)->default(0.00);
            $table->decimal('amount', 12, 2)->default(0.00);

            // HOS-specific additions
            $table->foreignId('station_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('bos_product_wise_summary_id')->comment('Original BOS product wise summary ID');
            $table->string('bos_uuid')->index()->nullable()->comment('Original BOS product wise summary UUID');
            $table->timestamp('synced_at')->nullable();
            $table->timestamp('created_at_bos')->nullable()->comment('Original creation time in BOS');
            $table->timestamp('updated_at_bos')->nullable()->comment('Original update time in BOS');

            $table->timestamps();

            // Indexes
            $table->index(['station_id', 'shift_id']);
            $table->index(['station_id', 'fuel_grade_id']);
            $table->index('shift_id');
            $table->index('fuel_grade_id');
            $table->index('bos_product_wise_summary_id');

            // Unique constraint to prevent duplicates
            $table->unique(['station_id', 'bos_product_wise_summary_id'], 'unique_station_bos_product_wise_summary');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_wise_summaries');
    }
};
