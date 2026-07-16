<?php

namespace App\Http\Controllers;

use App\Models\VisaService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class VisaServiceController extends Controller
{
    public function index(string $locale): View
    {
        $alternateUrls = collect(['id', 'ms', 'en'])
            ->mapWithKeys(fn (string $supportedLocale): array => [
                $supportedLocale => route('visa.index', ['locale' => $supportedLocale]),
            ])
            ->all();
        $canonicalUrl = $alternateUrls[$locale];
        $services = VisaService::query()
            ->publiclyAvailable()
            ->with('country')
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->limit(12)
            ->get();
        $schemaJson = json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'CollectionPage',
            'name' => __('visa.seo.index_title'),
            'description' => __('visa.seo.index_description'),
            'url' => $canonicalUrl,
            'mainEntity' => [
                '@type' => 'ItemList',
                'itemListElement' => $services->values()->map(fn (VisaService $service, int $index): array => [
                    '@type' => 'ListItem',
                    'position' => $index + 1,
                    'name' => $service->name,
                    'url' => route('visa.show', [
                        'locale' => $locale,
                        'visaService' => $service->slug,
                    ]),
                ])->all(),
            ],
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return view('pages.visa.index', compact('alternateUrls', 'canonicalUrl', 'schemaJson'));
    }

    public function show(string $locale, string $visaService): View|RedirectResponse
    {
        $service = VisaService::query()
            ->publiclyAvailable()
            ->where(function (Builder $query) use ($visaService): void {
                $query->where('slug', $visaService);

                if (ctype_digit($visaService)) {
                    $query->orWhere('id', (int) $visaService);
                }
            })
            ->firstOrFail();

        if ($visaService !== $service->slug) {
            return redirect()->route('visa.show', [
                'locale' => $locale,
                'visaService' => $service->slug,
            ], 301);
        }

        $service->load(['country.media', 'items', 'media']);
        $relatedServices = VisaService::query()
            ->publiclyAvailable()
            ->whereKeyNot($service->getKey())
            ->with(['country.media', 'media'])
            ->orderByRaw('CASE WHEN country_id = ? THEN 0 ELSE 1 END', [$service->country_id])
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->limit(3)
            ->get();
        $groupedItems = $service->items->groupBy(fn ($item): string => $item->type->value);
        $alternateUrls = collect(['id', 'ms', 'en'])
            ->mapWithKeys(fn (string $supportedLocale): array => [
                $supportedLocale => route('visa.show', [
                    'locale' => $supportedLocale,
                    'visaService' => $service->slug,
                ]),
            ])
            ->all();
        $canonicalUrl = $alternateUrls[$locale];
        $description = Str::limit(strip_tags((string) ($service->summary ?: $service->description)), 160, '');
        $offer = $service->price_idr === null ? null : [
            '@type' => 'Offer',
            'url' => $canonicalUrl,
            'priceCurrency' => 'IDR',
            'price' => $service->price_idr,
            'availability' => 'https://schema.org/InStock',
        ];
        $serviceSchema = array_filter([
            '@type' => 'Service',
            '@id' => $canonicalUrl.'#service',
            'name' => $service->name,
            'description' => $description,
            'url' => $canonicalUrl,
            'image' => $service->gallery_urls->all(),
            'serviceType' => $service->visa_type,
            'areaServed' => ['@type' => 'Country', 'name' => $service->country->name],
            'provider' => ['@type' => 'Organization', 'name' => config('app.name')],
            'offers' => $offer,
        ]);
        $schemaJson = json_encode([
            '@context' => 'https://schema.org',
            '@graph' => [$serviceSchema],
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return view('pages.visa.show', compact(
            'alternateUrls',
            'canonicalUrl',
            'description',
            'groupedItems',
            'relatedServices',
            'schemaJson',
            'service',
        ));
    }
}
