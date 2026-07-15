<?php

namespace App\Http\Controllers;

use App\Enums\UmrahPackageType;
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

        $packages = UmrahPackage::query()
            ->active()
            ->with([
                'media',
                'prices',
                'upcomingDepartures' => fn ($query) => $query->limit(3),
            ])
            ->when($activeType !== '', fn ($query) => $query->byType($activeType))
            ->latest()
            ->paginate(9)
            ->withQueryString();

        $alternateUrls = collect(['id', 'ms', 'en'])
            ->mapWithKeys(fn (string $locale): array => [
                $locale => route('umroh.index', array_filter([
                    'locale' => $locale,
                    'type' => $activeType,
                ])),
            ])
            ->all();
        $canonicalUrl = $alternateUrls[app()->getLocale()];
        $whatsappNumber = app(GeneralSettings::class)->whatsapp_number;

        return view('pages.umroh.index', compact(
            'activeType',
            'alternateUrls',
            'canonicalUrl',
            'packages',
            'packageTypes',
            'whatsappNumber',
        ));
    }

    public function show(string $locale, string $umrah): View|RedirectResponse
    {
        $package = $this->findPackage($umrah);
        $canonicalSlug = $package->slug ?: (string) $package->getKey();

        if ($umrah !== $canonicalSlug) {
            return redirect()->route('umroh.show', [
                'locale' => $locale,
                'umrah' => $canonicalSlug,
            ], 301);
        }

        $package->load([
            'media',
            'prices',
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
                    'umrah' => $package->getTranslation('slug', $supportedLocale, false)
                        ?: $package->getTranslation('slug', 'id', false)
                        ?: $package->getKey(),
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
            ->where(function ($query) use ($slug): void {
                $query->where('slug->'.app()->getLocale(), $slug)
                    ->orWhere('slug->id', $slug);

                if (ctype_digit($slug)) {
                    $query->orWhereKey((int) $slug);
                }
            })
            ->firstOrFail();
    }
}
