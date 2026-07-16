<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Models\Post;
use App\Models\TourPackage;
use App\Models\UmrahPackage;
use App\Settings\GeneralSettings;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DestinationController extends Controller
{
    public function index(Request $request): View
    {
        $destinations = Destination::query()
            ->active()
            ->with('media')
            ->withExists([
                'tourPackages as has_direct_tour_packages' => fn (Builder $query): Builder => $query
                    ->whereHas('tour', fn (Builder $query): Builder => $query->active()),
                'itineraries as has_itinerary_tour_packages' => fn (Builder $query): Builder => $query
                    ->whereHas('tourPackage.tour', fn (Builder $query): Builder => $query->active()),
                'umrahPackages as has_umrah_packages' => fn (Builder $query): Builder => $query->active(),
                'posts as has_posts' => fn (Builder $query): Builder => $query->published(),
            ])
            ->orderByDesc('is_featured')
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        $page = max(1, $request->integer('page', 1));
        $alternateUrls = collect(['id', 'ms', 'en'])
            ->mapWithKeys(fn (string $locale): array => [
                $locale => route('destination.index', array_filter([
                    'locale' => $locale,
                    'page' => $page > 1 ? $page : null,
                ])),
            ])
            ->all();
        $canonicalUrl = $alternateUrls[app()->getLocale()];
        $schemaJson = json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'CollectionPage',
            'name' => __('destination.seo.index_title'),
            'description' => __('destination.seo.index_description'),
            'url' => $canonicalUrl,
            'mainEntity' => [
                '@type' => 'ItemList',
                'itemListElement' => $destinations->getCollection()
                    ->values()
                    ->map(fn (Destination $destination, int $index): array => [
                        '@type' => 'ListItem',
                        'position' => (($destinations->currentPage() - 1) * $destinations->perPage()) + $index + 1,
                        'name' => $destination->name,
                        'url' => route('destination.show', [
                            'locale' => app()->getLocale(),
                            'destination' => $destination,
                        ]),
                    ])
                    ->all(),
            ],
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return view('pages.destination.index', compact(
            'alternateUrls',
            'canonicalUrl',
            'destinations',
            'schemaJson',
        ));
    }

    public function show(string $locale, Destination $destination): View
    {
        abort_unless($destination->is_active, 404);

        $destination->load('media');

        $tourPackages = TourPackage::query()
            ->whereHas('tour', fn (Builder $query): Builder => $query->active())
            ->where(fn (Builder $query): Builder => $query
                ->whereHas('destinations', fn (Builder $query): Builder => $query->whereKey($destination->getKey()))
                ->orWhereHas('itineraries.destinations', fn (Builder $query): Builder => $query->whereKey($destination->getKey())))
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
            ->whereHas('destinations', fn (Builder $query): Builder => $query->whereKey($destination->getKey()))
            ->with([
                'media',
                'prices',
                'upcomingDepartures' => fn ($query) => $query->limit(1),
            ])
            ->orderByDesc('is_featured')
            ->latest('id')
            ->paginate(6, ['*'], 'umrah_page')
            ->withQueryString();

        $posts = Post::query()
            ->published()
            ->whereHas('destinations', fn (Builder $query): Builder => $query->whereKey($destination->getKey()))
            ->with(['category', 'author'])
            ->latest('published_at')
            ->paginate(6, ['*'], 'article_page')
            ->withQueryString();

        $alternateUrls = collect(['id', 'ms', 'en'])
            ->mapWithKeys(fn (string $supportedLocale): array => [
                $supportedLocale => route('destination.show', [
                    'locale' => $supportedLocale,
                    'destination' => $destination,
                ]),
            ])
            ->all();
        $canonicalUrl = $alternateUrls[$locale];
        $description = Str::limit(
            strip_tags((string) ($destination->description ?: __('destination.show.fallback_description', [
                'name' => $destination->name,
            ]))),
            160,
            '',
        );
        $images = $destination->gallery_urls->all();
        $schemaJson = json_encode([
            '@context' => 'https://schema.org',
            '@graph' => [
                array_filter([
                    '@type' => 'TouristDestination',
                    '@id' => $canonicalUrl.'#destination',
                    'name' => $destination->name,
                    'description' => $description,
                    'url' => $canonicalUrl,
                    'image' => $images !== [] ? $images : null,
                    'address' => $destination->location,
                ]),
            ],
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $whatsappNumber = app(GeneralSettings::class)->whatsapp_number;

        return view('pages.destination.show', compact(
            'alternateUrls',
            'canonicalUrl',
            'description',
            'destination',
            'posts',
            'schemaJson',
            'tourPackages',
            'umrahPackages',
            'whatsappNumber',
        ));
    }
}
