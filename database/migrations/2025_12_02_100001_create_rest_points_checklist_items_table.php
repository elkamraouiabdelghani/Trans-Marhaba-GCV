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
        Schema::create('rest_points_checklist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rest_points_checklist_category_id')
                ->constrained('rest_points_checklist_categories')
                ->onDelete('cascade');
            $table->string('label');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('rest_points_checklist_category_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rest_points_checklist_items');
    }
};

