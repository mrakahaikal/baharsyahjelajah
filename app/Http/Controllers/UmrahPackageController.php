<?php

namespace App\Http\Controllers;

use App\Enums\UmrahPackageType;
use App\Models\Country;
use App\Models\UmrahPackage;
use App\Settings\GeneralSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UmrahPackageController extends Controller
{
    public function index(Request $request): View
    {
        $packageTypes = array_map(
            fn (UmrahPackageType $type): string => $type->value,
            UmrahPackageType::cases(),
        );
        $requestedType = $request->string('type')->toString();
        $activeType = in_array($requestedType, $packageTypes, true) ? $requestedType : '';

        $requestedCountry = $request->string('country')->toString();
        $activeCountry = filled($requestedCountry) ? $requestedCountry : '';

        $countries = Country::query()
            ->active()
            ->whereHas('umrahPackages', fn ($query) => $query->active())
            ->get();

        $packages = UmrahPackage::query()
            ->active()
            ->with([
                'media',
                'prices',
                'privatePrices',
                'upcomingDepartures' => fn ($query) => $query->limit(3),
            ])
            ->when($activeType !== '', fn ($query) => $query->byType($activeType))
            ->when($activeCountry !== '', fn ($query) => $query->whereHas('countries', fn ($q) => $q->where('slug', $activeCountry)))
            ->latest()
            ->paginate(9)
            ->withQueryString();

        $alternateUrls = collect(['id', 'ms', 'en'])
            ->mapWithKeys(fn (string $locale): array => [
                $locale => route('umroh.index', array_filter([
                    'locale' => $locale,
                    'type' => $activeType,
                    'country' => $activeCountry,
                ])),
            ])
            ->all();
        $canonicalUrl = $alternateUrls[app()->getLocale()];
        $whatsappNumber = app(GeneralSettings::class)->whatsapp_number;

        return view('pages.umroh.index', compact(
            'activeCountry',
            'activeType',
            'alternateUrls',
            'canonicalUrl',
            'countries',
            'packages',
            'packageTypes',
            'whatsappNumber',
        ));
    }

    public function show(string $locale, string $umrah): View|RedirectResponse
    {
        $package = $this->findPackage($umrah);
        $canonicalSlug = $this->translatedSlug($package, $locale);

        if ($umrah !== $canonicalSlug) {
            return redirect()->route('umroh.show', [
                'locale' => $locale,
                'umrah' => $canonicalSlug,
            ], 301);
        }

        $package->load([
            'media',
            'prices',
            'privatePrices',
            'upcomingDepartures.prices',
            'includes',
            'itineraries',
            'testimonials',
        ]);

        $relatedPackages = UmrahPackage::query()
            ->active()
            ->whereKeyNot($package->id)
            ->with([
                'media',
                'prices',
                'privatePrices',
                'upcomingDepartures' => fn ($query) => $query->limit(1),
            ])
            ->orderByDesc('is_featured')
            ->latest()
            ->limit(3)
            ->get();

        $alternateUrls = collect(['id', 'ms', 'en'])
            ->mapWithKeys(fn (string $supportedLocale): array => [
                $supportedLocale => route('umroh.show', [
                    'locale' => $supportedLocale,
                    'umrah' => $this->translatedSlug($package, $supportedLocale),
                ]),
            ])
            ->all();
        $canonicalUrl = $alternateUrls[$locale];
        $schemaJson = json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $package->name,
            'description' => strip_tags((string) $package->description),
            'image' => [$package->thumbnail_url],
            'brand' => [
                '@type' => 'Brand',
                'name' => config('app.name'),
            ],
            'offers' => [
                '@type' => 'Offer',
                'url' => $canonicalUrl,
                'priceCurrency' => 'IDR',
                'price' => $package->starting_price_idr,
                'availability' => $package->has_availability
                    ? 'https://schema.org/InStock'
                    : 'https://schema.org/PreOrder',
            ],
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return view('pages.umroh.show', compact(
            'alternateUrls',
            'canonicalUrl',
            'package',
            'relatedPackages',
            'schemaJson',
        ));
    }

    private function findPackage(string $slug): UmrahPackage
    {
        return UmrahPackage::query()
            ->active()
            ->whereLocalizedSlug($slug)
            ->firstOrFail();
    }

    private function translatedSlug(UmrahPackage $package, string $locale): string
    {
        return $package->localizedSlug($locale);
    }
}
