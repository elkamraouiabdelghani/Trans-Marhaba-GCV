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
        Schema::create('coaching_checklist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coaching_checklist_category_id')
                ->constrained('coaching_checklist_categories')
                ->onDelete('cascade');
            $table->string('label');
            $table->unsignedTinyInteger('score')->default(1);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('coaching_checklist_category_id', 'coach_cl_items_cat_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coaching_checklist_items');
    }
};

