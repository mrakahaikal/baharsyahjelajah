<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Destinations;

use App\Models\Country;
use App\Models\Destination;
use App\Models\Post;
use App\Models\TourPackage;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('get_destination')]
#[Title('Get Destination')]
#[Description('Retrieve detailed destination information including translations, gallery photos, associated countries, and linked tours/articles by ID or slug.')]
class GetDestinationTool extends Tool
{
    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'id' => $schema->integer()->description('The numeric ID of the destination.'),
            'slug' => $schema->string()->description('The slug of the destination (used if "id" is omitted).'),
            'locale' => $schema->string()->description('Preferred display locale ("id", "en", or "ms"). Default is "id".'),
        ];
    }

    public function handle(Request $request): Response
    {
        $id = $request->get('id');
        $slug = $request->get('slug');

        if (empty($id) && empty($slug)) {
            return Response::error('Either "id" or "slug" must be provided.');
        }

        $query = Destination::query()
            ->with([
                'countries',
                'posts' => fn ($q) => $q->latest('id')->limit(5),
                'tourPackages' => fn ($q) => $q->latest('id')->limit(5),
            ]);

        /** @var Destination|null $destination */
        $destination = ! empty($id)
            ? $query->find((int) $id)
            : $query->where('slug', (string) $slug)->first();

        if (! $destination) {
            $identifier = ! empty($id) ? "ID {$id}" : "slug '{$slug}'";

            return Response::error("Destination with {$identifier} not found.");
        }

        $locale = (string) $request->get('locale', 'id');
        if (! in_array($locale, ['id', 'en', 'ms'], true)) {
            $locale = 'id';
        }

        return Response::json([
            'destination' => [
                'id' => $destination->id,
                'name' => $destination->getTranslation('name', $locale, false) ?: $destination->getTranslation('name', 'id', false),
                'slug' => $destination->slug,
                'description' => $destination->getTranslation('description', $locale, false) ?: $destination->getTranslation('description', 'id', false),
                'location' => $destination->location,
                'map_url' => $destination->getSafeMapUrlAttribute(),
                'is_active' => $destination->is_active,
                'is_featured' => $destination->is_featured,
                'cover_url' => $destination->cover_url,
                'gallery_urls' => $destination->gallery_urls->all(),
                'countries' => $destination->countries->map(fn (Country $c) => [
                    'id' => $c->id,
                    'name' => $c->getTranslation('name', $locale, false) ?: $c->getTranslation('name', 'id', false),
                    'slug' => $c->slug,
                    'iso_alpha_2' => $c->iso_alpha_2,
                ])->values()->all(),
                'connected_content' => [
                    'recent_articles' => $destination->posts->map(fn (Post $p) => [
                        'id' => $p->id,
                        'title' => $p->getTranslation('title', $locale, false) ?: $p->getTranslation('title', 'id', false),
                        'slug' => $p->localizedSlug($locale),
                        'status' => $p->status,
                    ])->values()->all(),
                    'recent_tours' => $destination->tourPackages->map(fn (TourPackage $tp) => [
                        'id' => $tp->id,
                        'name' => is_array($tp->name) ? ($tp->name[$locale] ?? $tp->name['id'] ?? '') : $tp->name,
                        'slug' => $tp->slug,
                    ])->values()->all(),
                ],
                'translations' => [
                    'name' => $destination->getTranslations('name'),
                    'description' => $destination->getTranslations('description'),
                ],
                'created_at' => $destination->created_at?->toIso8601String(),
                'updated_at' => $destination->updated_at?->toIso8601String(),
            ],
        ]);
    }
}

