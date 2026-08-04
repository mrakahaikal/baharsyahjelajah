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
        Schema::create('vehicleables', function (Blueprint $table) {
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->morphs('vehicleable');
            $table->timestamps();

            $table->primary(['vehicle_id', 'vehicleable_id', 'vehicleable_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicleables');
    }
};
