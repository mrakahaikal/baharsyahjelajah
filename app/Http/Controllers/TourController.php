<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Models\Tour;
use App\Models\TourPackage;
use App\Settings\GeneralSettings;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TourController extends Controller
{
    public function index(): View
    {
        $heroTour = Tour::query()
            ->active()
            ->with([
                'packages' => fn ($query) => $query
                    ->oldest('id')
                    ->limit(1)
                    ->with('media'),
            ])
            ->orderByDesc('is_featured')
            ->latest()
            ->first();

        $heroImageUrl = $heroTour?->packages->first()?->cover_url ?? TourPackage::DEFAULT_COVER_URL;
        $heroImageAlt = $heroTour?->name ?? config('app.name');

        $alternateUrls = collect(['id', 'ms', 'en'])
            ->mapWithKeys(fn (string $targetLocale): array => [
                $targetLocale => route('tour.index', ['locale' => $targetLocale]),
            ])
            ->all();
        $canonicalUrl = $alternateUrls[app()->getLocale()];

        return view('pages.tour.index', compact(
            'alternateUrls',
            'canonicalUrl',
            'heroImageAlt',
            'heroImageUrl',
        ));
    }

    public function show(string $locale, string $tour): View|RedirectResponse
    {
        $requestedSlug = $tour;
        $tour = $this->findActiveTourByTranslatedSlug($requestedSlug);

        if ($requestedSlug !== $tour->getTranslation('slug', $locale)) {
            return redirect()->route('tour.show', [
                'locale' => $locale,
                'tour' => $tour->getTranslation('slug', $locale),
            ], 301);
        }

        $tour->load([
            'category',
            'packages' => fn ($query) => $query
                ->oldest('id')
                ->with(['includes', 'itineraries', 'media', 'tiers.priceTiers']),
        ]);

        $destinationHighlights = Destination::query()
            ->whereHas(
                'itineraries.tourPackage',
                fn (Builder $query): Builder => $query->where('tour_id', $tour->id),
            )
            ->with('media')
            ->oldest('id')
            ->get();

        $relatedTours = Tour::query()
            ->active()
            ->with([
                'category',
                'packages' => fn ($query) => $query
                    ->oldest('id')
                    ->with(['media', 'tiers.priceTiers']),
            ])
            ->withCount('packages')
            ->whereKeyNot($tour->id)
            ->when(
                $tour->tour_category_id,
                fn (Builder $query) => $query->where('tour_category_id', $tour->tour_category_id),
            )
            ->latest()
            ->limit(3)
            ->get();

        $alternateUrls = $this->localizedTourUrls($tour);
        $canonicalUrl = $alternateUrls[app()->getLocale()];

        return view('pages.tour.show', compact(
            'alternateUrls',
            'canonicalUrl',
            'destinationHighlights',
            'relatedTours',
            'tour',
        ));
    }

    public function showPackage(string $locale, string $tour, string $package): View|RedirectResponse
    {
        $requestedTourSlug = $tour;
        $requestedPackageSlug = $package;
        $tour = $this->findActiveTourByTranslatedSlug($requestedTourSlug);
        $package = $this->findPackageByTranslatedSlug($tour, $requestedPackageSlug);

        if (
            $requestedTourSlug !== $tour->getTranslation('slug', $locale)
            || $requestedPackageSlug !== $package->getTranslation('slug', $locale)
        ) {
            return redirect()->route('tour.package.show', [
                'locale' => $locale,
                'tour' => $tour->getTranslation('slug', $locale),
                'package' => $package->getTranslation('slug', $locale),
            ], 301);
        }
        $package->load([
            'media',
            'itineraries.destinations.media',
            'includes',
            'tiers.priceTiers',
        ]);
        $alternateUrls = $this->localizedPackageUrls($tour, $package);
        $canonicalUrl = $alternateUrls[app()->getLocale()];

        return view('pages.tour.package.show', compact(
            'alternateUrls',
            'canonicalUrl',
            'package',
            'tour',
        ));
    }

    public function booking(Request $request, string $locale, string $tour, string $package): View|RedirectResponse
    {
        $requestedTourSlug = $tour;
        $requestedPackageSlug = $package;
        $tour = $this->findActiveTourByTranslatedSlug($requestedTourSlug);
        $package = $this->findPackageByTranslatedSlug($tour, $requestedPackageSlug);
        $package->load(['media', 'tiers.priceTiers']);

        $defaultPax = max(1, min(1000, app(GeneralSettings::class)->default_pax));
        $requestedPax = $request->integer('pax');
        $initialPax = $requestedPax >= 1 && $requestedPax <= 1000 ? $requestedPax : $defaultPax;
        $initialTierId = $package->tiers
            ->firstWhere('id', $request->integer('tier'))
            ?->id ?? $package->tiers->first()?->id;

        if (
            $requestedTourSlug !== $tour->getTranslation('slug', $locale)
            || $requestedPackageSlug !== $package->getTranslation('slug', $locale)
        ) {
            return redirect()->route('tour.package.booking', array_filter([
                'locale' => $locale,
                'tour' => $tour->getTranslation('slug', $locale),
                'package' => $package->getTranslation('slug', $locale),
                'tier' => $initialTierId,
                'pax' => $initialPax,
            ]), 301);
        }

        $alternateUrls = $this->localizedPackageBookingUrls($tour, $package);
        $canonicalUrl = $alternateUrls[app()->getLocale()];

        return view('pages.tour.package.booking', compact(
            'alternateUrls',
            'canonicalUrl',
            'initialPax',
            'initialTierId',
            'package',
            'tour',
        ));
    }

    private function findActiveTourByTranslatedSlug(string $slug): Tour
    {
        $locale = app()->getLocale();

        return Tour::query()
            ->active()
            ->where(fn (Builder $query): Builder => $query
                ->where("slug->{$locale}", $slug)
                ->orWhere('slug->id', $slug))
            ->firstOrFail();
    }

    private function findPackageByTranslatedSlug(Tour $tour, string $slug): TourPackage
    {
        $locale = app()->getLocale();

        return $tour->packages()
            ->where(fn (Builder $query): Builder => $query
                ->where("slug->{$locale}", $slug)
                ->orWhere('slug->id', $slug))
            ->firstOrFail();
    }

    /** @return array<string, string> */
    private function localizedTourUrls(Tour $tour): array
    {
        return collect(['id', 'ms', 'en'])
            ->mapWithKeys(fn (string $locale): array => [
                $locale => route('tour.show', [
                    'locale' => $locale,
                    'tour' => $tour->getTranslation('slug', $locale),
                ]),
            ])
            ->all();
    }

    /** @return array<string, string> */
    private function localizedPackageUrls(Tour $tour, TourPackage $package): array
    {
        return collect(['id', 'ms', 'en'])
            ->mapWithKeys(fn (string $locale): array => [
                $locale => route('tour.package.show', [
                    'locale' => $locale,
                    'tour' => $tour->getTranslation('slug', $locale),
                    'package' => $package->getTranslation('slug', $locale),
                ]),
            ])
            ->all();
    }

    /** @return array<string, string> */
    private function localizedPackageBookingUrls(Tour $tour, TourPackage $package): array
    {
        return collect(['id', 'ms', 'en'])
            ->mapWithKeys(fn (string $locale): array => [
                $locale => route('tour.package.booking', [
                    'locale' => $locale,
                    'tour' => $tour->getTranslation('slug', $locale),
                    'package' => $package->getTranslation('slug', $locale),
                ]),
            ])
            ->all();
    }
}
