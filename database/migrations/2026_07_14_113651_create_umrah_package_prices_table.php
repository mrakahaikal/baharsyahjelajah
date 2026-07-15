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
        Schema::create('umrah_package_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('umrah_package_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('room_type');
            $table->unsignedBigInteger('price_idr');
            $table->timestamps();

            $table->unique(['umrah_package_id', 'room_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('umrah_package_prices');
    }
};
