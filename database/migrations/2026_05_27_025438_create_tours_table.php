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
        Schema::create('tours', function (Blueprint $table) {
            $table->id();
            $table->json('name');
            $table->json('slug');
            $table->json('description')->nullable();
            $table->json('highlights')->nullable();     // array of strings per locale
            $table->foreignId('category_id')
                ->nullable()
                ->constrained('tour_categories')
                ->nullOnDelete();
            $table->string('tour_type')->default('domestic'); // domestic | outbound
            $table->unsignedSmallInteger('duration_days');
            $table->unsignedSmallInteger('duration_nights')->default(0);
            $table->unsignedBigInteger('price_idr');
            $table->string('difficulty')->nullable();   // easy | moderate | hard
            $table->unsignedSmallInteger('max_pax')->nullable();
            $table->string('thumbnail')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index('is_active');
            $table->index('is_featured');
            $table->index('tour_type');
            $table->index('price_idr');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tours');
    }
};
