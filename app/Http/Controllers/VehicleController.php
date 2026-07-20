<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\VehicleRentalArea;
use App\Models\VehicleRentalTerm;
use App\Settings\GeneralSettings;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class VehicleController extends Controller
{
    public function index(): View
    {
        $alternateUrls = collect(['id', 'ms', 'en'])
            ->mapWithKeys(fn (string $locale): array => [
                $locale => route('transport.index', ['locale' => $locale]),
            ])
            ->all();
        $canonicalUrl = $alternateUrls[app()->getLocale()];

        return view('pages.transport.index', compact('alternateUrls', 'canonicalUrl'));
    }

    public function show(string $locale, string $vehicle): View|RedirectResponse
    {
        /** @var Vehicle $vehicle */
        $vehicle = $this->findByTranslatedSlug(Vehicle::class, $vehicle);
        $canonicalSlug = $this->translatedSlug($vehicle, $locale);

        if (request()->route('vehicle') !== $canonicalSlug) {
            return redirect()->route('transport.show', [
                'locale' => $locale,
                'vehicle' => $canonicalSlug,
                ...request()->query(),
            ], 301);
        }

        $availableAreas = VehicleRentalArea::query()
            ->active()
            ->whereHas('rates', fn (Builder $query) => $query->active()->effectiveOn(today())->where('vehicle_id', $vehicle->id))
            ->orderBy('sort_order')
            ->get();
        $selectedArea = $availableAreas->firstWhere('slug', request()->query('area')) ?? $availableAreas->first();

        $vehicle->load([
            'media',
            'galleries',
            'testimonials',
            'rentalRates' => fn ($query) => $query->active()->effectiveOn(today())->when($selectedArea, fn ($query) => $query->forArea($selectedArea))->latest('valid_from'),
        ]);
        $rentalTerms = VehicleRentalTerm::query()
            ->active()
            ->where(fn (Builder $query) => $query->whereNull('vehicle_category')->orWhere('vehicle_category', $vehicle->category?->value))
            ->orderBy('sort_order')
            ->get();

        $relatedVehicles = Vehicle::query()
            ->active()
            ->whereKeyNot($vehicle->id)
            ->when($selectedArea, fn (Builder $query) => $query->whereHas('rentalRates', fn (Builder $query) => $query->active()->effectiveOn(today())->forArea($selectedArea)))
            ->with([
                'media',
                'rentalRates' => fn ($query) => $query->active()->effectiveOn(today())->when($selectedArea, fn ($query) => $query->forArea($selectedArea)),
            ])
            ->orderByDesc('is_featured')
            ->orderBy('capacity_pax')
            ->limit(3)
            ->get();

        $alternateUrls = $this->alternateUrls($vehicle, 'transport.show');
        $canonicalUrl = $alternateUrls[$locale];
        $startingPrice = $vehicle->rentalRates->min('price_per_day_idr') ?? 0;
        $schemaJson = json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $vehicle->name,
            'description' => strip_tags((string) $vehicle->description),
            'image' => [$vehicle->thumbnail_url],
            'brand' => $vehicle->brand ? ['@type' => 'Brand', 'name' => $vehicle->brand] : null,
            'offers' => [
                '@type' => 'AggregateOffer',
                'url' => $canonicalUrl,
                'priceCurrency' => 'IDR',
                'lowPrice' => $startingPrice,
                'availability' => 'https://schema.org/InStock',
            ],
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return view('pages.transport.show', compact(
            'alternateUrls',
            'canonicalUrl',
            'relatedVehicles',
            'rentalTerms',
            'schemaJson',
            'selectedArea',
            'availableAreas',
            'vehicle',
        ));
    }

    public function booking(string $locale, string $vehicle): View|RedirectResponse
    {
        /** @var Vehicle $vehicle */
        $vehicle = $this->findByTranslatedSlug(Vehicle::class, $vehicle);
        $canonicalSlug = $this->translatedSlug($vehicle, $locale);

        if (request()->route('vehicle') !== $canonicalSlug) {
            return redirect()->route('transport.booking', [
                'locale' => $locale,
                'vehicle' => $canonicalSlug,
                ...request()->query(),
            ], 301);
        }

        $vehicle->load('media');
        $alternateUrls = $this->alternateUrls($vehicle, 'transport.booking');
        $canonicalUrl = $alternateUrls[$locale];
        $initialArea = request()->string('area')->toString() ?: null;
        $initialPax = max(1, min(request()->integer('pax', 1), $vehicle->capacity_pax ?: 100));
        $whatsappNumber = app(GeneralSettings::class)->whatsapp_number;

        return view('pages.transport.booking', compact(
            'alternateUrls',
            'canonicalUrl',
            'initialPax',
            'initialArea',
            'vehicle',
            'whatsappNumber',
        ));
    }

    /**
     * @param  class-string<Model>  $model
     */
    private function findByTranslatedSlug(string $model, string $slug): Model
    {
        return $model::query()
            ->active()
            ->whereLocalizedSlug($slug)
            ->firstOrFail();
    }

    /** @return array<string, string> */
    private function alternateUrls(Vehicle $vehicle, string $routeName): array
    {
        return collect(['id', 'ms', 'en'])
            ->mapWithKeys(fn (string $locale): array => [
                $locale => route($routeName, [
                    'locale' => $locale,
                    'vehicle' => $this->translatedSlug($vehicle, $locale),
                ]),
            ])
            ->all();
    }

    private function translatedSlug(Vehicle $vehicle, string $locale): string
    {
        return $vehicle->localizedSlug($locale);
    }
}
