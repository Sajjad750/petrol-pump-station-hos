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
        Schema::table('stations', function (Blueprint $table) {
            $table->unsignedBigInteger('bos_pts2_device_id')
                ->nullable()
                ->after('pts_id')
                ->comment('Latest BOS PTS2 device identifier');

            $table->uuid('bos_pts2_device_uuid')
                ->nullable()
                ->after('bos_pts2_device_id')
                ->comment('Latest BOS PTS2 device UUID');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stations', function (Blueprint $table) {
            $table->dropColumn([
                'bos_pts2_device_id',
                'bos_pts2_device_uuid',
            ]);
        });
    }
};
