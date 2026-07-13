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
        Schema::create('tour_package_itineraries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_package_id')
                ->constrained('tour_packages')
                ->cascadeOnDelete();
            $table->unsignedSmallInteger('day_number');
            $table->json('title');
            $table->json('description')
                ->nullable();

            $table->timestamps();

            $table->unique(['tour_package_id', 'day_number']);
            $table->index('tour_package_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tour_package_itineraries');
    }
};
