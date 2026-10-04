<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('visa_services', function (Blueprint $table) {
            $table->string('currency', 3)->default('IDR')->after('price_idr');
            $table->decimal('price', 15, 2)->nullable()->after('currency');
        });

        DB::table('visa_services')
            ->whereNotNull('price_idr')
            ->update([
                'price' => DB::raw('price_idr'),
                'currency' => 'IDR',
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('visa_services', function (Blueprint $table) {
            $table->dropColumn(['currency', 'price']);
        });
    }
};
