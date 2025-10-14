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
        Schema::create('stations', function (Blueprint $table) {
            $table->id();
            $table->string('pts_id')->unique()->comment('Unique identifier from BOS');
            $table->string('site_name');
            $table->string('type')->nullable();
            $table->string('dealer')->nullable();

            // Location fields
            $table->string('country')->nullable();
            $table->string('region')->nullable();
            $table->string('city')->nullable();
            $table->string('district')->nullable();
            $table->text('address')->nullable();

            // Contact information
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('notes')->nullable();

            // Sync tracking
            $table->boolean('is_active')->default(true);
            $table->text('api_key')->nullable()->comment('Encrypted API key for BOS authentication');
            $table->timestamp('last_sync_at')->nullable();
            $table->string('connectivity_status')->default('unknown')->comment('online, warning, offline');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('pts_id');
            $table->index('is_active');
            $table->index('connectivity_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stations');
    }
};
