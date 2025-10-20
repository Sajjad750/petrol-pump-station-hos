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
        Schema::table('pump_transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('pump_transactions', 'starting_totalizer')) {
                $table->float('starting_totalizer')->nullable()->after('amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pump_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('pump_transactions', 'starting_totalizer')) {
                $table->dropColumn('starting_totalizer');
            }
        });
    }
};
