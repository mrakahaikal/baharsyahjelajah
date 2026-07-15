<?php

namespace Database\Factories;

use App\Models\UmrahDeparture;
use App\Models\UmrahDeparturePrice;
use App\Models\UmrahPackagePrice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UmrahDeparturePrice>
 */
class UmrahDeparturePriceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'umrah_departure_id' => UmrahDeparture::factory(),
            'umrah_package_price_id' => UmrahPackagePrice::factory(),
            'price_idr' => fake()->numberBetween(28_000_000, 55_000_000),
        ];
    }
}
