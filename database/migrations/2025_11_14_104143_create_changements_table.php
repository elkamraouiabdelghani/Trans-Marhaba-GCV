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
        Schema::create('changements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('changement_type_id')->constrained('changement_types')->onDelete('restrict');
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('replacement_type')->nullable();
            $table->unsignedBigInteger('replacement_id')->nullable();
            $table->date('date_changement');
            $table->text('description_changement');
            $table->enum('responsable_changement', ['RH', 'DGA', 'QHSE']);
            $table->text('impact')->nullable();
            $table->text('action')->nullable();
            $table->enum('status', ['draft', 'in_progress', 'completed', 'approved'])->default('draft');
            $table->string('check_list_path')->nullable();
            $table->tinyInteger('current_step')->default(1);
            $table->string('created_by')->nullable();
            $table->string('validated_by')->nullable();
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
        Schema::dropIfExists('changements');
    }
};
