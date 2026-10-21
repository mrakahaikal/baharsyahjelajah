<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Tours\Packages;

use App\Mcp\Tools\Concerns\NormalizesMultilingualInput;
use App\Models\Country;
use App\Models\Destination;
use App\Models\Tour;
use App\Models\TourPackage;
use App\Models\Vehicle;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('create_tour_package')]
#[Title('Create Tour Package')]
#[Description('Create a new package variant under a specific tour, including duration in days/nights and optional destination, country, and vehicle associations.')]
class CreateTourPackageTool extends Tool
{
    use NormalizesMultilingualInput;

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'tour_id' => $schema->integer()->description('ID of the parent tour.')->required(),
            'name' => $schema->union(['string', 'object'])->description('Package name (plain string or multilingual map {"id": "...", "en": "...", "ms": "..."}). Required.'),
            'slug' => $schema->union(['string', 'object'])->description('Optional custom URL slug (plain string or multilingual map). Auto-generated from name if omitted.'),
            'duration_days' => $schema->integer()->description('Total duration in days (min: 1).')->required(),
            'duration_nights' => $schema->integer()->description('Total duration in nights (min: 0, default: duration_days - 1).'),
            'destination_ids' => $schema->array()->description('Optional array of numeric destination IDs included in this package.'),
            'country_ids' => $schema->array()->description('Optional array of numeric country IDs visited in this package.'),
            'vehicle_ids' => $schema->array()->description('Optional array of numeric vehicle IDs used in this package.'),
        ];
    }

    public function handle(Request $request): Response
    {
        $tourId = $request->get('tour_id');
        if (empty($tourId)) {
            return Response::error('The "tour_id" parameter is required.');
        }

        $tour = Tour::find((int) $tourId);
        if (! $tour) {
            return Response::error("Parent tour with ID {$tourId} not found.");
        }

        $rawName = $request->get('name');
        if (empty($rawName)) {
            return Response::error('The "name" parameter is required.');
        }

        $name = $this->normalizeMultilingualInput($rawName);
        if (empty($name['id'])) {
            return Response::error('Package name for Indonesian (id) cannot be empty.');
        }

        $durationDays = (int) $request->get('duration_days', 0);
        if ($durationDays < 1) {
            return Response::error('The "duration_days" parameter is required and must be at least 1.');
        }

        $durationNights = $request->has('duration_nights')
            ? max(0, (int) $request->get('duration_nights'))
            : max(0, $durationDays - 1);

        $slug = $this->resolveSlugTranslations($name, $request->get('slug'));

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

        $package = TourPackage::create([
            'tour_id' => $tour->id,
            'name' => $name,
            'slug' => $slug,
            'duration_days' => $durationDays,
            'duration_nights' => $durationNights,
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
            'message' => 'Paket tur berhasil dibuat.',
            'package' => [
                'id' => $package->id,
                'tour_id' => $package->tour_id,
                'name' => $package->getTranslations('name'),
                'slug' => $package->getTranslations('slug'),
                'duration_days' => (int) $package->duration_days,
                'duration_nights' => (int) $package->duration_nights,
                'duration_label' => $package->duration_label,
                'destinations' => $package->destinations->map(fn (Destination $d): array => [
                    'id' => $d->id,
                    'name' => $d->getTranslation('name', 'id', false),
                ])->all(),
                'countries' => $package->countries->map(fn (Country $c): array => [
                    'id' => $c->id,
                    'name' => $c->getTranslation('name', 'id', false),
                ])->all(),
                'vehicles' => $package->vehicles->map(fn (Vehicle $v): array => [
                    'id' => $v->id,
                    'name' => $v->getTranslation('name', 'id', false),
                ])->all(),
                'created_at' => $package->created_at?->toIso8601String(),
            ],
        ]);
    }
}
