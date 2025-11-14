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
        Schema::create('changement_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('changement_id')->constrained('changements')->onDelete('cascade');
            $table->tinyInteger('step_number');
            $table->json('step_data')->nullable();
            $table->enum('status', ['pending', 'validated', 'rejected'])->default('pending');
            $table->string('validated_by')->nullable();
            $table->timestamp('validated_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('changement_steps');
    }
};
