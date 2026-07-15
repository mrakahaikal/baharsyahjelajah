<?php

namespace Database\Factories;

use App\Models\UmrahPackage;
use App\Models\UmrahPackageItinerary;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UmrahPackageItinerary>
 */
class UmrahPackageItineraryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'umrah_package_id' => UmrahPackage::factory(),
            'day_number' => fake()->numberBetween(1, 15),
            'title' => ['id' => fake()->sentence(4), 'en' => fake()->sentence(4), 'ms' => fake()->sentence(4)],
            'location' => ['id' => fake()->city(), 'en' => fake()->city(), 'ms' => fake()->city()],
            'description' => ['id' => fake()->paragraph(), 'en' => fake()->paragraph(), 'ms' => fake()->paragraph()],
        ];
    }
}
