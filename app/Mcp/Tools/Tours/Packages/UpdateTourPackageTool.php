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
use Illuminate\Support\Str;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('update_tour_package')]
#[Title('Update Tour Package')]
#[Description('Update an existing tour package by ID, including name, slug, duration days/nights, and destination, country, or vehicle associations.')]
class UpdateTourPackageTool extends Tool
{
    use NormalizesMultilingualInput;

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('Numeric ID of the tour package to update.')->required(),
            'tour_id' => $schema->integer()->description('Updated parent tour ID.'),
            'name' => $schema->union(['string', 'object'])->description('Updated package name (plain string or multilingual map).'),
            'slug' => $schema->union(['string', 'object'])->description('Updated URL slug (plain string or multilingual map).'),
            'duration_days' => $schema->integer()->description('Updated duration in days (min: 1).'),
            'duration_nights' => $schema->integer()->description('Updated duration in nights (min: 0).'),
            'destination_ids' => $schema->array()->description('Updated array of numeric destination IDs to sync.'),
            'country_ids' => $schema->array()->description('Updated array of numeric country IDs to sync.'),
            'vehicle_ids' => $schema->array()->description('Updated array of numeric vehicle IDs to sync.'),
        ];
    }

    public function handle(Request $request): Response
    {
        $id = $request->get('id');
        if (empty($id)) {
            return Response::error('The "id" parameter is required.');
        }

        $package = TourPackage::find((int) $id);
        if (! $package) {
            return Response::error("Tour package with ID {$id} not found.");
        }

        if ($request->filled('tour_id')) {
            $tourId = (int) $request->get('tour_id');
            if (! Tour::whereKey($tourId)->exists()) {
                return Response::error("Parent tour with ID {$tourId} not found.");
            }
            $package->tour_id = $tourId;
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

        if ($request->filled('duration_days')) {
            $durationDays = (int) $request->get('duration_days');
            if ($durationDays < 1) {
                return Response::error('The "duration_days" parameter must be at least 1.');
            }
            $package->duration_days = $durationDays;
        }

        if ($request->has('duration_nights')) {
            $package->duration_nights = max(0, (int) $request->get('duration_nights'));
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
            'message' => 'Paket tur berhasil diperbarui.',
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
                'updated_at' => $package->updated_at?->toIso8601String(),
            ],
        ]);
    }
}
