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
        Schema::create('vehicle_rental_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_rental_area_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('price_per_day_idr');
            $table->date('valid_from');
            $table->date('valid_until')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(
                ['vehicle_id', 'vehicle_rental_area_id', 'valid_from'],
                'vehicle_area_rate_start_unique',
            );
            $table->index(
                ['vehicle_rental_area_id', 'is_active', 'valid_from', 'valid_until'],
                'vehicle_area_rate_lookup_index',
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_rental_rates');
    }
};
