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
        Schema::create('organigram_members', function (Blueprint $table) {
            $table->id();
            $table->enum('position', [
                'DG',
                'DGA',
                'COMPTABILITE',
                'IT',
                'OBC',
                'HSSE',
                'DEPOT_ET_EXPLOITATION',
                'MAINTENANCE',
                'MONITEUR',
                'DEPOT',
                'CHAUFFEURS',
            ])->index();
            $table->string('name');
            $table->unsignedInteger('revision')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organigram_members');
    }
};

