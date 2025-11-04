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
        Schema::create('driver_integration_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_integration_id')->constrained('driver_integrations')->onDelete('cascade');
            $table->string('step_key'); // e.g., 'identification_besoin', 'verification_documentaire', etc.
            $table->enum('status', ['pending', 'passed', 'failed'])->default('pending');
            $table->text('notes')->nullable();
            $table->foreignId('validated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('validated_at')->nullable();
            $table->json('payload')->nullable(); // Store step-specific data (scores, documents, etc.)
            $table->timestamps();

            $table->unique(['driver_integration_id', 'step_key']); // One step per integration
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_integration_steps');
    }
};
