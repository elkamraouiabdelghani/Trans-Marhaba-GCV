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
        Schema::create('driver_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_id')->constrained('drivers')->onDelete('cascade');
            $table->date('activity_date');
            $table->string('flotte')->nullable();
            $table->string('asset_description')->nullable();
            $table->string('driver_name')->nullable();
            $table->time('start_time');
            $table->time('end_time');
            $table->time('work_time')->nullable();
            $table->time('driving_time')->nullable();
            $table->time('rest_time')->nullable();
            $table->time('rest_daily')->nullable();
            $table->text('raison')->nullable();
            $table->string('start_location')->nullable();
            $table->string('overnight_location')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_activities');
    }
};
