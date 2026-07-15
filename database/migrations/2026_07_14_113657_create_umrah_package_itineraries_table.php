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
        Schema::create('umrah_package_itineraries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('umrah_package_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->unsignedSmallInteger('day_number');
            $table->json('title');
            $table->json('location')->nullable();
            $table->json('description')->nullable();
            $table->timestamps();

            $table->unique(['umrah_package_id', 'day_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('umrah_package_itineraries');
    }
};
