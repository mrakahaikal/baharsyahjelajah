<?php

namespace Database\Factories;

use App\Models\UmrahPackage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UmrahPackage>
 */
class UmrahPackageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'name' => ['id' => $name, 'en' => $name, 'ms' => $name],
            'slug' => ['id' => fake()->unique()->slug(), 'en' => fake()->unique()->slug(), 'ms' => fake()->unique()->slug()],
            'description' => ['id' => fake()->paragraph(), 'en' => fake()->paragraph(), 'ms' => fake()->paragraph()],
            'package_type' => fake()->randomElement(['regular', 'plus', 'vip', 'ramadan']),
            'duration_days' => fake()->numberBetween(9, 15),
            'price_idr' => fake()->numberBetween(28_000_000, 55_000_000),
            'airline' => fake()->company(),
            'hotel_makkah' => fake()->company(),
            'hotel_madinah' => fake()->company(),
            'room_type' => 'quad',
            'visa_included' => true,
            'handling_included' => true,
            'is_active' => true,
            'is_featured' => false,
        ];
    }
}
