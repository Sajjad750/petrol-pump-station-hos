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
            $table->string('site_id')->nullable()->after('pts_id');
            $table->string('mobile')->nullable()->after('dealer');
            $table->string('eod')->nullable()->after('mobile');
            $table->string('street')->nullable()->after('district');
            $table->string('building_number')->nullable()->after('street');
            $table->string('postal_code')->nullable()->after('building_number');
            $table->timestamp('last_updated')->nullable()->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stations', function (Blueprint $table) {
            $table->dropColumn([
                'site_id',
                'mobile',
                'eod',
                'street',
                'building_number',
                'postal_code',
                'last_updated',
            ]);
        });
    }
};
