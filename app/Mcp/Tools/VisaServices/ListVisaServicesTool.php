<?php

declare(strict_types=1);

namespace App\Mcp\Tools\VisaServices;

use App\Models\Country;
use App\Models\VisaService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('list_visa_services')]
#[Title('List Visa Services')]
#[Description('List visa services with filters by country, entry type (single/multiple), active/featured status, search keyword, and pagination.')]
class ListVisaServicesTool extends Tool
{
    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'search' => $schema->string()->description('Search keyword matching visa name, type, summary, or description.'),
            'country_id' => $schema->integer()->description('Filter by country numeric ID.'),
            'country_slug' => $schema->string()->description('Filter by country URL slug (e.g. "arab-saudi", "jepang").'),
            'iso_code' => $schema->string()->description('Filter by country ISO Alpha-2 or Alpha-3 code (e.g. "SA", "JPN", "TUR").'),
            'entry_type' => $schema->string()->description('Filter by entry type ("single" or "multiple").'),
            'is_active' => $schema->boolean()->description('Filter by active status. Default is true.'),
            'is_featured' => $schema->boolean()->description('Filter by featured status.'),
            'locale' => $schema->string()->description('Preferred display locale ("id", "en", or "ms"). Default is "id".'),
            'per_page' => $schema->integer()->description('Number of results per page (max 50, default 15).'),
            'page' => $schema->integer()->description('Page number for pagination (default 1).'),
        ];
    }

    public function handle(Request $request): Response
    {
        $locale = (string) $request->get('locale', 'id');
        $perPage = min(50, max(1, (int) $request->get('per_page', 15)));
        $page = max(1, (int) $request->get('page', 1));

        $query = VisaService::query()
            ->with(['country', 'items'])
            ->withCount('items');

        // Status filters
        if ($request->has('is_active')) {
            $query->where('is_active', (bool) $request->get('is_active'));
        }

        if ($request->has('is_featured')) {
            $query->where('is_featured', (bool) $request->get('is_featured'));
        }

        // Country filter
        $countryId = $request->get('country_id');
        $countrySlug = $request->get('country_slug');
        $isoCode = $request->get('iso_code');

        if (! empty($countryId)) {
            $query->where('country_id', (int) $countryId);
        } elseif (! empty($countrySlug)) {
            $query->whereHas('country', fn (Builder $q) => $q->where('slug', (string) $countrySlug));
        } elseif (! empty($isoCode)) {
            $code = Str::upper(trim((string) $isoCode));
            $query->whereHas('country', fn (Builder $q) => $q->where('iso_alpha_2', $code)->orWhere('iso_alpha_3', $code));
        }

        // Entry type filter
        if ($request->has('entry_type') && filled($request->get('entry_type'))) {
            $query->where('entry_type', (string) $request->get('entry_type'));
        }

        // Search filter
        $search = $request->get('search');
        if (filled($search)) {
            $term = trim((string) $search);
            $query->where(function (Builder $q) use ($term): void {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('slug', 'like', "%{$term}%")
                    ->orWhere('visa_type', 'like', "%{$term}%")
                    ->orWhere('summary', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%");
            });
        }

        $paginator = $query->orderBy('sort_order')->orderBy('id')->paginate($perPage, ['*'], 'page', $page);

        $services = collect($paginator->items())->map(function (VisaService $service) use ($locale): array {
            $name = $service->getTranslation('name', $locale, false) ?: $service->getTranslation('name', 'id', false);
            $visaType = $service->getTranslation('visa_type', $locale, false) ?: $service->getTranslation('visa_type', 'id', false);
            $summary = $service->getTranslation('summary', $locale, false) ?: $service->getTranslation('summary', 'id', false);

            $countryName = $service->country
                ? ($service->country->getTranslation('name', $locale, false) ?: $service->country->getTranslation('name', 'id', false))
                : null;

            return [
                'id' => $service->id,
                'name' => $name,
                'slug' => $service->slug,
                'visa_type' => $visaType,
                'summary' => $summary,
                'entry_type' => $service->entry_type?->value,
                'entry_type_label' => $service->entry_type?->getLabel(),
                'country' => $service->country ? [
                    'id' => $service->country->id,
                    'name' => $countryName,
                    'slug' => $service->country->slug,
                    'iso_alpha_2' => $service->country->iso_alpha_2,
                    'iso_alpha_3' => $service->country->iso_alpha_3,
                ] : null,
                'pricing' => [
                    'currency' => $service->currency,
                    'price' => $service->price !== null ? (float) $service->price : null,
                    'price_idr' => $service->price_idr,
                    'formatted_price' => $service->formatted_price,
                ],
                'processing_time' => [
                    'days_min' => $service->processing_days_min,
                    'days_max' => $service->processing_days_max,
                    'display' => $service->processing_days_min !== null
                        ? ($service->processing_days_max !== null
                            ? "{$service->processing_days_min} - {$service->processing_days_max} hari kerja"
                            : "{$service->processing_days_min} hari kerja")
                        : null,
                ],
                'validity' => [
                    'validity_days' => $service->validity_days,
                    'maximum_stay_days' => $service->maximum_stay_days,
                ],
                'items_count' => $service->items_count,
                'is_active' => $service->is_active,
                'is_featured' => $service->is_featured,
                'cover_url' => $service->cover_url,
            ];
        })->values()->all();

        return Response::json([
            'data' => $services,
            'pagination' => [
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
            ],
            'locale' => $locale,
        ]);
    }
}
