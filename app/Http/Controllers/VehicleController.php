<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Settings\GeneralSettings;
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
        $canonicalSlug = $vehicle->slug ?: (string) $vehicle->getKey();

        if (request()->route('vehicle') !== $canonicalSlug) {
            return redirect()->route('transport.show', [
                'locale' => $locale,
                'vehicle' => $canonicalSlug,
            ], 301);
        }

        $vehicle->load(['media', 'galleries', 'testimonials']);

        $relatedVehicles = Vehicle::query()
            ->active()
            ->whereKeyNot($vehicle->id)
            ->with('media')
            ->orderByDesc('is_featured')
            ->orderBy('capacity_pax')
            ->limit(3)
            ->get();

        $alternateUrls = $this->alternateUrls($vehicle, 'transport.show');
        $canonicalUrl = $alternateUrls[$locale];
        $startingPrice = collect([
            $vehicle->price_per_day_idr,
            $vehicle->price_per_trip_idr,
        ])->filter()->min() ?? 0;
        $schemaJson = json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $vehicle->name,
            'description' => strip_tags((string) $vehicle->description),
            'image' => [$vehicle->thumbnail_url],
            'brand' => ['@type' => 'Brand', 'name' => $vehicle->brand],
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
            'schemaJson',
            'vehicle',
        ));
    }

    public function booking(string $locale, string $vehicle): View|RedirectResponse
    {
        /** @var Vehicle $vehicle */
        $vehicle = $this->findByTranslatedSlug(Vehicle::class, $vehicle);
        $canonicalSlug = $vehicle->slug ?: (string) $vehicle->getKey();

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
        $initialRate = in_array(request()->query('rate'), ['daily', 'trip'], true)
            ? request()->query('rate')
            : null;
        $initialPax = max(1, min(request()->integer('pax', 1), $vehicle->capacity_pax));
        $whatsappNumber = app(GeneralSettings::class)->whatsapp_number;

        return view('pages.transport.booking', compact(
            'alternateUrls',
            'canonicalUrl',
            'initialPax',
            'initialRate',
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
            ->where(function ($query) use ($slug): void {
                $query->where('slug->'.app()->getLocale(), $slug)
                    ->orWhere('slug->id', $slug);

                if (ctype_digit($slug)) {
                    $query->orWhereKey((int) $slug);
                }
            })
            ->firstOrFail();
    }

    /** @return array<string, string> */
    private function alternateUrls(Vehicle $vehicle, string $routeName): array
    {
        return collect(['id', 'ms', 'en'])
            ->mapWithKeys(fn (string $locale): array => [
                $locale => route($routeName, [
                    'locale' => $locale,
                    'vehicle' => $vehicle->getTranslation('slug', $locale, false)
                        ?: $vehicle->getTranslation('slug', 'id', false)
                        ?: $vehicle->getKey(),
                ]),
            ])
            ->all();
    }
}
