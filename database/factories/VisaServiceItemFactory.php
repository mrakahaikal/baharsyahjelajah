<?php

namespace Database\Factories;

use App\Enums\VisaItemType;
use App\Models\VisaService;
use App\Models\VisaServiceItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VisaServiceItem>
 */
class VisaServiceItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'visa_service_id' => VisaService::factory(),
            'type' => VisaItemType::Requirement,
            'content' => ['id' => fake()->sentence(4), 'en' => fake()->sentence(4), 'ms' => fake()->sentence(4)],
            'details' => null,
            'is_mandatory' => true,
            'sort_order' => 0,
        ];
    }
}
