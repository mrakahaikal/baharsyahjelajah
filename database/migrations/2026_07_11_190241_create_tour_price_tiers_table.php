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
        Schema::create('tour_price_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_tier_id')
                ->constrained('package_tiers')
                ->cascadeOnDelete();
            $table->integer('min_pax');
            $table->integer('max_pax')->nullable(); // null bisa berarti "ke atas"
            $table->decimal('price', 15, 2);
            $table->string('currency', 3)->default('IDR');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tour_price_tiers');
    }
};
