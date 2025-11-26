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
        Schema::create('tbt_formations', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('participant')->nullable();
            $table->enum('status', ['planned', 'realized'])->default('planned');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->year('year');
            $table->unsignedTinyInteger('month');
            $table->date('week_start_date');
            $table->date('week_end_date'); 
            $table->text('notes')->nullable();
            $table->json('documents')->nullable();
            $table->timestamps();
            
            $table->index(['year', 'month']);
            $table->index(['week_start_date', 'week_end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbt_formations');
    }
};
