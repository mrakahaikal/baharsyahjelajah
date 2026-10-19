<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Tours;

use App\Enums\TourType;
use App\Models\Country;
use App\Models\Tour;
use App\Models\TourPackage;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('list_tours')]
#[Title('List Tours')]
#[Description('List and filter tours by category, type (domestic/international), country, destination, active/featured status, and search query, including starting prices and ratings.')]
class ListToursTool extends Tool
{
    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'tour_category_id' => $schema->integer()->description('Filter by tour category ID.'),
            'tour_type' => $schema->string()->description('Filter by tour type: "domestic" or "international".'),
            'country_id' => $schema->integer()->description('Filter by associated country ID.'),
            'destination_id' => $schema->integer()->description('Filter by destination ID visited in any of the tour packages.'),
            'is_active' => $schema->boolean()->description('Filter by active status (true/false).'),
            'is_featured' => $schema->boolean()->description('Filter by featured status (true/false).'),
            'currency' => $schema->string()->description('Filter by base currency code (e.g., "IDR", "MYR", "USD").'),
            'search' => $schema->string()->description('Search keyword matching tour name, slug, or short description across languages.'),
            'locale' => $schema->string()->description('Preferred output locale ("id", "en", "ms"). Default: "id".'),
            'per_page' => $schema->integer()->description('Number of results per page (1-50, default: 20).'),
            'page' => $schema->integer()->description('Page number for pagination (default: 1).'),
        ];
    }

    public function handle(Request $request): Response
    {
        $locale = (string) $request->get('locale', 'id');
        $perPage = min(50, max(1, (int) $request->get('per_page', 20)));
        $page = max(1, (int) $request->get('page', 1));

        $query = Tour::query()
            ->with([
                'category',
                'countries',
                'packages.tiers.priceTiers',
            ])
            ->withCount(['packages', 'testimonials'])
            ->withAvg('testimonials', 'rating');

        if ($request->filled('tour_category_id')) {
            $query->where('tour_category_id', (int) $request->get('tour_category_id'));
        }

        if ($request->filled('tour_type')) {
            $type = (string) $request->get('tour_type');
            $enumType = TourType::tryFrom($type);
            $query->where('tour_type', $enumType?->value ?? $type);
        }

        if ($request->filled('country_id')) {
            $countryId = (int) $request->get('country_id');
            $query->whereHas('countries', fn (Builder $q) => $q->where('countries.id', $countryId));
        }

        if ($request->filled('destination_id')) {
            $destinationId = (int) $request->get('destination_id');
            $query->whereHas('packages.destinations', fn (Builder $q) => $q->where('destinations.id', $destinationId));
        }

        if ($request->has('is_active')) {
            $query->where('is_active', (bool) $request->get('is_active'));
        }

        if ($request->has('is_featured')) {
            $query->where('is_featured', (bool) $request->get('is_featured'));
        }

        if ($request->filled('currency')) {
            $query->where('currency', strtoupper(trim((string) $request->get('currency'))));
        }

        if ($request->filled('search')) {
            $search = (string) $request->get('search');
            $query->where(function (Builder $sub) use ($search): void {
                $sub->where('name->id', 'like', "%{$search}%")
                    ->orWhere('name->en', 'like', "%{$search}%")
                    ->orWhere('name->ms', 'like', "%{$search}%")
                    ->orWhere('slug->id', 'like', "%{$search}%")
                    ->orWhere('slug->en', 'like', "%{$search}%")
                    ->orWhere('slug->ms', 'like', "%{$search}%")
                    ->orWhere('short_description->id', 'like', "%{$search}%")
                    ->orWhere('short_description->en', 'like', "%{$search}%")
                    ->orWhere('short_description->ms', 'like', "%{$search}%");
            });
        }

        $paginator = $query->orderByDesc('is_featured')->orderByDesc('id')->paginate(perPage: $perPage, page: $page);

        $tours = collect($paginator->items())->map(function (Tour $tour) use ($locale): array {
            $startingPriceTier = $tour->packages
                ->map(fn (TourPackage $pkg) => $pkg->startingPriceTier())
                ->filter()
                ->sortBy(fn ($pt) => (float) $pt->price)
                ->first();

            return [
                'id' => $tour->id,
                'name' => $tour->getTranslation('name', $locale, false) ?: $tour->getTranslation('name', 'id', false),
                'slug' => $tour->localizedSlug($locale),
                'short_description' => $tour->getTranslation('short_description', $locale, false) ?: $tour->getTranslation('short_description', 'id', false),
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
                'packages_count' => (int) ($tour->packages_count ?? 0),
                'starting_price' => $startingPriceTier ? [
                    'amount' => (float) $startingPriceTier->price,
                    'currency' => $startingPriceTier->currency,
                    'min_pax' => $startingPriceTier->min_pax,
                    'max_pax' => $startingPriceTier->max_pax,
                ] : null,
                'average_rating' => round((float) ($tour->testimonials_avg_rating ?? 0), 1),
                'testimonials_count' => (int) ($tour->testimonials_count ?? 0),
                'created_at' => $tour->created_at?->toIso8601String(),
                'updated_at' => $tour->updated_at?->toIso8601String(),
            ];
        })->all();

        return Response::json([
            'tours' => $tours,
            'pagination' => [
                'total' => $paginator->total(),
                'count' => count($tours),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'total_pages' => $paginator->lastPage(),
            ],
            'locale' => $locale,
        ]);
    }
}
