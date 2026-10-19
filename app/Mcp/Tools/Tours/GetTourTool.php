<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Tours;

use App\Models\Country;
use App\Models\Destination;
use App\Models\PackageTier;
use App\Models\Testimonial;
use App\Models\Tour;
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

#[Name('get_tour')]
#[Title('Get Tour Details')]
#[Description('Get comprehensive details of a tour by ID or slug, including its category, countries, packages, tiers, pricing matrix, itineraries, inclusions, and testimonials.')]
class GetTourTool extends Tool
{
    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('Numeric ID of the tour.'),
            'slug' => $schema->string()->description('URL slug of the tour (in id, en, or ms). Used if id is not provided.'),
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

        $query = Tour::query()->with([
            'category',
            'countries',
            'packages.destinations',
            'packages.vehicles',
            'packages.tiers.priceTiers',
            'packages.itineraries.destinations',
            'packages.includes',
            'testimonials',
        ]);

        $tour = ! empty($id)
            ? $query->find((int) $id)
            : $query->whereLocalizedSlug((string) $slug)->first();

        if (! $tour) {
            $identifier = ! empty($id) ? "ID {$id}" : "slug '{$slug}'";

            return Response::error("Tour with {$identifier} not found.");
        }

        $packages = $tour->packages->map(function (TourPackage $pkg) use ($locale): array {
            $startingPrice = $pkg->startingPriceTier();

            return [
                'id' => $pkg->id,
                'name' => $pkg->getTranslation('name', $locale, false) ?: $pkg->getTranslation('name', 'id', false),
                'slug' => $pkg->localizedSlug($locale),
                'duration_days' => (int) $pkg->duration_days,
                'duration_nights' => (int) $pkg->duration_nights,
                'duration_label' => $pkg->duration_label,
                'cover_url' => $pkg->cover_url,
                'starting_price' => $startingPrice ? [
                    'amount' => (float) $startingPrice->price,
                    'currency' => $startingPrice->currency,
                    'min_pax' => $startingPrice->min_pax,
                    'max_pax' => $startingPrice->max_pax,
                ] : null,
                'destinations' => $pkg->destinations->map(fn (Destination $d): array => [
                    'id' => $d->id,
                    'name' => $d->getTranslation('name', $locale, false) ?: $d->getTranslation('name', 'id', false),
                    'slug' => $d->slug,
                ])->all(),
                'vehicles' => $pkg->vehicles->map(fn (Vehicle $v): array => [
                    'id' => $v->id,
                    'name' => $v->getTranslation('name', $locale, false) ?: $v->getTranslation('name', 'id', false),
                    'capacity_pax' => $v->capacity_pax,
                ])->all(),
                'tiers' => $pkg->tiers->map(fn (PackageTier $tier): array => [
                    'id' => $tier->id,
                    'name' => $tier->getTranslation('name', $locale, false) ?: $tier->getTranslation('name', 'id', false),
                    'hotel_stars' => $tier->hotel_stars,
                    'price_tiers' => $tier->priceTiers->map(fn (TourPriceTier $pt): array => [
                        'id' => $pt->id,
                        'min_pax' => $pt->min_pax,
                        'max_pax' => $pt->max_pax,
                        'price' => (float) $pt->price,
                        'currency' => $pt->currency,
                    ])->all(),
                ])->all(),
                'itineraries' => $pkg->itineraries->map(fn (TourPackageItinerary $it): array => [
                    'id' => $it->id,
                    'day_number' => $it->day_number,
                    'title' => $it->getTranslation('title', $locale, false) ?: $it->getTranslation('title', 'id', false),
                    'description' => $it->getTranslation('description', $locale, false) ?: $it->getTranslation('description', 'id', false),
                    'destinations' => $it->destinations->map(fn (Destination $d): array => [
                        'id' => $d->id,
                        'name' => $d->getTranslation('name', $locale, false) ?: $d->getTranslation('name', 'id', false),
                    ])->all(),
                ])->all(),
                'includes' => $pkg->includes->map(fn (TourPackageInclude $inc): array => [
                    'id' => $inc->id,
                    'type' => $inc->type,
                    'item' => $inc->getTranslation('item', $locale, false) ?: $inc->getTranslation('item', 'id', false),
                    'sort_order' => $inc->sort_order,
                ])->all(),
            ];
        })->all();

        $testimonials = $tour->testimonials->map(fn (Testimonial $t): array => [
            'id' => $t->id,
            'customer_name' => $t->customer_name,
            'customer_origin' => $t->customer_origin,
            'rating' => $t->rating,
            'review' => $t->getTranslation('review', $locale, false) ?: $t->getTranslation('review', 'id', false),
        ])->all();

        return Response::json([
            'tour' => [
                'id' => $tour->id,
                'name' => $tour->getTranslation('name', $locale, false) ?: $tour->getTranslation('name', 'id', false),
                'slug' => $tour->localizedSlug($locale),
                'short_description' => $tour->getTranslation('short_description', $locale, false) ?: $tour->getTranslation('short_description', 'id', false),
                'description' => $tour->getTranslation('description', $locale, false) ?: $tour->getTranslation('description', 'id', false),
                'tour_type' => $tour->tour_type?->value,
                'tour_type_label' => $tour->tour_type?->getLabel(),
                'currency' => $tour->currency,
                'is_active' => (bool) $tour->is_active,
                'is_featured' => (bool) $tour->is_featured,
                'category' => $tour->category ? [
                    'id' => $tour->category->id,
                    'name' => $tour->category->getTranslation('name', $locale, false) ?: $tour->category->getTranslation('name', 'id', false),
                    'slug' => $tour->category->localizedSlug($locale),
                ] : null,
                'countries' => $tour->countries->map(fn (Country $c): array => [
                    'id' => $c->id,
                    'name' => $c->getTranslation('name', $locale, false) ?: $c->getTranslation('name', 'id', false),
                    'code' => $c->code,
                ])->all(),
                'translations' => [
                    'name' => $tour->getTranslations('name'),
                    'slug' => $tour->getTranslations('slug'),
                    'short_description' => $tour->getTranslations('short_description'),
                    'description' => $tour->getTranslations('description'),
                ],
                'packages' => $packages,
                'average_rating' => $tour->average_rating,
                'testimonials' => $testimonials,
                'created_at' => $tour->created_at?->toIso8601String(),
                'updated_at' => $tour->updated_at?->toIso8601String(),
            ],
            'locale' => $locale,
        ]);
    }
}
