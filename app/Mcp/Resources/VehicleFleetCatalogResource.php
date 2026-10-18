<?php

declare(strict_types=1);

namespace App\Mcp\Resources;

use App\Models\Country;
use App\Models\Vehicle;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\MimeType;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Attributes\Uri;
use Laravel\Mcp\Server\Resource;

#[Name('vehicle_fleet_catalog')]
#[Title('Katalog Armada Sewa Kendaraan')]
#[Description('Katalog terstruktur seluruh armada kendaraan aktif terkelompok per kategori (mobil, minibus, bus), kapasitas penumpang & bagasi, fitur AC/WiFi/transmisi, dan rentang tarif sewa harian.')]
#[Uri('vehicles://fleet-catalog')]
#[MimeType('application/json')]
class VehicleFleetCatalogResource extends Resource
{
    /**
     * Handle the resource request.
     */
    public function handle(Request $request): Response
    {
        $vehicles = Vehicle::query()
            ->active()
            ->with(['countries', 'rentalRates'])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (Vehicle $v): array => [
                'id' => $v->id,
                'catalog_code' => $v->catalog_code,
                'name' => [
                    'id' => $v->getTranslation('name', 'id', false),
                    'en' => $v->getTranslation('name', 'en', false),
                    'ms' => $v->getTranslation('name', 'ms', false),
                ],
                'slug' => $v->getTranslation('slug', 'id', false),
                'category' => $v->category?->value,
                'category_label' => $v->category?->getLabel(),
                'brand' => $v->brand,
                'model' => $v->model,
                'year' => $v->year,
                'specifications' => [
                    'capacity_pax' => $v->capacity_pax,
                    'capacity_luggage' => $v->capacity_luggage,
                    'transmission' => $v->transmission,
                    'has_ac' => $v->has_ac,
                    'has_wifi' => $v->has_wifi,
                ],
                'pricing_idr' => [
                    'price_per_day' => $v->price_per_day_idr,
                    'price_per_trip' => $v->price_per_trip_idr,
                    'overtime_rate' => $v->overtime_rate_idr,
                ],
                'operating_countries' => $v->countries->map(fn (Country $c) => [
                    'id' => $c->id,
                    'name' => $c->getTranslation('name', 'id', false),
                    'slug' => $c->slug,
                    'iso_alpha_2' => $c->iso_alpha_2,
                ])->values()->all(),
                'is_featured' => $v->is_featured,
                'thumbnail_url' => $v->thumbnail_url,
            ])
            ->all();

        return Response::json([
            'resource' => 'vehicles://fleet-catalog',
            'total_active_fleet' => count($vehicles),
            'fleet' => $vehicles,
        ]);
    }
}
