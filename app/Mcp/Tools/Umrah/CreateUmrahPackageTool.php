<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Umrah;

use App\Enums\UmrahPackageType;
use App\Mcp\Tools\Concerns\NormalizesMultilingualInput;
use App\Models\Country;
use App\Models\Destination;
use App\Models\UmrahPackage;
use App\Models\Vehicle;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('create_umrah_package')]
#[Title('Create Umrah Package')]
#[Description('Create a new Umrah pilgrimage package with multilingual name/slug/description, accommodations, airline, duration, and optional ziarah destination, country, and vehicle associations.')]
class CreateUmrahPackageTool extends Tool
{
    use NormalizesMultilingualInput;

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->union(['string', 'object'])->description('Package name (plain string or multilingual map {"id": "...", "en": "...", "ms": "..."}). Required.'),
            'slug' => $schema->union(['string', 'object'])->description('Optional custom URL slug (plain string or multilingual map). Auto-generated from name if omitted.'),
            'description' => $schema->union(['string', 'object'])->description('Detailed package description (plain text, Markdown, or HTML; auto-converted to semantic HTML). Required.'),
            'package_type' => $schema->string()->description('Package type: "regular", "plus", "vip", "ramadan", or "private". Default: "regular".'),
            'duration_days' => $schema->integer()->description('Total duration of pilgrimage in days (min: 1). Required.'),
            'price_idr' => $schema->integer()->description('Base reference price in IDR (min: 1). Required.'),
            'airline' => $schema->string()->description('Airline name (e.g. "Saudia Airlines", "Garuda Indonesia").'),
            'hotel_makkah' => $schema->string()->description('Hotel name in Makkah (e.g. "Hilton Suites Makkah").'),
            'hotel_makkah_stars' => $schema->integer()->description('Hotel star rating in Makkah (3 to 5).'),
            'hotel_madinah' => $schema->string()->description('Hotel name in Madinah (e.g. "Pullman Zamzam Madinah").'),
            'hotel_madinah_stars' => $schema->integer()->description('Hotel star rating in Madinah (3 to 5).'),
            'visa_included' => $schema->boolean()->description('Whether Umrah visa processing is included in package price (default: true).'),
            'handling_included' => $schema->boolean()->description('Whether airport handling and equipment are included (default: true).'),
            'is_active' => $schema->boolean()->description('Whether the package is active and publicly visible (default: true).'),
            'is_featured' => $schema->boolean()->description('Whether the package is highlighted as featured (default: false).'),
            'destination_ids' => $schema->array()->description('Optional array of numeric destination/ziarah IDs visited.'),
            'country_ids' => $schema->array()->description('Optional array of numeric country IDs visited (e.g., Saudi Arabia, Turkey, Egypt).'),
            'vehicle_ids' => $schema->array()->description('Optional array of numeric vehicle/bus IDs used.'),
        ];
    }

    public function handle(Request $request): Response
    {
        $rawName = $request->get('name');
        if (empty($rawName)) {
            return Response::error('The "name" parameter is required.');
        }

        $name = $this->normalizeMultilingualInput($rawName);
        if (empty($name['id'])) {
            return Response::error('Package name for Indonesian (id) cannot be empty.');
        }

        $rawDesc = $request->get('description');
        if (empty($rawDesc)) {
            return Response::error('The "description" parameter is required.');
        }
        $description = $this->normalizeRichTextMultilingual($rawDesc);

        $durationDays = (int) $request->get('duration_days', 0);
        if ($durationDays < 1) {
            return Response::error('The "duration_days" parameter is required and must be at least 1.');
        }

        $priceIdr = (int) $request->get('price_idr', 0);
        if ($priceIdr < 1) {
            return Response::error('The "price_idr" parameter is required and must be greater than 0.');
        }

        $slug = $this->resolveSlugTranslations($name, $request->get('slug'));

        $rawType = (string) $request->get('package_type', 'regular');
        $packageType = UmrahPackageType::tryFrom($rawType);
        if (! $packageType) {
            return Response::error("Invalid package_type '{$rawType}'. Allowed values: regular, plus, vip, ramadan, private.");
        }

        $destinationIds = [];
        if ($request->has('destination_ids') && is_array($request->get('destination_ids'))) {
            $rawDestIds = array_values(array_unique(array_map('intval', $request->get('destination_ids'))));
            if (! empty($rawDestIds)) {
                if (Destination::whereIn('id', $rawDestIds)->count() !== count($rawDestIds)) {
                    return Response::error('One or more provided destination_ids do not exist.');
                }
                $destinationIds = $rawDestIds;
            }
        }

        $countryIds = [];
        if ($request->has('country_ids') && is_array($request->get('country_ids'))) {
            $rawCountryIds = array_values(array_unique(array_map('intval', $request->get('country_ids'))));
            if (! empty($rawCountryIds)) {
                if (Country::whereIn('id', $rawCountryIds)->count() !== count($rawCountryIds)) {
                    return Response::error('One or more provided country_ids do not exist.');
                }
                $countryIds = $rawCountryIds;
            }
        }

        $vehicleIds = [];
        if ($request->has('vehicle_ids') && is_array($request->get('vehicle_ids'))) {
            $rawVehicleIds = array_values(array_unique(array_map('intval', $request->get('vehicle_ids'))));
            if (! empty($rawVehicleIds)) {
                if (Vehicle::whereIn('id', $rawVehicleIds)->count() !== count($rawVehicleIds)) {
                    return Response::error('One or more provided vehicle_ids do not exist.');
                }
                $vehicleIds = $rawVehicleIds;
            }
        }

        $package = UmrahPackage::create([
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
            'package_type' => $packageType->value,
            'duration_days' => $durationDays,
            'price_idr' => $priceIdr,
            'airline' => $request->filled('airline') ? trim((string) $request->get('airline')) : null,
            'hotel_makkah' => $request->filled('hotel_makkah') ? trim((string) $request->get('hotel_makkah')) : null,
            'hotel_makkah_stars' => $request->has('hotel_makkah_stars') ? (int) $request->get('hotel_makkah_stars') : null,
            'hotel_madinah' => $request->filled('hotel_madinah') ? trim((string) $request->get('hotel_madinah')) : null,
            'hotel_madinah_stars' => $request->has('hotel_madinah_stars') ? (int) $request->get('hotel_madinah_stars') : null,
            'visa_included' => $request->has('visa_included') ? (bool) $request->get('visa_included') : true,
            'handling_included' => $request->has('handling_included') ? (bool) $request->get('handling_included') : true,
            'is_active' => $request->has('is_active') ? (bool) $request->get('is_active') : true,
            'is_featured' => $request->has('is_featured') ? (bool) $request->get('is_featured') : false,
        ]);

        if (! empty($destinationIds)) {
            $package->destinations()->sync($destinationIds);
        }
        if (! empty($countryIds)) {
            $package->countries()->sync($countryIds);
        }
        if (! empty($vehicleIds)) {
            $package->vehicles()->sync($vehicleIds);
        }

        $package->load(['destinations', 'countries', 'vehicles']);

        return Response::json([
            'message' => 'Paket umrah berhasil dibuat.',
            'package' => [
                'id' => $package->id,
                'name' => $package->getTranslations('name'),
                'slug' => $package->getTranslations('slug'),
                'package_type' => $package->package_type,
                'duration_days' => (int) $package->duration_days,
                'price_idr' => (int) $package->price_idr,
                'airline' => $package->airline,
                'hotel_makkah' => $package->hotel_makkah,
                'hotel_madinah' => $package->hotel_madinah,
                'is_active' => (bool) $package->is_active,
                'is_featured' => (bool) $package->is_featured,
                'created_at' => $package->created_at?->toIso8601String(),
            ],
        ]);
    }
}
