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
        Schema::create('whatsapp_templates', function (Blueprint $table) {
            $table->id();
            $table->string('product_type');     // tour | vehicle | umrah
            $table->string('locale', 5);        // id | ms | en
            $table->text('template');           // Teks dengan variabel {product_name}, {price}, dll
            $table->json('variables')->nullable(); // dokumentasi variabel yang tersedia
            $table->timestamps();

            $table->unique(['product_type', 'locale']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_templates');
    }
};
