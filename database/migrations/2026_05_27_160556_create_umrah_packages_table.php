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
        Schema::create('umrah_packages', function (Blueprint $table) {
            $table->id();
            $table->json('name');
            $table->json('description')->nullable();
            $table->string('package_type');            // regular | plus | vip | ramadan
            $table->unsignedSmallInteger('duration_days');
            $table->unsignedBigInteger('price_idr');
            $table->string('airline')->nullable();
            $table->string('hotel_makkah')->nullable();
            $table->unsignedTinyInteger('hotel_makkah_stars')->nullable();
            $table->string('hotel_madinah')->nullable();
            $table->unsignedTinyInteger('hotel_madinah_stars')->nullable();
            $table->string('room_type')->nullable();   // quad | triple | double
            $table->boolean('visa_included')->default(true);
            $table->boolean('handling_included')->default(true);
            $table->string('thumbnail')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('is_active');
            $table->index('package_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('umrah_packages');
    }
};
