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
        Schema::table('currency_rates', function (Blueprint $table) {
            $table->string('provider', 50)->nullable()->after('rate');
            $table->timestamp('source_updated_at')->nullable()->after('provider');
            $table->timestamp('fetched_at')->nullable()->after('source_updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('currency_rates', function (Blueprint $table) {
            $table->dropColumn(['provider', 'source_updated_at', 'fetched_at']);
        });
    }
};
