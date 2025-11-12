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
        Schema::table('alerts', function (Blueprint $table) {
            $table->unsignedBigInteger('bos_alert_id')->nullable()->after('id');
            $table->uuid('bos_uuid')->nullable()->after('bos_alert_id');
            $table->json('raw_payload')->nullable()->after('meta');

            $table->unique(['station_id', 'bos_alert_id']);
            $table->index('bos_uuid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alerts', function (Blueprint $table) {
            $table->dropUnique(['station_id', 'bos_alert_id']);
            $table->dropIndex(['bos_uuid']);

            $table->dropColumn(['bos_alert_id', 'bos_uuid', 'raw_payload']);
        });
    }
};
