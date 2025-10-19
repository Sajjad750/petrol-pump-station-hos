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
        Schema::create('pts_users', function (Blueprint $table) {
            $table->id();

            // BOS fields (mirroring BOS structure)
            $table->integer('pts_user_id');
            $table->string('login', 10);
            $table->boolean('configuration_permission')->default(false);
            $table->boolean('control_permission')->default(false);
            $table->boolean('monitoring_permission')->default(false);
            $table->boolean('reports_permission')->default(false);
            $table->boolean('is_active')->default(true);

            // HOS-specific additions
            $table->foreignId('station_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('bos_pts_user_id')->comment('Original BOS PTS user ID');
            $table->timestamp('synced_at')->nullable();
            $table->timestamp('created_at_bos')->nullable()->comment('Original creation time in BOS');
            $table->timestamp('updated_at_bos')->nullable()->comment('Original update time in BOS');

            $table->timestamps();

            // Indexes
            $table->index(['station_id', 'pts_user_id']);
            $table->index(['station_id', 'login']);
            $table->index(['station_id', 'is_active']);
            $table->index('pts_user_id');
            $table->index('login');
            $table->index('is_active');
            $table->index('bos_pts_user_id');

            // Unique constraint to prevent duplicates
            $table->unique(['station_id', 'bos_pts_user_id'], 'unique_station_bos_pts_user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pts_users');
    }
};
