<?php

namespace Database\Factories;

use App\Enums\VisaEntryType;
use App\Models\Country;
use App\Models\VisaService;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<VisaService>
 */
class VisaServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = 'Visa '.fake()->unique()->words(2, true);

        return [
            'country_id' => Country::factory(),
            'name' => ['id' => $name, 'en' => $name, 'ms' => $name],
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(10, 9999),
            'visa_type' => ['id' => 'Kunjungan', 'en' => 'Visit', 'ms' => 'Lawatan'],
            'summary' => ['id' => fake()->sentence(), 'en' => fake()->sentence(), 'ms' => fake()->sentence()],
            'description' => [
                'id' => '<p>'.fake()->paragraph().'</p>',
                'en' => '<p>'.fake()->paragraph().'</p>',
                'ms' => '<p>'.fake()->paragraph().'</p>',
            ],
            'entry_type' => VisaEntryType::Single,
            'processing_days_min' => 5,
            'processing_days_max' => 10,
            'validity_days' => 90,
            'maximum_stay_days' => 30,
            'price_idr' => fake()->numberBetween(1_000_000, 5_000_000),
            'is_active' => true,
            'is_featured' => false,
            'sort_order' => 0,
        ];
    }
}
