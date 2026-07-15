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
        Schema::create('visa_service_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visa_service_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->json('content');
            $table->json('details')->nullable();
            $table->boolean('is_mandatory')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['visa_service_id', 'type', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visa_service_items');
    }
};
