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
        Schema::create('formations', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['mondatory', 'optionnel', 'complimentaire', 'other'])->default('mondatory');
            $table->foreignId('flotte_id')->nullable()->constrained('flottes')->nullOnDelete();
            $table->enum('delivery_type', ['interne', 'externe'])->default('interne');
            $table->string('theme');
            $table->string('participant')->nullable();
            $table->date('realizing_date')->nullable();
            $table->unsignedSmallInteger('duree')->nullable();
            $table->enum('status', ['planned', 'realized'])->default('planned');
            $table->string('organisme')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('reference_value')->nullable();
            $table->enum('reference_unit', ['months', 'years'])->nullable();
            $table->unsignedTinyInteger('warning_alert_percent')->nullable();
            $table->unsignedTinyInteger('critical_alert_percent')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('formations');
    }
};


