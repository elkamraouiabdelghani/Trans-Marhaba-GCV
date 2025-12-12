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
        Schema::create('driver_handovers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable();
            $table->foreignId('driver_from_id')->nullable()->constrained('drivers');
            $table->string('driver_from_name')->nullable();
            $table->foreignId('driver_to_id')->nullable()->constrained('drivers');
            $table->string('driver_to_name')->nullable();
            $table->foreignId('vehicle_id')->nullable()->constrained();
            $table->unsignedInteger('vehicle_km')->nullable();
            $table->decimal('gasoil', 8, 2)->nullable();
            $table->date('handover_date')->nullable();
            $table->date('back_date')->nullable();
            $table->string('location')->nullable();
            $table->string('cause')->nullable();
            $table->json('documents')->nullable();
            $table->json('document_files')->nullable();
            $table->json('equipment')->nullable();
            $table->text('anomalies_description')->nullable();
            $table->text('anomalies_actions')->nullable();
            $table->enum('status', ['pending', 'confirmed'])->nullable()->default('pending');
            $table->string('handover_file_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_handovers');
    }
};
