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
            $table->string('brand')->nullable()->change();
            $table->string('model')->nullable()->change();
            $table->unsignedSmallInteger('capacity_pax')->nullable()->change();
            $table->unsignedSmallInteger('capacity_luggage')->nullable()->change();
            $table->string('transmission')->nullable()->change();
            $table->string('catalog_code')->nullable()->unique()->after('id');
            $table->string('category')->nullable()->after('model');
            $table->json('capacity_label')->nullable()->after('capacity_pax');
            $table->unsignedBigInteger('overtime_rate_idr')->nullable()->after('price_per_trip_idr');
            $table->unsignedSmallInteger('sort_order')->default(0)->after('is_featured');

            $table->index(['category', 'is_active', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropIndex(['category', 'is_active', 'sort_order']);
            $table->dropUnique(['catalog_code']);
            $table->dropColumn(['catalog_code', 'category', 'capacity_label', 'overtime_rate_idr', 'sort_order']);
            $table->string('brand')->nullable(false)->change();
            $table->string('model')->nullable(false)->change();
            $table->unsignedSmallInteger('capacity_pax')->nullable(false)->change();
            $table->unsignedSmallInteger('capacity_luggage')->default(0)->nullable(false)->change();
            $table->string('transmission')->default('automatic')->nullable(false)->change();
        });
    }
};
