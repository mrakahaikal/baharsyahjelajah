<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Countries;

use App\Mcp\Tools\Concerns\NormalizesMultilingualInput;
use App\Models\Country;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('list_countries')]
#[Title('List Countries')]
#[Description('List countries with search, active/featured status filters, locale selection, and pagination.')]
class ListCountriesTool extends Tool
{
    use NormalizesMultilingualInput;

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'search' => $schema->string()->description('Optional search query matching country name, slug, ISO codes, capital city, or currency.'),
            'is_active' => $schema->boolean()->description('Filter by active status: true for active only, false for inactive only, or omit for all.'),
            'is_featured' => $schema->boolean()->description('Filter by featured status: true for featured only, false for non-featured only, or omit for all.'),
            'locale' => $schema->string()->description('Preferred display locale ("id", "en", or "ms"). Default is "id".'),
            'per_page' => $schema->integer()->description('Number of countries per page (default: 15, max: 100).'),
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

        $query = Country::query()
            ->withCount(['destinations', 'visaServices', 'tourPackages', 'umrahPackages', 'posts'])
            ->orderBy('sort_order')
            ->orderBy('id');

        if ($request->has('is_active')) {
            $query->where('is_active', (bool) $request->get('is_active'));
        }

        if ($request->has('is_featured')) {
            $query->where('is_featured', (bool) $request->get('is_featured'));
        }

        $search = trim((string) $request->get('search', ''));
        if ($search !== '') {
            $query->where(function (Builder $q) use ($search): void {
                $q->where('name->id', 'like', "%{$search}%")
                    ->orWhere('name->en', 'like', "%{$search}%")
                    ->orWhere('name->ms', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('iso_alpha_2', 'like', "%{$search}%")
                    ->orWhere('iso_alpha_3', 'like', "%{$search}%")
                    ->orWhere('currency_code', 'like', "%{$search}%")
                    ->orWhere('capital_city->id', 'like', "%{$search}%");
            });
        }

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        $countries = collect($paginator->items())->map(function (Country $country) use ($locale): array {
            return [
                'id' => $country->id,
                'name' => $country->getTranslation('name', $locale, false) ?: $country->getTranslation('name', 'id', false),
                'slug' => $country->slug,
                'iso_alpha_2' => $country->iso_alpha_2,
                'iso_alpha_3' => $country->iso_alpha_3,
                'currency_code' => $country->currency_code,
                'capital_city' => $country->getTranslation('capital_city', $locale, false),
                'language' => $country->getTranslation('language', $locale, false),
                'best_time_to_visit' => $country->getTranslation('best_time_to_visit', $locale, false),
                'travel_requirements_summary' => $country->getTranslation('travel_requirements_summary', $locale, false),
                'description' => $country->getTranslation('description', $locale, false),
                'is_active' => $country->is_active,
                'is_featured' => $country->is_featured,
                'sort_order' => $country->sort_order,
                'flag_url' => $country->flag_url,
                'cover_url' => $country->cover_url,
                'connected_counts' => [
                    'destinations' => $country->destinations_count,
                    'visa_services' => $country->visa_services_count,
                    'tour_packages' => $country->tour_packages_count,
                    'umrah_packages' => $country->umrah_packages_count,
                    'articles' => $country->posts_count,
                ],
                'created_at' => $country->created_at?->toIso8601String(),
                'updated_at' => $country->updated_at?->toIso8601String(),
            ];
        })->all();

        return Response::json([
            'countries' => $countries,
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
