<?php

declare(strict_types=1);

namespace App\Mcp\Resources;

use App\Models\Country;
use App\Models\Destination;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\MimeType;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Attributes\Uri;
use Laravel\Mcp\Server\Resource;

#[Name('featured_destinations')]
#[Title('Destinasi Wisata Unggulan')]
#[Description('Daftar destinasi wisata unggulan (is_featured = true) yang aktif untuk rekomendasi promosi utama dan perencanaan tur.')]
#[Uri('destinations://featured')]
#[MimeType('application/json')]
class FeaturedDestinationsResource extends Resource
{
    /**
     * Handle the resource request.
     */
    public function handle(Request $request): Response
    {
        $destinations = Destination::query()
            ->active()
            ->featured()
            ->with(['countries'])
            ->withCount(['posts', 'tourPackages'])
            ->orderBy('id')
            ->get()
            ->map(fn (Destination $d): array => [
                'id' => $d->id,
                'name' => [
                    'id' => $d->getTranslation('name', 'id', false),
                    'en' => $d->getTranslation('name', 'en', false),
                    'ms' => $d->getTranslation('name', 'ms', false),
                ],
                'slug' => $d->slug,
                'description' => [
                    'id' => $d->getTranslation('description', 'id', false),
                    'en' => $d->getTranslation('description', 'en', false),
                    'ms' => $d->getTranslation('description', 'ms', false),
                ],
                'location' => $d->location,
                'map_url' => $d->getSafeMapUrlAttribute(),
                'cover_url' => $d->cover_url,
                'countries' => $d->countries->map(fn (Country $c) => [
                    'id' => $c->id,
                    'name' => $c->getTranslation('name', 'id', false),
                    'slug' => $c->slug,
                ])->values()->all(),
                'connected_counts' => [
                    'articles' => $d->posts_count,
                    'tour_packages' => $d->tour_packages_count,
                ],
            ])
            ->all();

        return Response::json([
            'resource' => 'destinations://featured',
            'featured_count' => count($destinations),
            'featured_destinations' => $destinations,
        ]);
    }
}
