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
        Schema::create('sync_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('station_id')->constrained()->onDelete('cascade');
            $table->string('table_name')->comment('Name of the table being synced');
            $table->string('operation')->comment('create, update, delete');
            $table->json('request_payload')->nullable()->comment('Incoming data from BOS');
            $table->json('response_data')->nullable()->comment('Response data sent back to BOS');
            $table->string('status')->comment('success, failed, pending');
            $table->text('error_message')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            // Composite index for efficient querying
            $table->index(['station_id', 'table_name', 'status']);
            $table->index('synced_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sync_logs');
    }
};
