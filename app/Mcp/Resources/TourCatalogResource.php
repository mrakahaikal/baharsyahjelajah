<?php

declare(strict_types=1);

namespace App\Mcp\Resources;

use App\Models\Country;
use App\Models\PackageTier;
use App\Models\Tour;
use App\Models\TourPackage;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\MimeType;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Attributes\Uri;
use Laravel\Mcp\Server\Resource;

#[Name('tour_catalog')]
#[Title('Katalog Paket Wisata (Tour Catalog)')]
#[Description('Katalog terstruktur seluruh tur wisata aktif beserta kategori, negara tujuan, varian durasi paket, tier hotel, dan harga awal per pax.')]
#[Uri('tours://catalog')]
#[MimeType('application/json')]
class TourCatalogResource extends Resource
{
    public function handle(Request $request): Response
    {
        $tours = Tour::query()
            ->active()
            ->with([
                'category',
                'countries',
                'packages.tiers.priceTiers',
                'packages.destinations',
            ])
            ->withCount('testimonials')
            ->withAvg('testimonials', 'rating')
            ->orderByDesc('is_featured')
            ->orderBy('id')
            ->get()
            ->map(function (Tour $tour): array {
                return [
                    'id' => $tour->id,
                    'name' => $tour->getTranslations('name'),
                    'slug' => $tour->getTranslations('slug'),
                    'short_description' => $tour->getTranslations('short_description'),
                    'tour_type' => $tour->tour_type?->value,
                    'tour_type_label' => $tour->tour_type?->getLabel(),
                    'currency' => $tour->currency,
                    'is_featured' => (bool) $tour->is_featured,
                    'category' => $tour->category ? [
                        'id' => $tour->category->id,
                        'name' => $tour->category->getTranslation('name', 'id', false),
                        'slug' => $tour->category->localizedSlug('id'),
                    ] : null,
                    'countries' => $tour->countries->map(fn (Country $c): array => [
                        'id' => $c->id,
                        'name' => $c->getTranslation('name', 'id', false),
                        'code' => $c->code,
                    ])->all(),
                    'average_rating' => round((float) ($tour->testimonials_avg_rating ?? 0), 1),
                    'testimonials_count' => (int) ($tour->testimonials_count ?? 0),
                    'packages' => $tour->packages->map(function (TourPackage $pkg): array {
                        $startingPrice = $pkg->startingPriceTier();

                        return [
                            'id' => $pkg->id,
                            'name' => $pkg->getTranslations('name'),
                            'slug' => $pkg->getTranslations('slug'),
                            'duration_days' => (int) $pkg->duration_days,
                            'duration_nights' => (int) $pkg->duration_nights,
                            'duration_label' => $pkg->duration_label,
                            'starting_price' => $startingPrice ? [
                                'amount' => (float) $startingPrice->price,
                                'currency' => $startingPrice->currency,
                                'min_pax' => $startingPrice->min_pax,
                                'max_pax' => $startingPrice->max_pax,
                            ] : null,
                            'tiers' => $pkg->tiers->map(fn (PackageTier $tier): array => [
                                'id' => $tier->id,
                                'name' => $tier->getTranslation('name', 'id', false),
                                'hotel_stars' => $tier->hotel_stars,
                            ])->all(),
                        ];
                    })->all(),
                ];
            })
            ->all();

        return Response::json([
            'resource' => 'tours://catalog',
            'total_active_tours' => count($tours),
            'tours' => $tours,
        ]);
    }
}
