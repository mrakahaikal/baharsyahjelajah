<?php

namespace Database\Factories;

use App\Models\Country;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Country>
 */
class CountryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->country();
        $isoAlpha2 = strtoupper(fake()->unique()->lexify('??'));

        return [
            'name' => ['id' => $name, 'en' => $name, 'ms' => $name],
            'slug' => Str::slug($name).'-'.strtolower($isoAlpha2),
            'iso_alpha_2' => $isoAlpha2,
            'iso_alpha_3' => strtoupper(fake()->unique()->lexify('???')),
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}
