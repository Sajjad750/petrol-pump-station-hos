<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('fuel_grade_price_history', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('fuel_grade_id');
            $table->decimal('old_price', 10, 3)->nullable();
            $table->decimal('new_price', 10, 3)->nullable();
            $table->string('change_type')->nullable();
            $table->dateTime('effective_at')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('changed_by')->nullable();
            $table->string('changed_by_user_name')->nullable();
            $table->string('status')->nullable();
            $table->string('source_system')->nullable();
            $table->unsignedBigInteger('station_id')->nullable();
            $table->unsignedBigInteger('bos_price_history_id')->nullable();
            $table->uuid('bos_uuid')->nullable();
            $table->dateTime('created_at_bos')->nullable();
            $table->dateTime('updated_at_bos')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fuel_grade_price_history');
    }
};
