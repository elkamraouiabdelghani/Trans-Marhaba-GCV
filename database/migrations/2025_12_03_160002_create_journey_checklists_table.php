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
        Schema::create('journey_checklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journey_id')
                ->constrained('journeys')
                ->onDelete('cascade');
            $table->foreignId('completed_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');
            $table->timestamp('completed_at')->nullable();
            $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending');
            $table->text('notes')->nullable();
            $table->json('documents')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('journey_id');
            $table->index('completed_by');
            $table->index('completed_at');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journey_checklists');
    }
};

