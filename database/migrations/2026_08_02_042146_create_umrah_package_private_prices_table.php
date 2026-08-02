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
        Schema::create('umrah_package_private_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('umrah_package_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->unsignedTinyInteger('duration_nights');
            $table->unsignedTinyInteger('pax'); // e.g. 4 or 8
            $table->unsignedBigInteger('price_idr');
            $table->timestamps();

            $table->unique(['umrah_package_id', 'duration_nights', 'pax'], 'umrah_private_price_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('umrah_package_private_prices');
    }
};
