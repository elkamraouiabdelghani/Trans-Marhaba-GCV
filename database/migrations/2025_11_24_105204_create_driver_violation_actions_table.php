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
        Schema::create('driver_violation_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_violation_id')
                ->constrained('driver_violations')
                ->cascadeOnDelete();
            $table->text('analysis');
            $table->text('action_plan');
            $table->string('evidence_path')->nullable();
            $table->string('evidence_original_name')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_violation_actions');
    }
};
