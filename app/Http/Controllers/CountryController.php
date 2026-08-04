<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Destination;
use App\Models\Post;
use App\Models\TourPackage;
use App\Models\UmrahPackage;
use App\Models\Vehicle;
use App\Models\VisaService;
use App\Settings\GeneralSettings;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CountryController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->get('q'));

        $query = Country::query()
            ->active()
            ->with('media')
            ->withCount([
                'tourPackages as tour_packages_count' => fn (Builder $query): Builder => $query
                    ->whereHas('tour', fn (Builder $query): Builder => $query->active()),
                'umrahPackages as umrah_packages_count' => fn (Builder $query): Builder => $query->active(),
                'visaServices as visa_services_count' => fn (Builder $query): Builder => $query->publiclyAvailable(),
                'destinations as destinations_count' => fn (Builder $query): Builder => $query->active(),
                'vehicles as vehicles_count' => fn (Builder $query): Builder => $query->active(),
            ]);

        if (filled($search)) {
            $query->where('name', 'like', "%{$search}%");
        }

        $countries = $query
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(12)
            ->withQueryString();

        $page = max(1, $request->integer('page', 1));
        $alternateUrls = collect(['id', 'ms', 'en'])
            ->mapWithKeys(fn (string $locale): array => [
                $locale => route('country.index', array_filter([
                    'locale' => $locale,
                    'page' => $page > 1 ? $page : null,
                    'q' => filled($search) ? $search : null,
                ])),
            ])
            ->all();

        $canonicalUrl = $alternateUrls[app()->getLocale()];
        $schemaJson = json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'CollectionPage',
            'name' => __('country.seo.index_title'),
            'description' => __('country.seo.index_description'),
            'url' => $canonicalUrl,
            'mainEntity' => [
                '@type' => 'ItemList',
                'itemListElement' => $countries->getCollection()
                    ->values()
                    ->map(fn (Country $country, int $index): array => [
                        '@type' => 'ListItem',
                        'position' => (($countries->currentPage() - 1) * $countries->perPage()) + $index + 1,
                        'name' => $country->name,
                        'url' => route('country.show', [
                            'locale' => app()->getLocale(),
                            'country' => $country,
                        ]),
                    ])
                    ->all(),
            ],
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return view('pages.country.index', compact(
            'alternateUrls',
            'canonicalUrl',
            'countries',
            'schemaJson',
            'search',
        ));
    }

    public function show(string $locale, Country $country): View
    {
        abort_unless($country->is_active, 404);

        $country->load('media');

        $tourPackages = TourPackage::query()
            ->whereHas('tour', fn (Builder $query): Builder => $query->active())
            ->where(function (Builder $query) use ($country): void {
                $query->whereHas('countries', fn (Builder $q): Builder => $q->whereKey($country->getKey()))
                    ->orWhereHas('tour.countries', fn (Builder $q): Builder => $q->whereKey($country->getKey()));
            })
            ->with([
                'tour.category',
                'media',
                'includes',
                'itineraries',
                'tiers.priceTiers',
            ])
            ->latest('id')
            ->paginate(6, ['*'], 'tour_page')
            ->withQueryString();

        $umrahPackages = UmrahPackage::query()
            ->active()
            ->whereHas('countries', fn (Builder $query): Builder => $query->whereKey($country->getKey()))
            ->with([
                'media',
                'prices',
                'upcomingDepartures' => fn ($query) => $query->limit(1),
            ])
            ->orderByDesc('is_featured')
            ->latest('id')
            ->paginate(6, ['*'], 'umrah_page')
            ->withQueryString();

        $visaServices = VisaService::query()
            ->publiclyAvailable()
            ->where('country_id', $country->id)
            ->with(['media', 'country.media'])
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->paginate(6, ['*'], 'visa_page')
            ->withQueryString();

        $destinations = Destination::query()
            ->active()
            ->whereHas('countries', fn (Builder $query): Builder => $query->whereKey($country->getKey()))
            ->with('media')
            ->latest('id')
            ->paginate(6, ['*'], 'destination_page')
            ->withQueryString();

        $vehicles = Vehicle::query()
            ->active()
            ->whereHas('countries', fn (Builder $query): Builder => $query->whereKey($country->getKey()))
            ->with('media')
            ->latest('id')
            ->paginate(6, ['*'], 'vehicle_page')
            ->withQueryString();

        $posts = Post::query()
            ->published()
            ->whereHas('countries', fn (Builder $query): Builder => $query->whereKey($country->getKey()))
            ->with(['category', 'author'])
            ->latest('published_at')
            ->paginate(6, ['*'], 'article_page')
            ->withQueryString();

        $alternateUrls = collect(['id', 'ms', 'en'])
            ->mapWithKeys(fn (string $supportedLocale): array => [
                $supportedLocale => route('country.show', [
                    'locale' => $supportedLocale,
                    'country' => $country,
                ]),
            ])
            ->all();

        $canonicalUrl = $alternateUrls[$locale];
        $description = Str::limit(
            strip_tags((string) ($country->description ?: __('country.show.overview', ['name' => $country->name]))),
            160,
            '',
        );

        $schemaJson = json_encode([
            '@context' => 'https://schema.org',
            '@graph' => [
                array_filter([
                    '@type' => 'Country',
                    '@id' => $canonicalUrl.'#country',
                    'name' => $country->name,
                    'description' => $description,
                    'url' => $canonicalUrl,
                    'image' => $country->cover_url,
                ]),
            ],
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $whatsappNumber = app(GeneralSettings::class)->whatsapp_number;

        return view('pages.country.show', compact(
            'alternateUrls',
            'canonicalUrl',
            'country',
            'description',
            'destinations',
            'posts',
            'schemaJson',
            'tourPackages',
            'umrahPackages',
            'vehicles',
            'visaServices',
            'whatsappNumber',
        ));
    }
}
