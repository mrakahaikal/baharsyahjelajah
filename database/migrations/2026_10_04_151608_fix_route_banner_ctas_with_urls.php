<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('banners')
            ->where('cta_type', 'route')
            ->where(function ($query): void {
                $query->where('cta_value', 'like', 'http://%')
                    ->orWhere('cta_value', 'like', 'https://%');
            })
            ->update(['cta_type' => 'url']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // One-way data sanitization migration
    }
};
