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
            $table->json('capital_city')->nullable()->after('description');
            $table->string('currency_code', 10)->nullable()->after('capital_city');
            $table->json('language')->nullable()->after('currency_code');
            $table->json('best_time_to_visit')->nullable()->after('language');
            $table->json('travel_requirements_summary')->nullable()->after('best_time_to_visit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->dropColumn([
                'capital_city',
                'currency_code',
                'language',
                'best_time_to_visit',
                'travel_requirements_summary',
            ]);
        });
    }
};
