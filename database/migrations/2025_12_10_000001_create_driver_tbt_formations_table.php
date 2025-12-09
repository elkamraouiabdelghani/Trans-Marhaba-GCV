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
        Schema::create('driver_tbt_formations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_id')->constrained('drivers')->onDelete('cascade');
            $table->foreignId('tbt_formation_id')->constrained('tbt_formations')->onDelete('cascade');
            $table->enum('status', ['done', 'planned'])->default('done');
            $table->date('planned_at')->nullable();
            $table->date('done_at')->nullable();
            $table->enum('validation_status', ['pending', 'in_progress', 'validated', 'rejected'])->default('validated');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['driver_id', 'tbt_formation_id'], 'driver_tbt_formations_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_tbt_formations');
    }
};

