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
        Schema::create('tour_itineraries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_id')
                ->constrained('tours')
                ->cascadeOnDelete();
            $table->unsignedSmallInteger('day_number');
            $table->json('title');
            $table->json('description')->nullable();
            $table->json('meals_included')->nullable();  // ["breakfast", "lunch", "dinner"]
            $table->string('accommodation')->nullable();
            $table->timestamps();

            $table->unique(['tour_id', 'day_number']);
            $table->index('tour_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tour_itineraries');
    }
};
