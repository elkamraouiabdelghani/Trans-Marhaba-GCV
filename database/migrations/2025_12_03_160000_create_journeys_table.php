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
        Schema::create('journeys', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('from_latitude', 10, 7);
            $table->decimal('from_longitude', 10, 7);
            $table->string('from_location_name')->nullable();
            $table->decimal('to_latitude', 10, 7);
            $table->decimal('to_longitude', 10, 7);
            $table->string('to_location_name')->nullable();
            $table->text('details')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index(['from_latitude', 'from_longitude']);
            $table->index(['to_latitude', 'to_longitude']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journeys');
    }
};

