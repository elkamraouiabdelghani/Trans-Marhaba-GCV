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
        Schema::create('integration_candidates', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['driver', 'administration']);
            $table->foreignId('driver_id')->nullable()->constrained('drivers')->onDelete('set null');
            $table->text('identification_besoin')->nullable();
            $table->enum('poste_type', ['chauffeur', 'administration']);
            $table->text('description_poste')->nullable();
            $table->enum('prospection_method', ['reseaux_social', 'bouche_a_oreil', 'autre'])->nullable();
            $table->date('prospection_date')->nullable();
            $table->integer('nombre_candidats')->nullable();
            $table->text('notes_prospection')->nullable();
            $table->enum('status', ['draft', 'in_progress', 'rejected', 'validated'])->default('draft');
            $table->tinyInteger('current_step')->default(1)->comment('Current step number (1-8)');
            $table->foreignId('validated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('validated_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('integration_candidates');
    }
};
