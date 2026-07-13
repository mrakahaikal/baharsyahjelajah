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
            $table->foreignId('tour_category_id')
                ->nullable()
                ->constrained('tour_categories')
                ->nullOnDelete();
            $table->json('name');
            $table->json('slug');
            $table->json('short_description')
                ->nullable();
            $table->json('description')
                ->nullable();
            $table->string('tour_type')
                ->default('domestic'); // domestic | international
            $table->string('currency', 3)
                ->default('IDR');

            $table->boolean('is_active')
                ->default(true);
            $table->boolean('is_featured')
                ->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index('is_active');
            $table->index('is_featured');
            $table->index('tour_type');
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
