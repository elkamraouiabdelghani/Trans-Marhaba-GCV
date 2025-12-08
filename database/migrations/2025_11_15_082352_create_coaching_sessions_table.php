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
        Schema::create('coaching_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_id')->constrained('drivers')->onDelete('restrict');
            $table->foreignId('flotte_id')->nullable()->constrained('flottes')->onDelete('set null');
            $table->date('date')->nullable();
            $table->date('date_fin')->nullable();
            $table->enum('type', ['initial', 'suivi', 'correctif', 'route_analysing', 'obc_suite', 'other'])->default('initial');
            $table->string('route_taken')->nullable();
            $table->decimal('from_latitude', 10, 7)->nullable();
            $table->decimal('from_longitude', 10, 7)->nullable();
            $table->string('from_location_name')->nullable();
            $table->decimal('to_latitude', 10, 7)->nullable();
            $table->decimal('to_longitude', 10, 7)->nullable();
            $table->string('to_location_name')->nullable();
            $table->json('rest_places')->nullable();
            $table->string('moniteur')->nullable();
            $table->text('assessment')->nullable();
            $table->enum('status', ['planned', 'in_progress', 'completed', 'cancelled'])->default('planned');
            $table->integer('validity_days')->default(3);
            $table->date('next_planning_session')->nullable();
            $table->integer('score')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coaching_sessions');
    }
};
