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
        Schema::create('rest_points_checklist_item_answers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rest_points_checklist_id');
            $table->unsignedBigInteger('rest_points_checklist_item_id');
            $table->boolean('is_checked'); // true = yes, false = no
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->index('rest_points_checklist_id', 'rp_cl_answers_checklist_idx');
            $table->index('rest_points_checklist_item_id', 'rp_cl_answers_item_idx');
            // Ensure one answer per item per checklist
            $table->unique(['rest_points_checklist_id', 'rest_points_checklist_item_id'], 'unique_checklist_item_answer');
            
            // Add foreign keys with shorter constraint names
            $table->foreign('rest_points_checklist_id', 'rp_cl_answers_checklist_fk')
                ->references('id')
                ->on('rest_points_checklists')
                ->onDelete('cascade');
            $table->foreign('rest_points_checklist_item_id', 'rp_cl_answers_item_fk')
                ->references('id')
                ->on('rest_points_checklist_items')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rest_points_checklist_item_answers');
    }
};

