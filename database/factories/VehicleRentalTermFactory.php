<?php

namespace Database\Factories;

use App\Enums\VehicleRentalTermType;
use App\Models\VehicleRentalTerm;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<VehicleRentalTerm>
 */
class VehicleRentalTermFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->words(3, true);

        return [
            'code' => Str::slug($title),
            'type' => fake()->randomElement(VehicleRentalTermType::cases()),
            'vehicle_category' => null,
            'title' => ['id' => $title, 'en' => $title, 'ms' => $title],
            'content' => [
                'id' => fake()->paragraph(),
                'en' => fake()->paragraph(),
                'ms' => fake()->paragraph(),
            ],
            'is_active' => true,
            'sort_order' => fake()->numberBetween(1, 20),
        ];
    }
}
