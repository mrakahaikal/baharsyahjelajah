<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Vehicles;

use App\Enums\VehicleCategory;
use App\Models\Country;
use App\Models\Vehicle;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('list_vehicles')]
#[Title('List Vehicles & Fleet')]
#[Description('List rental fleet vehicles with filters by category (car/minibus/bus), transmission, passenger capacity, country, search keyword, and pagination.')]
class ListVehiclesTool extends Tool
{
    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'search' => $schema->string()->description('Search keyword matching vehicle name, brand, model, or catalog code.'),
            'category' => $schema->string()->description('Filter by vehicle category: "car", "minibus", or "bus".'),
            'transmission' => $schema->string()->description('Filter by transmission type: "automatic" or "manual".'),
            'min_passengers' => $schema->integer()->description('Filter vehicles with capacity_pax >= min_passengers.'),
            'min_luggage' => $schema->integer()->description('Filter vehicles with capacity_luggage >= min_luggage.'),
            'country_id' => $schema->integer()->description('Filter by operating country ID.'),
            'country_slug' => $schema->string()->description('Filter by operating country slug (e.g. "indonesia", "arab-saudi").'),
            'has_ac' => $schema->boolean()->description('Filter vehicles with Air Conditioning.'),
            'has_wifi' => $schema->boolean()->description('Filter vehicles with onboard WiFi.'),
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

        $query = Vehicle::query()
            ->with(['countries', 'rentalRates'])
            ->withCount(['rentalRates', 'tourPackages', 'umrahPackages']);

        // Status filters
        if ($request->has('is_active')) {
            $query->where('is_active', (bool) $request->get('is_active'));
        }

        if ($request->has('is_featured')) {
            $query->where('is_featured', (bool) $request->get('is_featured'));
        }

        // Category filter
        if ($request->has('category') && filled($request->get('category'))) {
            $cat = VehicleCategory::tryFrom((string) $request->get('category'));
            if ($cat) {
                $query->where('category', $cat);
            }
        }

        // Transmission filter
        if ($request->has('transmission') && filled($request->get('transmission'))) {
            $query->where('transmission', (string) $request->get('transmission'));
        }

        // Capacity filters
        if ($request->has('min_passengers') && $request->get('min_passengers') !== null) {
            $query->byCapacity((int) $request->get('min_passengers'));
        }

        if ($request->has('min_luggage') && $request->get('min_luggage') !== null) {
            $query->where('capacity_luggage', '>=', (int) $request->get('min_luggage'));
        }

        // Amenities
        if ($request->has('has_ac')) {
            $query->where('has_ac', (bool) $request->get('has_ac'));
        }

        if ($request->has('has_wifi')) {
            $query->where('has_wifi', (bool) $request->get('has_wifi'));
        }

        // Country filter
        $countryId = $request->get('country_id');
        $countrySlug = $request->get('country_slug');

        if (! empty($countryId)) {
            $query->whereHas('countries', fn (Builder $q) => $q->where('countries.id', (int) $countryId));
        } elseif (! empty($countrySlug)) {
            $query->whereHas('countries', fn (Builder $q) => $q->where('countries.slug', (string) $countrySlug));
        }

        // Search keyword
        $search = $request->get('search');
        if (filled($search)) {
            $term = trim((string) $search);
            $query->where(function (Builder $q) use ($term): void {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('slug', 'like', "%{$term}%")
                    ->orWhere('catalog_code', 'like', "%{$term}%")
                    ->orWhere('brand', 'like', "%{$term}%")
                    ->orWhere('model', 'like', "%{$term}%");
            });
        }

        $paginator = $query->orderBy('sort_order')->orderBy('id')->paginate($perPage, ['*'], 'page', $page);

        $vehicles = collect($paginator->items())->map(function (Vehicle $v) use ($locale): array {
            $name = $v->getTranslation('name', $locale, false) ?: $v->getTranslation('name', 'id', false);
            $slug = $v->getTranslation('slug', $locale, false) ?: $v->getTranslation('slug', 'id', false);
            $desc = $v->getTranslation('description', $locale, false) ?: $v->getTranslation('description', 'id', false);

            return [
                'id' => $v->id,
                'catalog_code' => $v->catalog_code,
                'name' => $name,
                'slug' => $slug,
                'category' => $v->category?->value,
                'category_label' => $v->category?->getLabel(),
                'brand' => $v->brand,
                'model' => $v->model,
                'year' => $v->year,
                'specifications' => [
                    'capacity_pax' => $v->capacity_pax,
                    'capacity_luggage' => $v->capacity_luggage,
                    'capacity_label' => $v->getTranslation('capacity_label', $locale, false) ?: $v->getTranslation('capacity_label', 'id', false),
                    'transmission' => $v->transmission,
                    'has_ac' => $v->has_ac,
                    'has_wifi' => $v->has_wifi,
                ],
                'pricing_idr' => [
                    'price_per_day' => $v->price_per_day_idr,
                    'price_per_trip' => $v->price_per_trip_idr,
                    'overtime_rate' => $v->overtime_rate_idr,
                ],
                'countries' => $v->countries->map(fn (Country $c) => [
                    'id' => $c->id,
                    'name' => $c->getTranslation('name', $locale, false) ?: $c->getTranslation('name', 'id', false),
                    'slug' => $c->slug,
                    'iso_alpha_2' => $c->iso_alpha_2,
                ])->values()->all(),
                'active_rental_rates_count' => $v->rental_rates_count,
                'is_active' => $v->is_active,
                'is_featured' => $v->is_featured,
                'thumbnail_url' => $v->thumbnail_url,
            ];
        })->values()->all();

        return Response::json([
            'data' => $vehicles,
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
