<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Tours\Packages;

use App\Models\Country;
use App\Models\Destination;
use App\Models\PackageTier;
use App\Models\TourPackage;
use App\Models\TourPackageInclude;
use App\Models\TourPackageItinerary;
use App\Models\TourPriceTier;
use App\Models\Vehicle;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('get_tour_package')]
#[Title('Get Tour Package Details')]
#[Description('Get complete details of a specific tour package by ID or slug, including parent tour, destinations, vehicles, tiers & pricing matrix, daily itinerary, and inclusions/exclusions.')]
class GetTourPackageTool extends Tool
{
    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('Numeric ID of the tour package.'),
            'slug' => $schema->string()->description('URL slug of the tour package (in id, en, or ms). Used if id is not provided.'),
            'locale' => $schema->string()->description('Preferred output locale ("id", "en", "ms"). Default: "id".'),
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

        $query = TourPackage::query()->with([
            'tour.category',
            'destinations',
            'countries',
            'vehicles',
            'tiers.priceTiers',
            'itineraries.destinations',
            'includes',
        ]);

        $package = ! empty($id)
            ? $query->find((int) $id)
            : $query->whereLocalizedSlug((string) $slug)->first();

        if (! $package) {
            $identifier = ! empty($id) ? "ID {$id}" : "slug '{$slug}'";

            return Response::error("Tour package with {$identifier} not found.");
        }

        $startingPrice = $package->startingPriceTier();

        return Response::json([
            'package' => [
                'id' => $package->id,
                'tour_id' => $package->tour_id,
                'tour' => $package->tour ? [
                    'id' => $package->tour->id,
                    'name' => $package->tour->getTranslation('name', $locale, false) ?: $package->tour->getTranslation('name', 'id', false),
                    'slug' => $package->tour->localizedSlug($locale),
                    'tour_type' => $package->tour->tour_type?->value,
                    'currency' => $package->tour->currency,
                ] : null,
                'name' => $package->getTranslation('name', $locale, false) ?: $package->getTranslation('name', 'id', false),
                'slug' => $package->localizedSlug($locale),
                'duration_days' => (int) $package->duration_days,
                'duration_nights' => (int) $package->duration_nights,
                'duration_label' => $package->duration_label,
                'cover_url' => $package->cover_url,
                'translations' => [
                    'name' => $package->getTranslations('name'),
                    'slug' => $package->getTranslations('slug'),
                ],
                'starting_price' => $startingPrice ? [
                    'amount' => (float) $startingPrice->price,
                    'currency' => $startingPrice->currency,
                    'min_pax' => $startingPrice->min_pax,
                    'max_pax' => $startingPrice->max_pax,
                ] : null,
                'destinations' => $package->destinations->map(fn (Destination $d): array => [
                    'id' => $d->id,
                    'name' => $d->getTranslation('name', $locale, false) ?: $d->getTranslation('name', 'id', false),
                    'slug' => $d->slug,
                ])->all(),
                'countries' => $package->countries->map(fn (Country $c): array => [
                    'id' => $c->id,
                    'name' => $c->getTranslation('name', $locale, false) ?: $c->getTranslation('name', 'id', false),
                    'code' => $c->code,
                ])->all(),
                'vehicles' => $package->vehicles->map(fn (Vehicle $v): array => [
                    'id' => $v->id,
                    'name' => $v->getTranslation('name', $locale, false) ?: $v->getTranslation('name', 'id', false),
                    'capacity_pax' => $v->capacity_pax,
                ])->all(),
                'tiers' => $package->tiers->map(fn (PackageTier $tier): array => [
                    'id' => $tier->id,
                    'name' => $tier->getTranslation('name', $locale, false) ?: $tier->getTranslation('name', 'id', false),
                    'name_translations' => $tier->getTranslations('name'),
                    'hotel_stars' => $tier->hotel_stars,
                    'price_tiers' => $tier->priceTiers->map(fn (TourPriceTier $pt): array => [
                        'id' => $pt->id,
                        'min_pax' => $pt->min_pax,
                        'max_pax' => $pt->max_pax,
                        'price' => (float) $pt->price,
                        'currency' => $pt->currency,
                    ])->all(),
                ])->all(),
                'itineraries' => $package->itineraries->map(fn (TourPackageItinerary $it): array => [
                    'id' => $it->id,
                    'day_number' => $it->day_number,
                    'title' => $it->getTranslation('title', $locale, false) ?: $it->getTranslation('title', 'id', false),
                    'title_translations' => $it->getTranslations('title'),
                    'description' => $it->getTranslation('description', $locale, false) ?: $it->getTranslation('description', 'id', false),
                    'description_translations' => $it->getTranslations('description'),
                    'destinations' => $it->destinations->map(fn (Destination $d): array => [
                        'id' => $d->id,
                        'name' => $d->getTranslation('name', $locale, false) ?: $d->getTranslation('name', 'id', false),
                    ])->all(),
                ])->all(),
                'includes' => $package->includes->map(fn (TourPackageInclude $inc): array => [
                    'id' => $inc->id,
                    'type' => $inc->type,
                    'item' => $inc->getTranslation('item', $locale, false) ?: $inc->getTranslation('item', 'id', false),
                    'item_translations' => $inc->getTranslations('item'),
                    'sort_order' => $inc->sort_order,
                ])->all(),
                'created_at' => $package->created_at?->toIso8601String(),
                'updated_at' => $package->updated_at?->toIso8601String(),
            ],
            'locale' => $locale,
        ]);
    }
}
