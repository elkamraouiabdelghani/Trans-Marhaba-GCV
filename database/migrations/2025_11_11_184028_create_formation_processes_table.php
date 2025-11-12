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
        Schema::create('formation_processes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_id')->constrained('drivers')->onDelete('cascade');
            $table->foreignId('formation_id')->constrained('formations')->onDelete('restrict');
            // $table->foreignId('driver_formation_id')->nullable()->constrained('driver_formations')->onDelete('set null');
            $table->string('site')->nullable();
            $table->foreignId('flotte_id')->nullable()->constrained('flottes')->onDelete('set null');
            $table->string('theme')->nullable();
            $table->enum('status', ['draft', 'in_progress', 'rejected', 'validated'])->default('draft');
            $table->tinyInteger('current_step')->default(1)->comment('Current step number (1-8)');
            $table->foreignId('validated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('validated_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('formation_processes');
    }
};
