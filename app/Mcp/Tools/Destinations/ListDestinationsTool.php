<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Destinations;

use App\Mcp\Tools\Concerns\NormalizesMultilingualInput;
use App\Models\Country;
use App\Models\Destination;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('list_destinations')]
#[Title('List Destinations')]
#[Description('List travel destinations with search, country filters, active/featured status, locale selection, and pagination.')]
class ListDestinationsTool extends Tool
{
    use NormalizesMultilingualInput;

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'search' => $schema->string()->description('Optional search query to match destination name, description, or location.'),
            'country_id' => $schema->integer()->description('Optional filter by associated Country ID.'),
            'country_slug' => $schema->string()->description('Optional filter by associated Country slug (e.g. "indonesia", "saudi-arabia").'),
            'is_active' => $schema->boolean()->description('Filter by active status: true for active only, false for inactive only, or omit for all.'),
            'is_featured' => $schema->boolean()->description('Filter by featured status: true for featured only, false for non-featured only, or omit for all.'),
            'locale' => $schema->string()->description('Display language locale for returned name and description ("id", "en", or "ms"). Default is "id".'),
            'per_page' => $schema->integer()->description('Number of destinations per page (default: 15, max: 100).'),
            'page' => $schema->integer()->description('Page number for pagination (default: 1).'),
        ];
    }

    public function handle(Request $request): Response
    {
        $locale = (string) $request->get('locale', 'id');
        if (! in_array($locale, ['id', 'en', 'ms'], true)) {
            $locale = 'id';
        }

        $perPage = (int) $request->get('per_page', 15);
        $perPage = max(1, min(100, $perPage));
        $page = max(1, (int) $request->get('page', 1));

        $query = Destination::query()
            ->with(['countries'])
            ->withCount(['posts', 'tourPackages', 'umrahPackages'])
            ->latest('id');

        // Status active filter
        if ($request->has('is_active')) {
            $query->where('is_active', (bool) $request->get('is_active'));
        }

        // Status featured filter
        if ($request->has('is_featured')) {
            $query->where('is_featured', (bool) $request->get('is_featured'));
        }

        // Country filter by ID or slug
        $countryId = $request->get('country_id');
        $countrySlug = $request->get('country_slug');

        if (! empty($countryId)) {
            $query->whereHas('countries', fn (Builder $q) => $q->where('countries.id', (int) $countryId));
        } elseif (! empty($countrySlug)) {
            $country = Country::query()->where('slug', (string) $countrySlug)->first();
            if ($country) {
                $query->whereHas('countries', fn (Builder $q) => $q->where('countries.id', $country->id));
            } else {
                return Response::json([
                    'destinations' => [],
                    'pagination' => [
                        'total' => 0,
                        'per_page' => $perPage,
                        'current_page' => $page,
                        'last_page' => 1,
                        'has_more_pages' => false,
                    ],
                ]);
            }
        }

        // Search filter
        $search = trim((string) $request->get('search', ''));
        if ($search !== '') {
            $query->where(function (Builder $q) use ($search): void {
                $q->where('name->id', 'like', "%{$search}%")
                    ->orWhere('name->en', 'like', "%{$search}%")
                    ->orWhere('name->ms', 'like', "%{$search}%")
                    ->orWhere('description->id', 'like', "%{$search}%")
                    ->orWhere('description->en', 'like', "%{$search}%")
                    ->orWhere('description->ms', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        $destinations = collect($paginator->items())->map(function (Destination $destination) use ($locale): array {
            return [
                'id' => $destination->id,
                'name' => $destination->getTranslation('name', $locale, false) ?: $destination->getTranslation('name', 'id', false),
                'slug' => $destination->slug,
                'description' => $destination->getTranslation('description', $locale, false) ?: $destination->getTranslation('description', 'id', false),
                'location' => $destination->location,
                'map_url' => $destination->getSafeMapUrlAttribute(),
                'is_active' => $destination->is_active,
                'is_featured' => $destination->is_featured,
                'cover_url' => $destination->cover_url,
                'countries' => $destination->countries->map(fn (Country $c) => [
                    'id' => $c->id,
                    'name' => $c->getTranslation('name', $locale, false) ?: $c->getTranslation('name', 'id', false),
                    'slug' => $c->slug,
                    'iso_alpha_2' => $c->iso_alpha_2,
                ])->values()->all(),
                'connected_counts' => [
                    'articles' => $destination->posts_count,
                    'tour_packages' => $destination->tour_packages_count,
                    'umrah_packages' => $destination->umrah_packages_count,
                ],
                'created_at' => $destination->created_at?->toIso8601String(),
                'updated_at' => $destination->updated_at?->toIso8601String(),
            ];
        })->all();

        return Response::json([
            'destinations' => $destinations,
            'pagination' => [
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'has_more_pages' => $paginator->hasMorePages(),
            ],
        ]);
    }
}

