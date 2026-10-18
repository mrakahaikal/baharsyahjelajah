<?php

namespace App\Mcp\Resources;

use App\Models\VehicleRentalArea;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Resource;

class VehicleRentalAreasResource extends Resource
{
    protected string $uri = 'vehicles://rental-areas';

    protected string $name = 'Daftar Wilayah Sewa Kendaraan (Rental Areas)';

    protected string $description = 'Snapshot seluruh wilayah operasional sewa kendaraan yang aktif beserta minimal hari sewa dan jumlah armada aktif.';

    protected string $mimeType = 'application/json';

    public function handle(Request $request): Response
    {
        $areas = VehicleRentalArea::query()
            ->active()
            ->withCount(['rates as active_rates_count' => function ($query): void {
                $query->where('is_active', true);
            }])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(function (VehicleRentalArea $area): array {
                $activeVehiclesCount = $area->rates()
                    ->where('is_active', true)
                    ->distinct('vehicle_id')
                    ->count('vehicle_id');

                return [
                    'id' => $area->id,
                    'slug' => $area->slug,
                    'name' => [
                        'id' => $area->getTranslation('name', 'id'),
                        'en' => $area->getTranslation('name', 'en'),
                        'ms' => $area->getTranslation('name', 'ms'),
                    ],
                    'description' => [
                        'id' => $area->getTranslation('description', 'id'),
                        'en' => $area->getTranslation('description', 'en'),
                        'ms' => $area->getTranslation('description', 'ms'),
                    ],
                    'minimum_rental_days' => $area->minimum_rental_days,
                    'sort_order' => $area->sort_order,
                    'active_rates_count' => $area->active_rates_count,
                    'active_vehicles_count' => $activeVehiclesCount,
                ];
            })
            ->all();

        return Response::json([
            'total_active_areas' => count($areas),
            'areas' => $areas,
        ]);
    }
}
