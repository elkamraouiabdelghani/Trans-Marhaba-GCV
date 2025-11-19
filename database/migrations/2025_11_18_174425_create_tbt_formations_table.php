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
            // Formation data
            $table->string('title');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            // Planning data
            $table->year('year');
            $table->unsignedTinyInteger('month'); // 1-12 for quick filtering
            $table->date('week_start_date'); // Monday of the week
            $table->date('week_end_date'); // Sunday of the week
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes for performance
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
