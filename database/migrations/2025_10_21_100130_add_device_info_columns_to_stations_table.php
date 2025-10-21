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
            $table->integer('battery_voltage')->nullable()->comment('Battery voltage in millivolts (mV)');
            $table->integer('cpu_temperature')->nullable()->comment('CPU temperature in degrees Celsius (°C)');
            $table->string('unique_identifier', 24)->nullable()->comment('Device unique identifier (up to 24 hex digits)');
            $table->json('firmware_information')->nullable();
            $table->json('network_settings')->nullable();
            $table->json('remote_server_configuration')->nullable();
            $table->integer('utc_offset')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stations', function (Blueprint $table) {
            $table->dropColumn([
                'battery_voltage',
                'cpu_temperature',
                'unique_identifier',
                'firmware_information',
                'network_settings',
                'remote_server_configuration',
                'utc_offset'
            ]);
        });
    }
};
