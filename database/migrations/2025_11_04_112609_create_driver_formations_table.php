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
        Schema::create('driver_formations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_id')->constrained('drivers')->onDelete('cascade');
            $table->foreignId('formation_type_id')->constrained('formation_types')->onDelete('restrict');
            $table->enum('status', ['done', 'planned'])->default('planned');
            $table->date('planned_at')->nullable();
            $table->date('due_at')->nullable();
            $table->date('done_at')->nullable();
            $table->unsignedSmallInteger('progress_percent')->default(0);
            $table->enum('validation_status', ['pending', 'in_progress', 'validated', 'rejected'])->default('pending');
            $table->string('certificate_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_formations');
    }
};
