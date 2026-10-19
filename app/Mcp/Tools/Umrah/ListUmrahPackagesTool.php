<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Umrah;

use App\Enums\UmrahPackageType;
use App\Models\Country;
use App\Models\Destination;
use App\Models\UmrahPackage;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('list_umrah_packages')]
#[Title('List Umrah Packages')]
#[Description('List and filter Umrah pilgrimage packages by package type, duration, price range, quota availability, airline, destination, and search query.')]
class ListUmrahPackagesTool extends Tool
{
    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'package_type' => $schema->string()->description('Filter by package type: "regular", "plus", "vip", "ramadan", or "private".'),
            'duration_days' => $schema->integer()->description('Filter by duration in days (e.g., 9, 12, 16).'),
            'min_price_idr' => $schema->integer()->description('Filter by minimum price in IDR.'),
            'max_price_idr' => $schema->integer()->description('Filter by maximum price in IDR.'),
            'has_availability' => $schema->boolean()->description('Filter only packages that have open upcoming departures with available quota.'),
            'airline' => $schema->string()->description('Filter by airline name (partial match, e.g. "Garuda", "Saudia").'),
            'destination_id' => $schema->integer()->description('Filter by associated destination/ziarah ID.'),
            'country_id' => $schema->integer()->description('Filter by associated country ID.'),
            'is_active' => $schema->boolean()->description('Filter by active status (true/false).'),
            'is_featured' => $schema->boolean()->description('Filter by featured status (true/false).'),
            'search' => $schema->string()->description('Search keyword matching package name, slug, airline, or hotel across languages.'),
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

        $query = UmrahPackage::query()
            ->with([
                'prices',
                'privatePrices',
                'upcomingDepartures',
                'countries',
                'destinations',
            ])
            ->withCount(['departures', 'testimonials'])
            ->withAvg('testimonials', 'rating');

        if ($request->filled('package_type')) {
            $type = (string) $request->get('package_type');
            $enumType = UmrahPackageType::tryFrom($type);
            $query->where('package_type', $enumType?->value ?? $type);
        }

        if ($request->filled('duration_days')) {
            $query->where('duration_days', (int) $request->get('duration_days'));
        }

        if ($request->filled('min_price_idr')) {
            $query->where('price_idr', '>=', (int) $request->get('min_price_idr'));
        }

        if ($request->filled('max_price_idr')) {
            $query->where('price_idr', '<=', (int) $request->get('max_price_idr'));
        }

        if ($request->has('has_availability') && (bool) $request->get('has_availability')) {
            $query->whereHas('upcomingDepartures', fn (Builder $q) => $q->where('status', '!=', 'full')->where('status', '!=', 'closed'));
        }

        if ($request->filled('airline')) {
            $airline = (string) $request->get('airline');
            $query->where('airline', 'like', "%{$airline}%");
        }

        if ($request->filled('destination_id')) {
            $destinationId = (int) $request->get('destination_id');
            $query->whereHas('destinations', fn (Builder $q) => $q->where('destinations.id', $destinationId));
        }

        if ($request->filled('country_id')) {
            $countryId = (int) $request->get('country_id');
            $query->whereHas('countries', fn (Builder $q) => $q->where('countries.id', $countryId));
        }

        if ($request->has('is_active')) {
            $query->where('is_active', (bool) $request->get('is_active'));
        }

        if ($request->has('is_featured')) {
            $query->where('is_featured', (bool) $request->get('is_featured'));
        }

        if ($request->filled('search')) {
            $search = (string) $request->get('search');
            $query->where(function (Builder $sub) use ($search): void {
                $sub->where('name->id', 'like', "%{$search}%")
                    ->orWhere('name->en', 'like', "%{$search}%")
                    ->orWhere('name->ms', 'like', "%{$search}%")
                    ->orWhere('slug->id', 'like', "%{$search}%")
                    ->orWhere('slug->en', 'like', "%{$search}%")
                    ->orWhere('airline', 'like', "%{$search}%")
                    ->orWhere('hotel_makkah', 'like', "%{$search}%")
                    ->orWhere('hotel_madinah', 'like', "%{$search}%");
            });
        }

        $paginator = $query->orderByDesc('is_featured')->orderByDesc('id')->paginate(perPage: $perPage, page: $page);

        $packages = collect($paginator->items())->map(function (UmrahPackage $package) use ($locale): array {
            $nextDeparture = $package->upcomingDepartures->first();

            return [
                'id' => $package->id,
                'name' => $package->getTranslation('name', $locale, false) ?: $package->getTranslation('name', 'id', false),
                'slug' => $package->localizedSlug($locale),
                'package_type' => $package->package_type,
                'package_type_label' => $package->package_type_label,
                'duration_days' => (int) $package->duration_days,
                'starting_price_idr' => (int) $package->starting_price_idr,
                'formatted_price' => $package->formatted_price,
                'airline' => $package->airline,
                'hotel_makkah' => $package->hotel_makkah,
                'hotel_makkah_stars' => $package->hotel_makkah_stars,
                'hotel_madinah' => $package->hotel_madinah,
                'hotel_madinah_stars' => $package->hotel_madinah_stars,
                'visa_included' => (bool) $package->visa_included,
                'handling_included' => (bool) $package->handling_included,
                'thumbnail_url' => $package->thumbnail_url,
                'is_active' => (bool) $package->is_active,
                'is_featured' => (bool) $package->is_featured,
                'has_availability' => $package->has_availability,
                'next_departure' => $nextDeparture ? [
                    'id' => $nextDeparture->id,
                    'departure_date' => $nextDeparture->departure_date?->toDateString(),
                    'return_date' => $nextDeparture->return_date?->toDateString(),
                    'status' => $nextDeparture->status,
                    'quota_sisa' => $nextDeparture->quota_sisa,
                    'quota_total' => $nextDeparture->quota_total,
                ] : null,
                'destinations' => $package->destinations->map(fn (Destination $d): array => [
                    'id' => $d->id,
                    'name' => $d->getTranslation('name', $locale, false) ?: $d->getTranslation('name', 'id', false),
                    'slug' => $d->slug,
                ])->all(),
                'countries' => $package->countries->map(fn (Country $c): array => [
                    'id' => $c->id,
                    'name' => $c->getTranslation('name', $locale, false) ?: $c->getTranslation('name', 'id', false),
                    'code' => $c->code,
                ])->all(),
                'average_rating' => round((float) ($package->testimonials_avg_rating ?? 0), 1),
                'testimonials_count' => (int) ($package->testimonials_count ?? 0),
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
