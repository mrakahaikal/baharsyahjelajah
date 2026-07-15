<?php

namespace Database\Factories;

use App\Models\UmrahPackage;
use App\Models\UmrahPackagePrice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UmrahPackagePrice>
 */
class UmrahPackagePriceFactory extends Factory
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
            'room_type' => 'quad',
            'price_idr' => fake()->numberBetween(28_000_000, 55_000_000),
        ];
    }
}
