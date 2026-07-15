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
        Schema::create('umrah_departure_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('umrah_departure_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('umrah_package_price_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->unsignedBigInteger('price_idr');
            $table->timestamps();

            $table->unique(['umrah_departure_id', 'umrah_package_price_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('umrah_departure_prices');
    }
};
