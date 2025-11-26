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
        Schema::create('driver_violations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_id')->constrained('drivers')->onDelete('cascade');
            $table->foreignId('violation_type_id')->nullable()->constrained('violation_types')->onDelete('set null');
            $table->date('violation_date');
            $table->time('violation_time')->nullable();
            $table->decimal('speed', 8, 2)->nullable();
            $table->decimal('speed_limit', 8, 2)->nullable();
            $table->unsignedInteger('violation_duration_seconds')->nullable();
            $table->decimal('violation_distance_km', 8, 2)->nullable();
            $table->string('location')->nullable();
            $table->decimal('location_lat', 10, 7)->nullable();
            $table->decimal('location_lng', 10, 7)->nullable();
            $table->enum('status', ['pending', 'rejected', 'confirmed'])->default('pending');
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->onDelete('set null');
            $table->text('description')->nullable();
            $table->text('analysis')->nullable();
            $table->text('action_plan')->nullable();
            $table->string('evidence_path')->nullable();
            $table->string('evidence_original_name')->nullable();
            $table->string('document_path')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Indexes for performance
            $table->index('driver_id');
            $table->index('violation_type_id');
            $table->index('violation_date');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_violations');
    }
};
