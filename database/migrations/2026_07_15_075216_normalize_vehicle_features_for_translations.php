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
        DB::table('vehicles')->whereNotNull('features')->orderBy('id')->each(function (object $vehicle): void {
            $features = json_decode($vehicle->features, true);

            if (! is_array($features) || ! array_is_list($features)) {
                return;
            }

            DB::table('vehicles')->where('id', $vehicle->id)->update([
                'features' => json_encode([
                    'id' => $features,
                    'en' => $features,
                    'ms' => $features,
                ], JSON_UNESCAPED_UNICODE),
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('vehicles')->whereNotNull('features')->orderBy('id')->each(function (object $vehicle): void {
            $features = json_decode($vehicle->features, true);

            if (! is_array($features) || array_is_list($features)) {
                return;
            }

            DB::table('vehicles')->where('id', $vehicle->id)->update([
                'features' => json_encode($features['id'] ?? [], JSON_UNESCAPED_UNICODE),
            ]);
        });
    }
};
