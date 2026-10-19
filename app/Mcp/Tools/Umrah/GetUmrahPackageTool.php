<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Umrah;

use App\Models\Country;
use App\Models\Destination;
use App\Models\Testimonial;
use App\Models\UmrahDeparture;
use App\Models\UmrahInclude;
use App\Models\UmrahPackage;
use App\Models\UmrahPackageItinerary;
use App\Models\UmrahPackagePrice;
use App\Models\UmrahPackagePrivatePrice;
use App\Models\Vehicle;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('get_umrah_package')]
#[Title('Get Umrah Package Details')]
#[Description('Get comprehensive details of an Umrah package by ID or slug, including accommodations, departures, room prices, private prices, daily itinerary, inclusions, and visa requirements.')]
class GetUmrahPackageTool extends Tool
{
    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('Numeric ID of the Umrah package.'),
            'slug' => $schema->string()->description('URL slug of the Umrah package (in id, en, or ms). Used if id is not provided.'),
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

        $query = UmrahPackage::query()->with([
            'upcomingDepartures.prices.packagePrice',
            'prices',
            'privatePrices',
            'itineraries',
            'includes',
            'destinations',
            'vehicles',
            'countries',
            'testimonials',
        ]);

        $package = ! empty($id)
            ? $query->find((int) $id)
            : $query->whereLocalizedSlug((string) $slug)->first();

        if (! $package) {
            $identifier = ! empty($id) ? "ID {$id}" : "slug '{$slug}'";

            return Response::error("Umrah package with {$identifier} not found.");
        }

        return Response::json([
            'package' => [
                'id' => $package->id,
                'name' => $package->getTranslation('name', $locale, false) ?: $package->getTranslation('name', 'id', false),
                'slug' => $package->localizedSlug($locale),
                'description' => $package->getTranslation('description', $locale, false) ?: $package->getTranslation('description', 'id', false),
                'translations' => [
                    'name' => $package->getTranslations('name'),
                    'slug' => $package->getTranslations('slug'),
                    'description' => $package->getTranslations('description'),
                ],
                'package_type' => $package->package_type,
                'package_type_label' => $package->package_type_label,
                'duration_days' => (int) $package->duration_days,
                'price_idr' => (int) $package->price_idr,
                'starting_price_idr' => (int) $package->starting_price_idr,
                'formatted_price' => $package->formatted_price,
                'airline' => $package->airline,
                'hotel_makkah' => $package->hotel_makkah,
                'hotel_makkah_stars' => $package->hotel_makkah_stars,
                'hotel_madinah' => $package->hotel_madinah,
                'hotel_madinah_stars' => $package->hotel_madinah_stars,
                'visa_included' => (bool) $package->visa_included,
                'handling_included' => (bool) $package->handling_included,
                'thumbnail_url' => $package->thumbnail_url,
                'is_active' => (bool) $package->is_active,
                'is_featured' => (bool) $package->is_featured,
                'has_availability' => $package->has_availability,
                'room_prices' => $package->prices->map(fn (UmrahPackagePrice $p): array => [
                    'id' => $p->id,
                    'room_type' => $p->room_type,
                    'price_idr' => (int) $p->price_idr,
                    'formatted_price' => $p->formatted_price,
                ])->all(),
                'private_prices' => $package->privatePrices->map(fn (UmrahPackagePrivatePrice $pp): array => [
                    'id' => $pp->id,
                    'duration_nights' => (int) $pp->duration_nights,
                    'pax' => (int) $pp->pax,
                    'price_idr' => (int) $pp->price_idr,
                    'formatted_price' => $pp->formatted_price,
                ])->all(),
                'upcoming_departures' => $package->upcomingDepartures->map(fn (UmrahDeparture $dep): array => [
                    'id' => $dep->id,
                    'departure_date' => $dep->departure_date?->toDateString(),
                    'return_date' => $dep->return_date?->toDateString(),
                    'quota_total' => (int) $dep->quota_total,
                    'quota_booked' => (int) $dep->quota_booked,
                    'quota_sisa' => (int) $dep->quota_sisa,
                    'quota_percentage' => (int) $dep->quota_percentage,
                    'status' => $dep->status,
                    'price_override_idr' => $dep->price_override_idr ? (int) $dep->price_override_idr : null,
                ])->all(),
                'itineraries' => $package->itineraries->map(fn (UmrahPackageItinerary $it): array => [
                    'id' => $it->id,
                    'day_number' => (int) $it->day_number,
                    'title' => $it->getTranslation('title', $locale, false) ?: $it->getTranslation('title', 'id', false),
                    'title_translations' => $it->getTranslations('title'),
                    'location' => $it->getTranslation('location', $locale, false) ?: $it->getTranslation('location', 'id', false),
                    'location_translations' => $it->getTranslations('location'),
                    'description' => $it->getTranslation('description', $locale, false) ?: $it->getTranslation('description', 'id', false),
                    'description_translations' => $it->getTranslations('description'),
                ])->all(),
                'includes' => $package->includes->map(fn (UmrahInclude $inc): array => [
                    'id' => $inc->id,
                    'type' => $inc->type,
                    'item' => $inc->getTranslation('item', $locale, false) ?: $inc->getTranslation('item', 'id', false),
                    'item_translations' => $inc->getTranslations('item'),
                    'sort_order' => (int) $inc->sort_order,
                ])->all(),
                'destinations' => $package->destinations->map(fn (Destination $d): array => [
                    'id' => $d->id,
                    'name' => $d->getTranslation('name', $locale, false) ?: $d->getTranslation('name', 'id', false),
                    'slug' => $d->slug,
                ])->all(),
                'vehicles' => $package->vehicles->map(fn (Vehicle $v): array => [
                    'id' => $v->id,
                    'name' => $v->getTranslation('name', $locale, false) ?: $v->getTranslation('name', 'id', false),
                    'capacity_pax' => $v->capacity_pax,
                ])->all(),
                'countries' => $package->countries->map(fn (Country $c): array => [
                    'id' => $c->id,
                    'name' => $c->getTranslation('name', $locale, false) ?: $c->getTranslation('name', 'id', false),
                    'code' => $c->code,
                ])->all(),
                'testimonials' => $package->testimonials->map(fn (Testimonial $t): array => [
                    'id' => $t->id,
                    'customer_name' => $t->customer_name,
                    'rating' => $t->rating,
                    'review' => $t->getTranslation('review', $locale, false) ?: $t->getTranslation('review', 'id', false),
                ])->all(),
                'created_at' => $package->created_at?->toIso8601String(),
                'updated_at' => $package->updated_at?->toIso8601String(),
            ],
            'locale' => $locale,
        ]);
    }
}
