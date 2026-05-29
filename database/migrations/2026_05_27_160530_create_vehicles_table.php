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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->json('name');
            $table->string('brand');
            $table->string('model');
            $table->unsignedSmallInteger('year')->nullable();
            $table->unsignedSmallInteger('capacity_pax');
            $table->unsignedSmallInteger('capacity_luggage')->default(0);
            $table->string('transmission')->default('automatic'); // automatic | manual
            $table->boolean('has_ac')->default(true);
            $table->boolean('has_wifi')->default(false);
            $table->boolean('is_available')->default(true);
            $table->unsignedBigInteger('price_per_day_idr')->nullable();
            $table->unsignedBigInteger('price_per_trip_idr')->nullable();
            $table->json('features')->nullable();   // array of extra features
            $table->string('thumbnail')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('is_available');
            $table->index('capacity_pax');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
