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
        Schema::table('banners', function (Blueprint $table) {
            $table->string('image_path')->nullable()->change();
            $table->string('placement')->default('home_promo')->after('image_path');
            $table->timestamp('starts_at')->nullable()->after('is_active');
            $table->timestamp('ends_at')->nullable()->after('starts_at');

            $table->index(['placement', 'is_active', 'sort_order'], 'banners_placement_visibility_index');
        });

        Schema::table('faqs', function (Blueprint $table) {
            $table->json('contexts')->nullable()->after('category');
            $table->index(['is_active', 'sort_order'], 'faqs_visibility_order_index');
        });

        $primaryBannerId = DB::table('banners')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->value('id');

        if ($primaryBannerId !== null) {
            DB::table('banners')->where('id', $primaryBannerId)->update([
                'placement' => 'home_hero',
            ]);
        }

        DB::table('faqs')
            ->select(['id', 'category'])
            ->orderBy('id')
            ->eachById(function (object $faq): void {
                $contexts = match ($faq->category) {
                    'tour' => ['home', 'tour'],
                    'umrah' => ['home', 'umrah'],
                    'vehicle' => ['home', 'vehicle'],
                    'payment' => ['home', 'booking'],
                    default => ['home'],
                };

                DB::table('faqs')->where('id', $faq->id)->update([
                    'contexts' => json_encode($contexts, JSON_THROW_ON_ERROR),
                ]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('faqs', function (Blueprint $table) {
            $table->dropIndex('faqs_visibility_order_index');
            $table->dropColumn('contexts');
        });

        Schema::table('banners', function (Blueprint $table) {
            $table->dropIndex('banners_placement_visibility_index');
            $table->dropColumn(['placement', 'starts_at', 'ends_at']);
        });

        DB::table('banners')->whereNull('image_path')->update(['image_path' => '']);

        Schema::table('banners', function (Blueprint $table) {
            $table->string('image_path')->nullable(false)->change();
        });
    }
};
