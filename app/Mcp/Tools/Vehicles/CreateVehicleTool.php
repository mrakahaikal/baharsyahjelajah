<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Vehicles;

use App\Enums\VehicleCategory;
use App\Mcp\Tools\Concerns\NormalizesMultilingualInput;
use App\Models\Country;
use App\Models\Vehicle;
use App\Models\VehicleRentalArea;
use App\Models\VehicleRentalRate;
use Carbon\Carbon;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('create_vehicle')]
#[Title('Create Vehicle & Fleet Entry')]
#[Description('Create a new vehicle with technical specifications, pricing, country links, and optional area rental rates in an atomic transaction.')]
class CreateVehicleTool extends Tool
{
    use NormalizesMultilingualInput;

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->union(['string', 'object'])->description('The vehicle title/model name. Single string or multilingual map {"id": "...", "en": "..."}. Required.'),
            'slug' => $schema->union(['string', 'object'])->description('URL slug for the vehicle. Single string or multilingual map. Auto-generated if omitted.'),
            'catalog_code' => $schema->string()->description('Unique inventory/catalog code (e.g. "HIACE-PREMIO-01"). Auto-generated if omitted.'),
            'category' => $schema->string()->description('Vehicle category: "car", "minibus", or "bus". Required.'),
            'brand' => $schema->string()->description('Manufacturer brand (e.g. "Toyota", "Isuzu", "Mitsubishi").'),
            'model' => $schema->string()->description('Vehicle model series (e.g. "HiAce Premio Luxury", "Innova Zenix").'),
            'year' => $schema->integer()->description('Vehicle production/assembly year (e.g. 2023).'),
            'capacity_pax' => $schema->integer()->description('Maximum passenger seat capacity. Required.'),
            'capacity_luggage' => $schema->integer()->description('Luggage/suitcase capacity count. Default is 0.'),
            'capacity_label' => $schema->union(['string', 'object'])->description('Human-readable capacity summary label (e.g. "6-7 Penumpang"). Single string or map.'),
            'transmission' => $schema->string()->description('Transmission type: "automatic" or "manual". Default is "automatic".'),
            'has_ac' => $schema->boolean()->description('Equipped with Air Conditioning. Default is true.'),
            'has_wifi' => $schema->boolean()->description('Equipped with WiFi. Default is false.'),
            'price_per_day_idr' => $schema->integer()->description('Default baseline daily rental rate in IDR.'),
            'price_per_trip_idr' => $schema->integer()->description('Default baseline per-trip rate in IDR (e.g. airport transfer).'),
            'overtime_rate_idr' => $schema->integer()->description('Overtime hourly charge in IDR.'),
            'description' => $schema->union(['string', 'object'])->description('Detailed overview or description. Single string or map.'),
            'features' => $schema->union(['string', 'object'])->description('Key features or amenities description. Single string or map.'),
            'country_ids' => $schema->array()->description('List of numeric Country IDs operating this vehicle.'),
            'country_slugs' => $schema->array()->description('List of Country URL slugs (e.g. ["indonesia", "arab-saudi"]).'),
            'rental_rates' => $schema->array()->description('Optional area-specific rental rates: [{area_id: int, area_slug: string, price_per_day_idr: int, valid_from: string, valid_until: string}].'),
            'is_active' => $schema->boolean()->description('Publish status. Default is true.'),
            'is_featured' => $schema->boolean()->description('Feature on home/landing page. Default is false.'),
            'sort_order' => $schema->integer()->description('Display sorting weight. Default is 0.'),
        ];
    }

    public function handle(Request $request): Response
    {
        // 1. Validate & normalize name
        $rawName = $request->get('name');
        if (empty($rawName)) {
            return Response::error('The "name" parameter is required.');
        }
        $name = $this->normalizeMultilingualInput($rawName);
        if (empty($name['id'])) {
            return Response::error('Vehicle name for Indonesian (id) cannot be empty.');
        }

        // 2. Validate category
        $rawCategory = $request->get('category');
        if (empty($rawCategory)) {
            return Response::error('The "category" parameter is required ("car", "minibus", "bus").');
        }
        $category = VehicleCategory::tryFrom((string) $rawCategory);
        if (! $category) {
            return Response::error("Invalid category '{$rawCategory}'. Allowed: 'car', 'minibus', 'bus'.");
        }

        // 3. Validate passenger capacity
        $capacityPax = $request->get('capacity_pax');
        if ($capacityPax === null || (int) $capacityPax <= 0) {
            return Response::error('The "capacity_pax" parameter is required and must be greater than 0.');
        }

        // 4. Resolve slug
        $rawSlug = $request->get('slug');
        $slug = $this->resolveSlugTranslations($name, $rawSlug);

        if (Vehicle::query()->whereLocalizedSlug($slug['id'])->exists()) {
            return Response::error("Vehicle with slug '{$slug['id']}' already exists.");
        }

        // 5. Catalog code
        $catalogCode = $request->get('catalog_code');
        if (filled($catalogCode)) {
            $catalogCode = Str::upper(trim((string) $catalogCode));
            if (Vehicle::query()->where('catalog_code', $catalogCode)->exists()) {
                return Response::error("Vehicle with catalog code '{$catalogCode}' already exists.");
            }
        } else {
            $catalogCode = 'VH-'.Str::upper(Str::slug($name['id'])).'-'.Str::upper(Str::random(4));
        }

        // 6. Multilingual texts
        $description = filled($request->get('description')) ? $this->normalizeMultilingualInput($request->get('description')) : null;
        $features = filled($request->get('features')) ? $this->normalizeMultilingualInput($request->get('features')) : null;
        $capacityLabel = filled($request->get('capacity_label')) ? $this->normalizeMultilingualInput($request->get('capacity_label')) : null;

        // 7. Resolve countries
        $countryIds = [];
        if ($request->has('country_ids') && is_array($request->get('country_ids'))) {
            $countryIds = array_map('intval', $request->get('country_ids'));
        }
        if ($request->has('country_slugs') && is_array($request->get('country_slugs'))) {
            $slugs = array_map('strval', $request->get('country_slugs'));
            $foundIds = Country::query()->whereIn('slug', $slugs)->pluck('id')->all();
            $countryIds = array_unique(array_merge($countryIds, $foundIds));
        }

        $vehicleData = [
            'catalog_code' => $catalogCode,
            'name' => $name,
            'slug' => $slug,
            'category' => $category,
            'brand' => filled($request->get('brand')) ? trim((string) $request->get('brand')) : null,
            'model' => filled($request->get('model')) ? trim((string) $request->get('model')) : null,
            'year' => $request->has('year') && $request->get('year') !== null ? (int) $request->get('year') : null,
            'capacity_pax' => (int) $capacityPax,
            'capacity_luggage' => (int) $request->get('capacity_luggage', 0),
            'capacity_label' => $capacityLabel,
            'transmission' => (string) $request->get('transmission', 'automatic'),
            'has_ac' => (bool) $request->get('has_ac', true),
            'has_wifi' => (bool) $request->get('has_wifi', false),
            'price_per_day_idr' => $request->has('price_per_day_idr') && $request->get('price_per_day_idr') !== null ? (int) $request->get('price_per_day_idr') : null,
            'price_per_trip_idr' => $request->has('price_per_trip_idr') && $request->get('price_per_trip_idr') !== null ? (int) $request->get('price_per_trip_idr') : null,
            'overtime_rate_idr' => $request->has('overtime_rate_idr') && $request->get('overtime_rate_idr') !== null ? (int) $request->get('overtime_rate_idr') : null,
            'description' => $description,
            'features' => $features,
            'is_active' => (bool) $request->get('is_active', true),
            'is_featured' => (bool) $request->get('is_featured', false),
            'sort_order' => (int) $request->get('sort_order', 0),
        ];

        $rawRates = $request->get('rental_rates', []);

        /** @var Vehicle $vehicle */
        $vehicle = DB::transaction(function () use ($vehicleData, $countryIds, $rawRates): Vehicle {
            /** @var Vehicle $created */
            $created = Vehicle::create($vehicleData);

            if (! empty($countryIds)) {
                $created->countries()->sync($countryIds);
            }

            if (is_array($rawRates) && ! empty($rawRates)) {
                foreach ($rawRates as $rawRate) {
                    if (! is_array($rawRate)) {
                        continue;
                    }

                    $areaId = $rawRate['area_id'] ?? null;
                    if (empty($areaId) && ! empty($rawRate['area_slug'])) {
                        $area = VehicleRentalArea::where('slug', (string) $rawRate['area_slug'])->first();
                        $areaId = $area?->id;
                    }

                    if (empty($areaId)) {
                        continue;
                    }

                    $priceDay = isset($rawRate['price_per_day_idr']) ? (int) $rawRate['price_per_day_idr'] : $created->price_per_day_idr;
                    $validFrom = ! empty($rawRate['valid_from']) ? Carbon::parse((string) $rawRate['valid_from']) : now();
                    $validUntil = ! empty($rawRate['valid_until']) ? Carbon::parse((string) $rawRate['valid_until']) : null;

                    VehicleRentalRate::create([
                        'vehicle_id' => $created->id,
                        'vehicle_rental_area_id' => (int) $areaId,
                        'price_per_day_idr' => (int) $priceDay,
                        'valid_from' => $validFrom,
                        'valid_until' => $validUntil,
                        'is_active' => (bool) ($rawRate['is_active'] ?? true),
                    ]);
                }
            }

            return $created;
        });

        $vehicle->load(['countries', 'rentalRates']);
        $vehicleName = $vehicle->getTranslation('name', 'id', false);

        return Response::json([
            'message' => "Vehicle '{$vehicleName}' created successfully.",
            'vehicle' => [
                'id' => $vehicle->id,
                'catalog_code' => $vehicle->catalog_code,
                'name' => $vehicleName,
                'slug' => $vehicle->getTranslation('slug', 'id', false),
                'category' => $vehicle->category?->value,
                'capacity_pax' => $vehicle->capacity_pax,
                'price_per_day_idr' => $vehicle->price_per_day_idr,
                'countries_count' => $vehicle->countries->count(),
                'rental_rates_count' => $vehicle->rentalRates->count(),
                'is_active' => $vehicle->is_active,
                'is_featured' => $vehicle->is_featured,
            ],
        ]);
    }
}
