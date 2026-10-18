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

#[Name('destination_catalog')]
#[Title('Katalog Destinasi Wisata')]
#[Description('Katalog terstruktur seluruh destinasi wisata aktif beserta terjemahan (id, en, ms), lokasi administratif, tautan peta, status unggulan, dan negara lokasi.')]
#[Uri('destinations://catalog')]
#[MimeType('application/json')]
class DestinationCatalogResource extends Resource
{
    /**
     * Handle the resource request.
     */
    public function handle(Request $request): Response
    {
        $destinations = Destination::query()
            ->active()
            ->with(['countries'])
            ->withCount(['posts', 'tourPackages', 'umrahPackages'])
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
                'is_featured' => $d->is_featured,
                'cover_url' => $d->cover_url,
                'countries' => $d->countries->map(fn (Country $c) => [
                    'id' => $c->id,
                    'name' => $c->getTranslation('name', 'id', false),
                    'slug' => $c->slug,
                    'iso_alpha_2' => $c->iso_alpha_2,
                ])->values()->all(),
                'connected_counts' => [
                    'articles' => $d->posts_count,
                    'tour_packages' => $d->tour_packages_count,
                    'umrah_packages' => $d->umrah_packages_count,
                ],
            ])
            ->all();

        return Response::json([
            'resource' => 'destinations://catalog',
            'total_active_destinations' => count($destinations),
            'destinations' => $destinations,
        ]);
    }
}
