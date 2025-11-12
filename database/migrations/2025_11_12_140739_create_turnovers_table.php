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
        Schema::create('turnovers', function (Blueprint $table) {
            $table->id();
            $table->date('departure_date');
            $table->string('flotte')->nullable();
            $table->foreignId('driver_id')->nullable()->constrained('drivers')->onDelete('set null');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('position')->nullable();
            $table->text('departure_reason');
            $table->text('interview_notes')->nullable();
            $table->string('interviewed_by')->nullable();
            $table->text('observations')->nullable();
            $table->string('turnover_pdf_path')->nullable();
            $table->enum('status', ['pending', 'confirmed'])->default('pending');
            $table->timestamp('confirmed_at')->nullable();
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('turnovers');
    }
};
