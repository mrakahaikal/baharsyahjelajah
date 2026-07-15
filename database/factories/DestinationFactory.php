<?php

namespace Database\Factories;

use App\Models\Destination;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Destination>
 */
class DestinationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->city();

        return [
            'name' => [
                'id' => $name,
                'en' => $name,
                'ms' => $name,
            ],
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(10, 9999),
            'description' => [
                'id' => fake()->paragraph(),
                'en' => fake()->paragraph(),
                'ms' => fake()->paragraph(),
            ],
            'location' => fake()->city().', '.fake()->country(),
            'map_url' => 'https://maps.google.com/?q='.urlencode($name),
            'is_active' => true,
            'is_featured' => false,
        ];
    }
}
