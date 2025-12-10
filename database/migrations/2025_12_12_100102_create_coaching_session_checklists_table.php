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
        Schema::create('coaching_session_checklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coaching_session_id')
                ->constrained('coaching_sessions')
                ->onDelete('cascade');
            $table->foreignId('completed_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');
            $table->timestamp('completed_at')->nullable();
            $table->enum('status', ['pending', 'completed'])->default('completed');
            $table->json('meta')->nullable(); // vehicle regs, context, topics, tests, scores, comments, signatures
            $table->timestamps();

            $table->unique('coaching_session_id', 'uniq_coaching_session_checklist');
            $table->index('completed_by', 'coach_cl_completed_by_idx');
            $table->index('completed_at', 'coach_cl_completed_at_idx');
            $table->index('status', 'coach_cl_status_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coaching_session_checklists');
    }
};

