<?php

namespace Database\Factories;

use App\Models\Vehicle;
use App\Models\VehicleRentalArea;
use App\Models\VehicleRentalRate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VehicleRentalRate>
 */
class VehicleRentalRateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'vehicle_id' => Vehicle::factory(),
            'vehicle_rental_area_id' => VehicleRentalArea::factory(),
            'price_per_day_idr' => fake()->numberBetween(8, 50) * 115000,
            'valid_from' => now()->startOfYear(),
            'valid_until' => now()->endOfYear(),
            'is_active' => true,
        ];
    }
}
