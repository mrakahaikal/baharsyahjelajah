<?php

namespace Database\Factories;

use App\Models\UmrahInclude;
use App\Models\UmrahPackage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UmrahInclude>
 */
class UmrahIncludeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'package_id' => UmrahPackage::factory(),
            'item' => ['id' => fake()->sentence(), 'en' => fake()->sentence(), 'ms' => fake()->sentence()],
            'type' => 'include',
            'sort_order' => fake()->numberBetween(1, 20),
        ];
    }
}
