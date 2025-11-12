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
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedInteger('driving_hours')->default(0);
            $table->unsignedInteger('rest_hours')->default(0);
            $table->text('route_description')->nullable();
            $table->text('compliance_notes')->nullable();
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
