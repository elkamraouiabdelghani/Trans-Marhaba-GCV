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
        Schema::create('changement_checklist_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('changement_id')->constrained('changements')->onDelete('cascade');
            $table->foreignId('sous_cretaire_id')->constrained('sous_cretaires')->onDelete('restrict');
            $table->enum('status', ['OK', 'KO', 'N/A'])->default('N/A');
            $table->text('observation')->nullable();
            $table->timestamps();
            
            // Ensure one result per changement per sous cretaire
            $table->unique(['changement_id', 'sous_cretaire_id'], 'chg_checklist_chg_sous_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('changement_checklist_results');
    }
};
