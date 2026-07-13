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
        Schema::create('destinationables', function (Blueprint $table) {
            $table->foreignId('destination_id')->constrained()->cascadeOnDelete();
            $table->morphs('destinationable');
            $table->timestamps();

            $table->unique(
                ['destination_id', 'destinationable_type', 'destinationable_id'],
                'destinationables_unique',
            );
        });

        DB::table('destinations')
            ->whereNotNull('destinationable_type')
            ->whereNotNull('destinationable_id')
            ->orderBy('id')
            ->each(function (object $destination): void {
                DB::table('destinationables')->insert([
                    'destination_id' => $destination->id,
                    'destinationable_type' => $destination->destinationable_type,
                    'destinationable_id' => $destination->destinationable_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });

        Schema::table('destinations', function (Blueprint $table) {
            $table->dropMorphs('destinationable');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('destinations', function (Blueprint $table) {
            $table->nullableMorphs('destinationable');
        });

        DB::table('destinationables')
            ->orderBy('destination_id')
            ->orderBy('created_at')
            ->each(function (object $destinationable): void {
                DB::table('destinations')
                    ->where('id', $destinationable->destination_id)
                    ->whereNull('destinationable_type')
                    ->update([
                        'destinationable_type' => $destinationable->destinationable_type,
                        'destinationable_id' => $destinationable->destinationable_id,
                    ]);
            });

        Schema::dropIfExists('destinationables');
    }
};
