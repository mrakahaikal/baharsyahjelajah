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
        Schema::create('countryables', function (Blueprint $table) {
            $table->foreignId('country_id')->constrained('countries')->cascadeOnDelete();
            $table->morphs('countryable');
            $table->timestamps();

            $table->primary(['country_id', 'countryable_id', 'countryable_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('countryables');
    }
};
