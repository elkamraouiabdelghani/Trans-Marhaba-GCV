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
        Schema::create('coaching_session_checklist_item_answers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('coaching_session_checklist_id');
            $table->unsignedBigInteger('coaching_checklist_item_id');
            $table->unsignedTinyInteger('score'); // 1-3
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->index('coaching_session_checklist_id', 'cscia_checklist_idx');
            $table->index('coaching_checklist_item_id', 'cscia_item_idx');
            $table->unique(['coaching_session_checklist_id', 'coaching_checklist_item_id'], 'uniq_coach_cl_item_answer');
            
            // Add foreign keys with shorter constraint names
            $table->foreign('coaching_session_checklist_id', 'cscia_checklist_fk')
                ->references('id')
                ->on('coaching_session_checklists')
                ->onDelete('cascade');
            $table->foreign('coaching_checklist_item_id', 'cscia_item_fk')
                ->references('id')
                ->on('coaching_checklist_items')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coaching_session_checklist_item_answers');
    }
};

