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
        Schema::create('umrah_departures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')
                ->constrained('umrah_packages')
                ->cascadeOnDelete();
            $table->date('departure_date');
            $table->date('return_date');
            $table->unsignedSmallInteger('quota_total');
            $table->unsignedSmallInteger('quota_booked')->default(0);
            $table->string('status')->default('open'); // open | nearly_full | full | closed
            $table->unsignedBigInteger('price_override_idr')->nullable(); // null = gunakan harga package
            $table->timestamps();

            $table->index('package_id');
            $table->index('departure_date');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('umrah_departures');
    }
};
