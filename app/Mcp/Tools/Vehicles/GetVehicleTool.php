<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Vehicles;

use App\Models\Country;
use App\Models\Vehicle;
use App\Models\VehicleRentalRate;
use App\Models\VehicleRentalTerm;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('get_vehicle')]
#[Title('Get Vehicle & Fleet Profile')]
#[Description('Retrieve full profile of a vehicle by ID, slug, or catalog code, including specifications, localized texts, area rental rates, and rental terms.')]
class GetVehicleTool extends Tool
{
    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('The numeric ID of the vehicle.'),
            'slug' => $schema->string()->description('The URL slug of the vehicle (used if "id" is omitted).'),
            'catalog_code' => $schema->string()->description('The vehicle catalog code (e.g. "INNOVA-REBORN-01").'),
            'locale' => $schema->string()->description('Preferred display locale ("id", "en", or "ms"). Default is "id".'),
        ];
    }

    public function handle(Request $request): Response
    {
        $id = $request->get('id');
        $slug = $request->get('slug');
        $catalogCode = $request->get('catalog_code');
        $locale = (string) $request->get('locale', 'id');

        if (empty($id) && empty($slug) && empty($catalogCode)) {
            return Response::error('At least one of "id", "slug", or "catalog_code" must be provided.');
        }

        $query = Vehicle::query()->with([
            'countries',
            'rentalRates.area',
            'galleries',
        ]);

        /** @var Vehicle|null $vehicle */
        if (! empty($id)) {
            $vehicle = $query->find((int) $id);
        } elseif (! empty($catalogCode)) {
            $vehicle = $query->where('catalog_code', (string) $catalogCode)->first();
        } else {
            $vehicle = $query->whereLocalizedSlug((string) $slug)->first();
        }

        if (! $vehicle) {
            $identifier = ! empty($id)
                ? "ID {$id}"
                : (! empty($catalogCode) ? "catalog code '{$catalogCode}'" : "slug '{$slug}'");

            return Response::error("Vehicle with {$identifier} not found.");
        }

        // Fetch rental terms for this vehicle's category
        $terms = [];
        if ($vehicle->category) {
            $terms = VehicleRentalTerm::query()
                ->where('vehicle_category', $vehicle->category)
                ->active()
                ->orderBy('sort_order')
                ->get()
                ->map(fn (VehicleRentalTerm $t): array => [
                    'type' => $t->type?->value,
                    'type_label' => $t->type?->getLabel(),
                    'title' => $t->getTranslation('title', $locale, false) ?: $t->getTranslation('title', 'id', false),
                    'content' => $t->getTranslation('content', $locale, false) ?: $t->getTranslation('content', 'id', false),
                ])
                ->all();
        }

        $areaRates = $vehicle->rentalRates->map(fn (VehicleRentalRate $rate): array => [
            'id' => $rate->id,
            'area_id' => $rate->vehicle_rental_area_id,
            'area_name' => $rate->area ? ($rate->area->getTranslation('name', $locale, false) ?: $rate->area->getTranslation('name', 'id', false)) : null,
            'area_slug' => $rate->area?->slug,
            'price_per_day_idr' => $rate->price_per_day_idr,
            'valid_from' => $rate->valid_from?->toDateString(),
            'valid_until' => $rate->valid_until?->toDateString(),
            'is_active' => $rate->is_active,
        ])->all();

        return Response::json([
            'id' => $vehicle->id,
            'catalog_code' => $vehicle->catalog_code,
            'name' => $vehicle->getTranslation('name', $locale, false) ?: $vehicle->getTranslation('name', 'id', false),
            'slug' => $vehicle->getTranslation('slug', $locale, false) ?: $vehicle->getTranslation('slug', 'id', false),
            'category' => $vehicle->category?->value,
            'category_label' => $vehicle->category?->getLabel(),
            'brand' => $vehicle->brand,
            'model' => $vehicle->model,
            'year' => $vehicle->year,
            'description' => $vehicle->getTranslation('description', $locale, false) ?: $vehicle->getTranslation('description', 'id', false),
            'specifications' => [
                'capacity_pax' => $vehicle->capacity_pax,
                'capacity_luggage' => $vehicle->capacity_luggage,
                'capacity_label' => $vehicle->getTranslation('capacity_label', $locale, false) ?: $vehicle->getTranslation('capacity_label', 'id', false),
                'transmission' => $vehicle->transmission,
                'has_ac' => $vehicle->has_ac,
                'has_wifi' => $vehicle->has_wifi,
                'features' => $vehicle->getTranslation('features', $locale, false) ?: $vehicle->getTranslation('features', 'id', false),
            ],
            'pricing_idr' => [
                'price_per_day' => $vehicle->price_per_day_idr,
                'price_per_trip' => $vehicle->price_per_trip_idr,
                'overtime_rate' => $vehicle->overtime_rate_idr,
            ],
            'countries' => $vehicle->countries->map(fn (Country $c) => [
                'id' => $c->id,
                'name' => $c->getTranslation('name', $locale, false) ?: $c->getTranslation('name', 'id', false),
                'slug' => $c->slug,
                'iso_alpha_2' => $c->iso_alpha_2,
            ])->values()->all(),
            'area_rental_rates' => $areaRates,
            'rental_terms' => $terms,
            'translations' => [
                'name' => $vehicle->getTranslations('name'),
                'slug' => $vehicle->getTranslations('slug'),
                'description' => $vehicle->getTranslations('description'),
                'features' => $vehicle->getTranslations('features'),
                'capacity_label' => $vehicle->getTranslations('capacity_label'),
            ],
            'is_active' => $vehicle->is_active,
            'is_featured' => $vehicle->is_featured,
            'thumbnail_url' => $vehicle->thumbnail_url,
            'updated_at' => $vehicle->updated_at?->toIso8601String(),
        ]);
    }
}
