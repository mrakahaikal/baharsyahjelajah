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
        Schema::table('destinations', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('map_url');
            $table->boolean('is_featured')->default(false)->after('is_active');

            $table->index(['is_active', 'is_featured', 'id'], 'destinations_publication_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('destinations', function (Blueprint $table) {
            $table->dropIndex('destinations_publication_index');
            $table->dropColumn(['is_active', 'is_featured']);
        });
    }
};
