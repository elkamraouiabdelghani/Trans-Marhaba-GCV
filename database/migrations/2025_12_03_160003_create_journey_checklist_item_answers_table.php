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
        Schema::create('journey_checklist_item_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journey_checklist_id')
                ->constrained('journey_checklists')
                ->onDelete('cascade');
            $table->foreignId('journeys_checklist_id')
                ->constrained('journeys_checklist')
                ->onDelete('cascade');
            $table->unsignedTinyInteger('weight')->default(1); // 1-10
            $table->unsignedTinyInteger('score'); // 1-5
            $table->decimal('note', 8, 2); // calculated: weight × score
            $table->text('comment')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('journey_checklist_id', 'j_cl_answers_checklist_idx');
            $table->index('journeys_checklist_id', 'j_cl_answers_template_idx');
            
            // Ensure one answer per item per checklist
            $table->unique(['journey_checklist_id', 'journeys_checklist_id'], 'unique_journey_checklist_item_answer');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journey_checklist_item_answers');
    }
};

