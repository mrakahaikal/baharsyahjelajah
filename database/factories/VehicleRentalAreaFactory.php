<?php

namespace Database\Factories;

use App\Models\VehicleRentalArea;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<VehicleRentalArea>
 */
class VehicleRentalAreaFactory extends Factory
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
            'name' => ['id' => $name, 'en' => $name, 'ms' => $name],
            'slug' => Str::slug($name),
            'description' => [
                'id' => 'Wilayah layanan sewa kendaraan.',
                'en' => 'Vehicle rental service area.',
                'ms' => 'Kawasan perkhidmatan sewa kenderaan.',
            ],
            'minimum_rental_days' => 1,
            'is_active' => true,
            'sort_order' => fake()->numberBetween(1, 20),
        ];
    }
}
