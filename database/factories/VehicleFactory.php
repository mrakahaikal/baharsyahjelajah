<?php

namespace Database\Factories;

use App\Enums\VehicleCategory;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Vehicle>
 */
class VehicleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->randomElement(['Toyota HiAce', 'Toyota Alphard', 'Toyota Innova', 'Mercedes Sprinter']).' '.fake()->unique()->numberBetween(10, 999);
        $slug = Str::slug($name);

        return [
            'name' => ['id' => $name, 'en' => $name, 'ms' => $name],
            'slug' => ['id' => $slug, 'en' => $slug, 'ms' => $slug],
            'description' => [
                'id' => 'Armada nyaman dengan sopir profesional untuk perjalanan Anda.',
                'en' => 'A comfortable fleet with a professional driver for your journey.',
                'ms' => 'Armada selesa dengan pemandu profesional untuk perjalanan anda.',
            ],
            'brand' => 'Toyota',
            'model' => 'HiAce',
            'category' => VehicleCategory::Minibus,
            'year' => fake()->numberBetween(2021, 2026),
            'capacity_pax' => fake()->numberBetween(6, 16),
            'capacity_label' => null,
            'capacity_luggage' => fake()->numberBetween(2, 8),
            'transmission' => fake()->randomElement(['automatic', 'manual']),
            'has_ac' => true,
            'has_wifi' => fake()->boolean(),
            'is_active' => true,
            'is_featured' => false,
            'price_per_day_idr' => fake()->numberBetween(5, 20) * 100000,
            'price_per_trip_idr' => fake()->numberBetween(8, 30) * 100000,
            'overtime_rate_idr' => 172500,
            'sort_order' => fake()->numberBetween(1, 20),
            'features' => [
                'id' => ['Sopir profesional', 'Air mineral'],
                'en' => ['Professional driver', 'Mineral water'],
                'ms' => ['Pemandu profesional', 'Air mineral'],
            ],
        ];
    }
}
