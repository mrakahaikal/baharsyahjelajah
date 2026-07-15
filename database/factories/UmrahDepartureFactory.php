<?php

namespace Database\Factories;

use App\Models\UmrahDeparture;
use App\Models\UmrahPackage;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<UmrahDeparture>
 */
class UmrahDepartureFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $departureDate = Carbon::instance(fake()->dateTimeBetween('+1 week', '+6 months'));

        return [
            'package_id' => UmrahPackage::factory(),
            'departure_date' => $departureDate->toDateString(),
            'return_date' => $departureDate->copy()->addDays(9)->toDateString(),
            'quota_total' => 40,
            'quota_booked' => fake()->numberBetween(0, 25),
            'status' => 'open',
        ];
    }
}
