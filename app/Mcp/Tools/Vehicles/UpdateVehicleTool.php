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

#[Name('update_vehicle')]
#[Title('Update Vehicle & Fleet Entry')]
#[Description('Update an existing fleet vehicle specifications, pricing, country assignments, or area rental rates.')]
class UpdateVehicleTool extends Tool
{
    use NormalizesMultilingualInput;

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('The numeric ID of the vehicle to update.'),
            'slug' => $schema->string()->description('The URL slug of the vehicle to update (used if "id" is omitted).'),
            'catalog_code' => $schema->string()->description('The catalog code of the vehicle to update (used if "id" and "slug" are omitted).'),
            'name' => $schema->mixed()->description('New vehicle name. Single string or multilingual map.'),
            'new_slug' => $schema->string()->description('New URL slug for the vehicle.'),
            'new_catalog_code' => $schema->string()->description('New catalog code for the vehicle.'),
            'category' => $schema->string()->description('New category: "car", "minibus", or "bus".'),
            'brand' => $schema->string()->description('Manufacturer brand.'),
            'model' => $schema->string()->description('Model series.'),
            'year' => $schema->integer()->description('Vehicle year.'),
            'capacity_pax' => $schema->integer()->description('Passenger capacity.'),
            'capacity_luggage' => $schema->integer()->description('Luggage capacity count.'),
            'capacity_label' => $schema->mixed()->description('Human-readable capacity summary label.'),
            'transmission' => $schema->string()->description('Transmission type: "automatic" or "manual".'),
            'has_ac' => $schema->boolean()->description('Equipped with Air Conditioning.'),
            'has_wifi' => $schema->boolean()->description('Equipped with WiFi.'),
            'price_per_day_idr' => $schema->integer()->description('Baseline daily rental rate in IDR.'),
            'price_per_trip_idr' => $schema->integer()->description('Baseline per-trip rate in IDR.'),
            'overtime_rate_idr' => $schema->integer()->description('Overtime hourly charge in IDR.'),
            'description' => $schema->mixed()->description('Detailed overview description.'),
            'features' => $schema->mixed()->description('Key features or amenities description.'),
            'country_ids' => $schema->array()->description('New list of Country IDs (replaces current country links).'),
            'country_slugs' => $schema->array()->description('New list of Country slugs.'),
            'rental_rates' => $schema->array()->description('If provided, creates or syncs area-specific rates: [{area_id: int, area_slug: string, price_per_day_idr: int, valid_from: string, valid_until: string}].'),
            'is_active' => $schema->boolean()->description('Publish status.'),
            'is_featured' => $schema->boolean()->description('Feature on home/landing page.'),
            'sort_order' => $schema->integer()->description('Display sorting weight.'),
        ];
    }

    public function handle(Request $request): Response
    {
        $id = $request->get('id');
        $slug = $request->get('slug');
        $catalogCode = $request->get('catalog_code');

        if (empty($id) && empty($slug) && empty($catalogCode)) {
            return Response::error('At least one of "id", "slug", or "catalog_code" must be provided.');
        }

        /** @var Vehicle|null $vehicle */
        if (! empty($id)) {
            $vehicle = Vehicle::find((int) $id);
        } elseif (! empty($catalogCode)) {
            $vehicle = Vehicle::where('catalog_code', (string) $catalogCode)->first();
        } else {
            $vehicle = Vehicle::whereLocalizedSlug((string) $slug)->first();
        }

        if (! $vehicle) {
            $identifier = ! empty($id)
                ? "ID {$id}"
                : (! empty($catalogCode) ? "catalog code '{$catalogCode}'" : "slug '{$slug}'");

            return Response::error("Vehicle with {$identifier} not found.");
        }

        $attributesToUpdate = [];

        // 1. Name
        if ($request->has('name')) {
            $rawName = $request->get('name');
            if (empty($rawName)) {
                return Response::error('Vehicle name cannot be empty.');
            }
            $currentName = $vehicle->getTranslations('name');
            $attributesToUpdate['name'] = $this->mergeTranslations($currentName, $rawName);
        }

        // 2. New Slug
        if ($request->has('new_slug')) {
            $newSlug = Str::slug(trim((string) $request->get('new_slug')));
            if (empty($newSlug)) {
                return Response::error('New slug cannot be empty.');
            }
            $conflict = Vehicle::query()
                ->whereLocalizedSlug($newSlug)
                ->where('id', '!=', $vehicle->id)
                ->exists();
            if ($conflict) {
                return Response::error("A vehicle with slug '{$newSlug}' already exists.");
            }
            $currentSlugs = $vehicle->getTranslations('slug');
            $currentSlugs['id'] = $newSlug;
            $attributesToUpdate['slug'] = $currentSlugs;
        }

        // 3. New Catalog Code
        if ($request->has('new_catalog_code')) {
            $newCode = Str::upper(trim((string) $request->get('new_catalog_code')));
            if (empty($newCode)) {
                return Response::error('New catalog code cannot be empty.');
            }
            $conflict = Vehicle::query()
                ->where('catalog_code', $newCode)
                ->where('id', '!=', $vehicle->id)
                ->exists();
            if ($conflict) {
                return Response::error("A vehicle with catalog code '{$newCode}' already exists.");
            }
            $attributesToUpdate['catalog_code'] = $newCode;
        }

        // 4. Category
        if ($request->has('category')) {
            $val = (string) $request->get('category');
            $cat = VehicleCategory::tryFrom($val);
            if (! $cat) {
                return Response::error("Invalid category '{$val}'. Allowed: 'car', 'minibus', 'bus'.");
            }
            $attributesToUpdate['category'] = $cat;
        }

        // 5. Specs & Basic fields
        foreach (['brand', 'model'] as $field) {
            if ($request->has($field)) {
                $attributesToUpdate[$field] = filled($request->get($field)) ? trim((string) $request->get($field)) : null;
            }
        }

        foreach (['year', 'capacity_pax', 'capacity_luggage', 'price_per_day_idr', 'price_per_trip_idr', 'overtime_rate_idr', 'sort_order'] as $field) {
            if ($request->has($field)) {
                $attributesToUpdate[$field] = $request->get($field) !== null ? (int) $request->get($field) : null;
            }
        }

        if ($request->has('transmission')) {
            $attributesToUpdate['transmission'] = (string) $request->get('transmission');
        }

        foreach (['has_ac', 'has_wifi', 'is_active', 'is_featured'] as $field) {
            if ($request->has($field)) {
                $attributesToUpdate[$field] = (bool) $request->get($field);
            }
        }

        // 6. Multilingual texts
        foreach (['description', 'features', 'capacity_label'] as $field) {
            if ($request->has($field)) {
                $current = $vehicle->getTranslations($field);
                $attributesToUpdate[$field] = $this->mergeTranslations($current, $request->get($field));
            }
        }

        // 7. Resolve countries
        $countrySyncNeeded = false;
        $countryIds = [];
        if ($request->has('country_ids') || $request->has('country_slugs')) {
            $countrySyncNeeded = true;
            if ($request->has('country_ids') && is_array($request->get('country_ids'))) {
                $countryIds = array_map('intval', $request->get('country_ids'));
            }
            if ($request->has('country_slugs') && is_array($request->get('country_slugs'))) {
                $slugs = array_map('strval', $request->get('country_slugs'));
                $foundIds = Country::query()->whereIn('slug', $slugs)->pluck('id')->all();
                $countryIds = array_unique(array_merge($countryIds, $foundIds));
            }
        }

        $rawRates = $request->has('rental_rates') ? $request->get('rental_rates') : null;

        DB::transaction(function () use ($vehicle, $attributesToUpdate, $countrySyncNeeded, $countryIds, $rawRates): void {
            if (! empty($attributesToUpdate)) {
                $vehicle->update($attributesToUpdate);
            }

            if ($countrySyncNeeded) {
                $vehicle->countries()->sync($countryIds);
            }

            if (is_array($rawRates)) {
                // Remove existing rates and recreate
                $vehicle->rentalRates()->delete();

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

                    $priceDay = isset($rawRate['price_per_day_idr']) ? (int) $rawRate['price_per_day_idr'] : $vehicle->price_per_day_idr;
                    $validFrom = ! empty($rawRate['valid_from']) ? Carbon::parse((string) $rawRate['valid_from']) : now();
                    $validUntil = ! empty($rawRate['valid_until']) ? Carbon::parse((string) $rawRate['valid_until']) : null;

                    VehicleRentalRate::create([
                        'vehicle_id' => $vehicle->id,
                        'vehicle_rental_area_id' => (int) $areaId,
                        'price_per_day_idr' => (int) $priceDay,
                        'valid_from' => $validFrom,
                        'valid_until' => $validUntil,
                        'is_active' => (bool) ($rawRate['is_active'] ?? true),
                    ]);
                }
            }
        });

        $vehicle->refresh();
        $vehicle->load(['countries', 'rentalRates']);
        $vehicleName = $vehicle->getTranslation('name', 'id', false);

        return Response::json([
            'message' => "Vehicle '{$vehicleName}' updated successfully.",
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
                'updated_at' => $vehicle->updated_at?->toIso8601String(),
            ],
        ]);
    }

    /**
     * Merge current translations with input.
     *
     * @param  array<string, mixed>  $current
     * @return array<string, string>
     */
    protected function mergeTranslations(array $current, mixed $input): array
    {
        if (is_string($input)) {
            $current['id'] = trim($input);

            return $current;
        }

        if (is_array($input)) {
            foreach ($input as $loc => $val) {
                if (filled($val)) {
                    $current[(string) $loc] = trim((string) $val);
                }
            }
        }

        return $current;
    }
}
