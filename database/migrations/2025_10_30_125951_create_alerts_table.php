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
        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('station_id')->nullable()->index();
            $table->string('device_type');
            $table->integer('device_number')->nullable();
            $table->string('state')->nullable();
            $table->integer('code');
            $table->dateTime('datetime');
            $table->boolean('is_read')->default(false);
            $table->json('meta')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            // Optionally: $table->foreign('station_id')->references('id')->on('stations')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};
