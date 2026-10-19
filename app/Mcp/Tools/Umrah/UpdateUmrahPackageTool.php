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
use Illuminate\Support\Str;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('update_umrah_package')]
#[Title('Update Umrah Package')]
#[Description('Update an existing Umrah package by ID, including multilingual text, accommodations, pricing, status, and associations.')]
class UpdateUmrahPackageTool extends Tool
{
    use NormalizesMultilingualInput;

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('Numeric ID of the Umrah package to update.')->required(),
            'name' => $schema->mixed()->description('Updated package name (plain string or multilingual map).'),
            'slug' => $schema->mixed()->description('Updated URL slug (plain string or multilingual map).'),
            'description' => $schema->mixed()->description('Updated package description (plain text, Markdown, or HTML; auto-converted to semantic HTML).'),
            'package_type' => $schema->string()->description('Updated package type: "regular", "plus", "vip", "ramadan", or "private".'),
            'duration_days' => $schema->integer()->description('Updated duration in days.'),
            'price_idr' => $schema->integer()->description('Updated base reference price in IDR.'),
            'airline' => $schema->string()->description('Updated airline name.'),
            'hotel_makkah' => $schema->string()->description('Updated hotel name in Makkah.'),
            'hotel_makkah_stars' => $schema->integer()->description('Updated hotel stars in Makkah (3 to 5).'),
            'hotel_madinah' => $schema->string()->description('Updated hotel name in Madinah.'),
            'hotel_madinah_stars' => $schema->integer()->description('Updated hotel stars in Madinah (3 to 5).'),
            'visa_included' => $schema->boolean()->description('Updated visa inclusion flag.'),
            'handling_included' => $schema->boolean()->description('Updated handling inclusion flag.'),
            'is_active' => $schema->boolean()->description('Updated active status.'),
            'is_featured' => $schema->boolean()->description('Updated featured status.'),
            'destination_ids' => $schema->array()->description('Updated array of destination/ziarah IDs to sync.'),
            'country_ids' => $schema->array()->description('Updated array of country IDs to sync.'),
            'vehicle_ids' => $schema->array()->description('Updated array of vehicle IDs to sync.'),
        ];
    }

    public function handle(Request $request): Response
    {
        $id = $request->get('id');
        if (empty($id)) {
            return Response::error('The "id" parameter is required.');
        }

        $package = UmrahPackage::find((int) $id);
        if (! $package) {
            return Response::error("Umrah package with ID {$id} not found.");
        }

        if ($request->has('name') && ! empty($request->get('name'))) {
            $rawName = $request->get('name');
            $existing = $package->getTranslations('name');
            $incoming = is_array($rawName) ? $rawName : $this->normalizeMultilingualInput($rawName);
            $package->setTranslations('name', array_merge($existing, array_filter($incoming, fn ($v) => $v !== null && $v !== '')));
        }

        if ($request->has('slug') && ! empty($request->get('slug'))) {
            $rawSlug = $request->get('slug');
            $existing = $package->getTranslations('slug');
            $incoming = is_array($rawSlug)
                ? array_map(fn ($v) => Str::slug((string) $v), $rawSlug)
                : array_map(fn ($v) => Str::slug((string) $v), $this->normalizeMultilingualInput($rawSlug));
            $package->setTranslations('slug', array_merge($existing, array_filter($incoming, fn ($v) => $v !== '')));
        }

        if ($request->has('description') && ! empty($request->get('description'))) {
            $rawDesc = $request->get('description');
            $existing = $package->getTranslations('description');
            $incoming = is_array($rawDesc)
                ? array_map(fn ($v) => $this->ensureRichTextHtml((string) $v), $rawDesc)
                : $this->normalizeRichTextMultilingual($rawDesc);
            $package->setTranslations('description', array_merge($existing, array_filter($incoming, fn ($v) => $v !== '')));
        }

        if ($request->filled('package_type')) {
            $rawType = (string) $request->get('package_type');
            $packageType = UmrahPackageType::tryFrom($rawType);
            if (! $packageType) {
                return Response::error("Invalid package_type '{$rawType}'.");
            }
            $package->package_type = $packageType->value;
        }

        if ($request->filled('duration_days')) {
            $package->duration_days = max(1, (int) $request->get('duration_days'));
        }

        if ($request->filled('price_idr')) {
            $package->price_idr = max(1, (int) $request->get('price_idr'));
        }

        if ($request->has('airline')) {
            $airline = $request->get('airline');
            $package->airline = $airline !== null && trim((string) $airline) !== '' ? trim((string) $airline) : null;
        }

        if ($request->has('hotel_makkah')) {
            $hotel = $request->get('hotel_makkah');
            $package->hotel_makkah = $hotel !== null && trim((string) $hotel) !== '' ? trim((string) $hotel) : null;
        }

        if ($request->has('hotel_makkah_stars')) {
            $package->hotel_makkah_stars = $request->get('hotel_makkah_stars') !== null ? (int) $request->get('hotel_makkah_stars') : null;
        }

        if ($request->has('hotel_madinah')) {
            $hotel = $request->get('hotel_madinah');
            $package->hotel_madinah = $hotel !== null && trim((string) $hotel) !== '' ? trim((string) $hotel) : null;
        }

        if ($request->has('hotel_madinah_stars')) {
            $package->hotel_madinah_stars = $request->get('hotel_madinah_stars') !== null ? (int) $request->get('hotel_madinah_stars') : null;
        }

        if ($request->has('visa_included')) {
            $package->visa_included = (bool) $request->get('visa_included');
        }

        if ($request->has('handling_included')) {
            $package->handling_included = (bool) $request->get('handling_included');
        }

        if ($request->has('is_active')) {
            $package->is_active = (bool) $request->get('is_active');
        }

        if ($request->has('is_featured')) {
            $package->is_featured = (bool) $request->get('is_featured');
        }

        if ($request->has('destination_ids') && is_array($request->get('destination_ids'))) {
            $rawDestIds = array_values(array_unique(array_map('intval', $request->get('destination_ids'))));
            if (! empty($rawDestIds) && Destination::whereIn('id', $rawDestIds)->count() !== count($rawDestIds)) {
                return Response::error('One or more provided destination_ids do not exist.');
            }
            $package->destinations()->sync($rawDestIds);
        }

        if ($request->has('country_ids') && is_array($request->get('country_ids'))) {
            $rawCountryIds = array_values(array_unique(array_map('intval', $request->get('country_ids'))));
            if (! empty($rawCountryIds) && Country::whereIn('id', $rawCountryIds)->count() !== count($rawCountryIds)) {
                return Response::error('One or more provided country_ids do not exist.');
            }
            $package->countries()->sync($rawCountryIds);
        }

        if ($request->has('vehicle_ids') && is_array($request->get('vehicle_ids'))) {
            $rawVehicleIds = array_values(array_unique(array_map('intval', $request->get('vehicle_ids'))));
            if (! empty($rawVehicleIds) && Vehicle::whereIn('id', $rawVehicleIds)->count() !== count($rawVehicleIds)) {
                return Response::error('One or more provided vehicle_ids do not exist.');
            }
            $package->vehicles()->sync($rawVehicleIds);
        }

        $package->save();
        $package->load(['destinations', 'countries', 'vehicles']);

        return Response::json([
            'message' => 'Paket umrah berhasil diperbarui.',
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
                'updated_at' => $package->updated_at?->toIso8601String(),
            ],
        ]);
    }
}
