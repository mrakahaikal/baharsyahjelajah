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
        Schema::create('visa_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained()->restrictOnDelete();
            $table->json('name');
            $table->string('slug')->unique();
            $table->json('visa_type');
            $table->json('summary')->nullable();
            $table->json('description')->nullable();
            $table->string('entry_type')->nullable();
            $table->unsignedSmallInteger('processing_days_min')->nullable();
            $table->unsignedSmallInteger('processing_days_max')->nullable();
            $table->unsignedSmallInteger('validity_days')->nullable();
            $table->unsignedSmallInteger('maximum_stay_days')->nullable();
            $table->unsignedBigInteger('price_idr')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['country_id', 'is_active', 'sort_order']);
            $table->index(['is_active', 'is_featured', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visa_services');
    }
};
