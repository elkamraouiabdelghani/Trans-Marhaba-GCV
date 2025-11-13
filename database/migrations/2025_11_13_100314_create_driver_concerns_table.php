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
        Schema::create('driver_concerns', function (Blueprint $table) {
            $table->id();
            $table->date('reported_at');
            $table->foreignId('driver_id')->constrained('drivers')->cascadeOnDelete();
            $table->string('vehicle_licence_plate')->nullable();
            $table->string('concern_type')->nullable();
            $table->text('description')->nullable();
            $table->text('immediate_action')->nullable();
            $table->string('responsible_party')->nullable();
            $table->enum('status', ['open', 'in_progress', 'resolved', 'closed'])->default('open');
            $table->text('resolution_comments')->nullable();
            $table->date('completion_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_concerns');
    }
};
