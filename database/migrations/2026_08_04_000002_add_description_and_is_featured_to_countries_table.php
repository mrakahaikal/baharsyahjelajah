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
        Schema::table('countries', function (Blueprint $table) {
            $table->json('description')->nullable()->after('name');
            $table->boolean('is_featured')->default(false)->after('is_active');

            $table->index(['is_active', 'is_featured', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->dropIndex(['is_active', 'is_featured', 'sort_order']);
            $table->dropColumn(['description', 'is_featured']);
        });
    }
};
