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
        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->json('reviewer_name');
            $table->string('reviewer_country', 10)->nullable(); // ID | MY | SG
            $table->string('reviewer_flag', 10)->nullable();    // 🇮🇩 | 🇲🇾 | 🇸🇬

            // Polymorphic: product_type = 'tour' | 'vehicle' | 'umrah'
            $table->string('product_type');
            $table->unsignedBigInteger('product_id');

            $table->unsignedTinyInteger('rating');              // 1–5
            $table->json('content');
            $table->string('photo')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['product_type', 'product_id']);
            $table->index('is_active');
            $table->index('is_featured');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('testimonials');
    }
};
