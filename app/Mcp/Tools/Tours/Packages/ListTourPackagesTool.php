<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Tours\Packages;

use App\Models\Destination;
use App\Models\PackageTier;
use App\Models\TourPackage;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('list_tour_packages')]
#[Title('List Tour Packages')]
#[Description('List and filter tour package variants by parent tour ID, duration days/nights, destination, and search query, with starting price summaries.')]
class ListTourPackagesTool extends Tool
{
    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'tour_id' => $schema->integer()->description('Filter by parent tour ID.'),
            'duration_days' => $schema->integer()->description('Filter by exact duration in days.'),
            'duration_nights' => $schema->integer()->description('Filter by exact duration in nights.'),
            'destination_id' => $schema->integer()->description('Filter by destination ID visited in the package.'),
            'search' => $schema->string()->description('Search keyword matching package name or slug across languages.'),
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

        $query = TourPackage::query()
            ->with([
                'tour.category',
                'destinations',
                'tiers.priceTiers',
            ])
            ->withCount(['itineraries', 'includes', 'tiers']);

        if ($request->filled('tour_id')) {
            $query->where('tour_id', (int) $request->get('tour_id'));
        }

        if ($request->filled('duration_days')) {
            $query->where('duration_days', (int) $request->get('duration_days'));
        }

        if ($request->filled('duration_nights')) {
            $query->where('duration_nights', (int) $request->get('duration_nights'));
        }

        if ($request->filled('destination_id')) {
            $destinationId = (int) $request->get('destination_id');
            $query->whereHas('destinations', fn (Builder $q) => $q->where('destinations.id', $destinationId));
        }

        if ($request->filled('search')) {
            $search = (string) $request->get('search');
            $query->where(function (Builder $sub) use ($search): void {
                $sub->where('name->id', 'like', "%{$search}%")
                    ->orWhere('name->en', 'like', "%{$search}%")
                    ->orWhere('name->ms', 'like', "%{$search}%")
                    ->orWhere('slug->id', 'like', "%{$search}%")
                    ->orWhere('slug->en', 'like', "%{$search}%")
                    ->orWhere('slug->ms', 'like', "%{$search}%");
            });
        }

        $paginator = $query->orderBy('tour_id')->orderBy('duration_days')->orderBy('id')->paginate(perPage: $perPage, page: $page);

        $packages = collect($paginator->items())->map(function (TourPackage $package) use ($locale): array {
            $startingPrice = $package->startingPriceTier();

            return [
                'id' => $package->id,
                'tour_id' => $package->tour_id,
                'tour_name' => $package->tour
                    ? ($package->tour->getTranslation('name', $locale, false) ?: $package->tour->getTranslation('name', 'id', false))
                    : null,
                'name' => $package->getTranslation('name', $locale, false) ?: $package->getTranslation('name', 'id', false),
                'slug' => $package->localizedSlug($locale),
                'duration_days' => (int) $package->duration_days,
                'duration_nights' => (int) $package->duration_nights,
                'duration_label' => $package->duration_label,
                'cover_url' => $package->cover_url,
                'starting_price' => $startingPrice ? [
                    'amount' => (float) $startingPrice->price,
                    'currency' => $startingPrice->currency,
                    'min_pax' => $startingPrice->min_pax,
                    'max_pax' => $startingPrice->max_pax,
                ] : null,
                'destinations' => $package->destinations->map(fn (Destination $d): array => [
                    'id' => $d->id,
                    'name' => $d->getTranslation('name', $locale, false) ?: $d->getTranslation('name', 'id', false),
                ])->all(),
                'tiers' => $package->tiers->map(fn (PackageTier $tier): array => [
                    'id' => $tier->id,
                    'name' => $tier->getTranslation('name', $locale, false) ?: $tier->getTranslation('name', 'id', false),
                    'hotel_stars' => $tier->hotel_stars,
                ])->all(),
                'itineraries_count' => (int) ($package->itineraries_count ?? 0),
                'includes_count' => (int) ($package->includes_count ?? 0),
                'tiers_count' => (int) ($package->tiers_count ?? 0),
                'created_at' => $package->created_at?->toIso8601String(),
                'updated_at' => $package->updated_at?->toIso8601String(),
            ];
        })->all();

        return Response::json([
            'packages' => $packages,
            'pagination' => [
                'total' => $paginator->total(),
                'count' => count($packages),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'total_pages' => $paginator->lastPage(),
            ],
            'locale' => $locale,
        ]);
    }
}
