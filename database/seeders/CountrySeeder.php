<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $countries = [
            [
                'name' => ['id' => 'Mesir', 'en' => 'Egypt', 'ms' => 'Mesir'],
                'slug' => 'mesir',
                'iso_alpha_2' => 'EG',
                'iso_alpha_3' => 'EGY',
                'sort_order' => 1,
            ],
            [
                'name' => ['id' => 'Arab Saudi', 'en' => 'Saudi Arabia', 'ms' => 'Arab Saudi'],
                'slug' => 'arab-saudi',
                'iso_alpha_2' => 'SA',
                'iso_alpha_3' => 'SAU',
                'sort_order' => 2,
            ],
        ];

        foreach ($countries as $country) {
            Country::query()->updateOrCreate(
                ['iso_alpha_2' => $country['iso_alpha_2']],
                [...$country, 'is_active' => true],
            );
        }
    }
}
