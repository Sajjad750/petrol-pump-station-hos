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
        // Add bos_shift_id to product_wise_summaries
        Schema::table('product_wise_summaries', function (Blueprint $table) {
            if (!Schema::hasColumn('product_wise_summaries', 'bos_shift_id')) {
                $table->unsignedBigInteger('bos_shift_id')->nullable()->after('shift_id');
                $table->index(['station_id', 'bos_shift_id'], 'pws_station_bos_shift_idx');
                $table->index('bos_shift_id', 'pws_bos_shift_idx');
            }
        });

        // Add bos_shift_id to payment_mode_wise_summaries
        Schema::table('payment_mode_wise_summaries', function (Blueprint $table) {
            if (!Schema::hasColumn('payment_mode_wise_summaries', 'bos_shift_id')) {
                $table->unsignedBigInteger('bos_shift_id')->nullable()->after('shift_id');
                $table->index(['station_id', 'bos_shift_id'], 'pmws_station_bos_shift_idx');
                $table->index('bos_shift_id', 'pmws_bos_shift_idx');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop from product_wise_summaries
        Schema::table('product_wise_summaries', function (Blueprint $table) {
            if (Schema::hasColumn('product_wise_summaries', 'bos_shift_id')) {
                $table->dropIndex('pws_station_bos_shift_idx');
                $table->dropIndex('pws_bos_shift_idx');
                $table->dropColumn('bos_shift_id');
            }
        });

        // Drop from payment_mode_wise_summaries
        Schema::table('payment_mode_wise_summaries', function (Blueprint $table) {
            if (Schema::hasColumn('payment_mode_wise_summaries', 'bos_shift_id')) {
                $table->dropIndex('pmws_station_bos_shift_idx');
                $table->dropIndex('pmws_bos_shift_idx');
                $table->dropColumn('bos_shift_id');
            }
        });
    }
};
