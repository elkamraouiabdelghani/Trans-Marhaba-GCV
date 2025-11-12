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
            $table->foreignId('formation_category_id')->nullable()->constrained('formation_categories')->nullOnDelete();
            $table->foreignId('flotte_id')->nullable()->constrained('flottes')->nullOnDelete();
            $table->string('name')->unique();
            $table->string('code')->unique();
            $table->year('planned_year')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('obligatoire')->default(false);
            $table->unsignedInteger('reference_value')->nullable();
            $table->enum('reference_unit', ['months', 'years'])->nullable();
            $table->unsignedTinyInteger('warning_alert_percent')->nullable();
            $table->unsignedInteger('warning_alert_days')->nullable();
            $table->unsignedTinyInteger('critical_alert_percent')->nullable();
            $table->unsignedInteger('critical_alert_days')->nullable();
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


