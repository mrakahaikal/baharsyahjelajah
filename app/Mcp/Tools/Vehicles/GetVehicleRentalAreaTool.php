<?php

namespace App\Mcp\Tools\Vehicles;

use App\Mcp\Tools\Concerns\NormalizesMultilingualInput;
use App\Models\VehicleRentalArea;
use App\Models\VehicleRentalRate;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('get_vehicle_rental_area')]
#[Title('Get Vehicle Rental Area Details')]
#[Description('Get comprehensive vehicle rental area details by ID or Slug, including multilingual translations, minimum rental days, and active fleet rates.')]
class GetVehicleRentalAreaTool extends Tool
{
    use NormalizesMultilingualInput;

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('Vehicle rental area numeric ID.'),
            'slug' => $schema->string()->description('Vehicle rental area unique URL slug.'),
            'locale' => $schema->string()->description('Preferred output locale ("id", "en", "ms"). Default is "id".'),
        ];
    }

    public function handle(Request $request): Response
    {
        $id = $request->get('id');
        $slug = $request->get('slug');
        $locale = (string) $request->get('locale', 'id');

        if (empty($id) && empty($slug)) {
            return Response::error('Either "id" or "slug" must be provided.');
        }

        $area = VehicleRentalArea::query()
            ->when(! empty($id), fn ($q) => $q->where('id', (int) $id))
            ->when(! empty($slug), fn ($q) => $q->where('slug', (string) $slug))
            ->first();

        if (! $area) {
            $identifier = ! empty($id) ? "ID {$id}" : "slug '{$slug}'";

            return Response::error("Vehicle rental area with {$identifier} not found.");
        }

        $activeRates = $area->rates()
            ->with(['vehicle'])
            ->where('is_active', true)
            ->get()
            ->map(function (VehicleRentalRate $rate) use ($locale): array {
                $vehicle = $rate->vehicle;

                return [
                    'rate_id' => $rate->id,
                    'price_per_day_idr' => $rate->price_per_day_idr,
                    'formatted_price' => 'Rp '.number_format($rate->price_per_day_idr, 0, ',', '.'),
                    'valid_from' => $rate->valid_from?->toDateString(),
                    'valid_until' => $rate->valid_until?->toDateString(),
                    'vehicle' => $vehicle ? [
                        'id' => $vehicle->id,
                        'catalog_code' => $vehicle->catalog_code,
                        'name' => $vehicle->getTranslation('name', $locale, false) ?: $vehicle->getTranslation('name', 'id', false),
                        'slug' => $vehicle->getTranslation('slug', $locale, false) ?: $vehicle->getTranslation('slug', 'id', false),
                        'brand' => $vehicle->brand,
                        'model' => $vehicle->model,
                        'category' => $vehicle->category?->value,
                        'capacity_pax' => $vehicle->capacity_pax,
                        'capacity_luggage' => $vehicle->capacity_luggage,
                        'transmission' => $vehicle->transmission,
                        'is_active' => (bool) $vehicle->is_active,
                    ] : null,
                ];
            })->all();

        return Response::json([
            'area' => [
                'id' => $area->id,
                'slug' => $area->slug,
                'name' => $area->getTranslation('name', $locale, false) ?: $area->getTranslation('name', 'id', false),
                'name_translations' => $area->getTranslations('name'),
                'description' => $area->getTranslation('description', $locale, false) ?: $area->getTranslation('description', 'id', false),
                'description_translations' => $area->getTranslations('description'),
                'minimum_rental_days' => $area->minimum_rental_days,
                'is_active' => (bool) $area->is_active,
                'sort_order' => $area->sort_order,
                'created_at' => $area->created_at?->toIso8601String(),
                'updated_at' => $area->updated_at?->toIso8601String(),
            ],
            'active_rates' => $activeRates,
            'total_active_rates' => count($activeRates),
            'locale' => $locale,
        ]);
    }
}
