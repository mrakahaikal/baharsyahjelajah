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
        Schema::table('vehicles', function (Blueprint $table) {
            $table->json('slug')->nullable()->after('name');
            $table->json('description')->nullable()->after('slug');
            $table->renameColumn('is_available', 'is_active');
            $table->boolean('is_featured')->default(false)->after('is_active');
            $table->index(['is_active', 'is_featured']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropIndex(['is_active', 'is_featured']);
            $table->dropColumn('is_featured');
            $table->renameColumn('is_active', 'is_available');
            $table->dropColumn(['slug', 'description']);
        });
    }
};
