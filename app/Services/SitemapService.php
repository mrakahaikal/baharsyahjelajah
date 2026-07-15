<?php

namespace App\Services;

use App\Models\Destination;
use App\Models\Post;
use App\Models\Tour;
use App\Models\TourPackage;
use App\Models\UmrahPackage;
use App\Models\Vehicle;
use App\Models\VisaService;
use Closure;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\SitemapIndex;
use Spatie\Sitemap\Tags\Sitemap as SitemapTag;
use Spatie\Sitemap\Tags\Url;

class SitemapService
{
    /** @var list<string> */
    private const array LOCALES = ['id', 'ms', 'en'];

    /** @var list<string> */
    private const array STATIC_ROUTE_NAMES = [
        'home',
        'blog.index',
        'tour.index',
        'umroh.index',
        'destination.index',
        'transport.index',
        'visa.index',
        'contact.index',
        'gallery.index',
        'testimonials.index',
    ];

    public function generate(): string
    {
        $generatedAt = now();
        $sitemapBuilders = [
            'sitemap-pages.xml' => fn (): Sitemap => $this->pagesSitemap(),
            'sitemap-posts.xml' => fn (): Sitemap => $this->postsSitemap(),
            'sitemap-tours.xml' => fn (): Sitemap => $this->toursSitemap(),
            'sitemap-umrah.xml' => fn (): Sitemap => $this->umrahSitemap(),
            'sitemap-destinations.xml' => fn (): Sitemap => $this->destinationsSitemap(),
            'sitemap-vehicles.xml' => fn (): Sitemap => $this->vehiclesSitemap(),
            'sitemap-visas.xml' => fn (): Sitemap => $this->visasSitemap(),
        ];
        $index = SitemapIndex::create();

        foreach ($sitemapBuilders as $filename => $buildSitemap) {
            $buildSitemap()->writeToFile(public_path($filename));
            $index->add(
                SitemapTag::create($this->absoluteUrl($filename))
                    ->setLastModificationDate($generatedAt),
            );
        }

        $sitemapPath = public_path('sitemap.xml');
        $index->writeToFile($sitemapPath);

        return $sitemapPath;
    }

    private function pagesSitemap(): Sitemap
    {
        $sitemap = Sitemap::create();

        foreach (self::STATIC_ROUTE_NAMES as $routeName) {
            $this->addLocalizedUrls(
                $sitemap,
                fn (string $locale): string => route($routeName, ['locale' => $locale], absolute: false),
            );
        }

        return $sitemap;
    }

    private function postsSitemap(): Sitemap
    {
        $sitemap = Sitemap::create();

        Post::query()
            ->published()
            ->select(['id', 'slug', 'updated_at'])
            ->lazyById()
            ->each(function (Post $post) use ($sitemap): void {
                if (blank($this->translatedSlug($post, 'id'))) {
                    return;
                }

                $this->addLocalizedUrls(
                    $sitemap,
                    fn (string $locale): string => route('blog.show', [
                        'locale' => $locale,
                        'post' => $this->translatedSlug($post, $locale),
                    ], absolute: false),
                    $post->updated_at,
                );
            });

        return $sitemap;
    }

    private function toursSitemap(): Sitemap
    {
        $sitemap = Sitemap::create();

        Tour::query()
            ->active()
            ->whereNull((new Tour)->qualifyColumn('deleted_at'))
            ->select(['id', 'slug', 'updated_at'])
            ->lazyById()
            ->each(function (Tour $tour) use ($sitemap): void {
                if (blank($this->translatedSlug($tour, 'id'))) {
                    return;
                }

                $this->addLocalizedUrls(
                    $sitemap,
                    fn (string $locale): string => route('tour.show', [
                        'locale' => $locale,
                        'tour' => $this->translatedSlug($tour, $locale),
                    ], absolute: false),
                    $tour->updated_at,
                );
            });

        TourPackage::query()
            ->whereHas('tour', fn (Builder $query): Builder => $query
                ->active()
                ->whereNull((new Tour)->qualifyColumn('deleted_at')))
            ->with('tour:id,slug,updated_at')
            ->select(['id', 'tour_id', 'slug', 'updated_at'])
            ->lazyById()
            ->each(function (TourPackage $package) use ($sitemap): void {
                $tour = $package->tour;

                if (blank($this->translatedSlug($tour, 'id')) || blank($this->translatedSlug($package, 'id'))) {
                    return;
                }

                $this->addLocalizedUrls(
                    $sitemap,
                    fn (string $locale): string => route('tour.package.show', [
                        'locale' => $locale,
                        'tour' => $this->translatedSlug($tour, $locale),
                        'package' => $this->translatedSlug($package, $locale),
                    ], absolute: false),
                    $package->updated_at->isAfter($tour->updated_at)
                        ? $package->updated_at
                        : $tour->updated_at,
                );
            });

        return $sitemap;
    }

    private function umrahSitemap(): Sitemap
    {
        $sitemap = Sitemap::create();

        UmrahPackage::query()
            ->active()
            ->select(['id', 'slug', 'updated_at'])
            ->lazyById()
            ->each(function (UmrahPackage $package) use ($sitemap): void {
                $this->addLocalizedUrls(
                    $sitemap,
                    fn (string $locale): string => route('umroh.show', [
                        'locale' => $locale,
                        'umrah' => $this->translatedSlug($package, $locale, fallbackToKey: true),
                    ], absolute: false),
                    $package->updated_at,
                );
            });

        return $sitemap;
    }

    private function destinationsSitemap(): Sitemap
    {
        $sitemap = Sitemap::create();

        Destination::query()
            ->active()
            ->select(['id', 'slug', 'updated_at'])
            ->lazyById()
            ->each(function (Destination $destination) use ($sitemap): void {
                if (blank($destination->slug)) {
                    return;
                }

                $this->addLocalizedUrls(
                    $sitemap,
                    fn (string $locale): string => route('destination.show', [
                        'locale' => $locale,
                        'destination' => $destination->slug,
                    ], absolute: false),
                    $destination->updated_at,
                );
            });

        return $sitemap;
    }

    private function vehiclesSitemap(): Sitemap
    {
        $sitemap = Sitemap::create();

        Vehicle::query()
            ->active()
            ->select(['id', 'slug', 'updated_at'])
            ->lazyById()
            ->each(function (Vehicle $vehicle) use ($sitemap): void {
                $this->addLocalizedUrls(
                    $sitemap,
                    fn (string $locale): string => route('transport.show', [
                        'locale' => $locale,
                        'vehicle' => $this->translatedSlug($vehicle, $locale, fallbackToKey: true),
                    ], absolute: false),
                    $vehicle->updated_at,
                );
            });

        return $sitemap;
    }

    private function visasSitemap(): Sitemap
    {
        $sitemap = Sitemap::create();

        VisaService::query()
            ->publiclyAvailable()
            ->select(['id', 'slug', 'updated_at'])
            ->lazyById()
            ->each(function (VisaService $service) use ($sitemap): void {
                if (blank($service->slug)) {
                    return;
                }

                $this->addLocalizedUrls(
                    $sitemap,
                    fn (string $locale): string => route('visa.show', [
                        'locale' => $locale,
                        'visaService' => $service->slug,
                    ], absolute: false),
                    $service->updated_at,
                );
            });

        return $sitemap;
    }

    /** @param Closure(string): string $urlResolver */
    private function addLocalizedUrls(
        Sitemap $sitemap,
        Closure $urlResolver,
        ?DateTimeInterface $lastModifiedAt = null,
    ): void {
        $urls = collect(self::LOCALES)
            ->mapWithKeys(fn (string $locale): array => [
                $locale => $this->absoluteUrl($urlResolver($locale)),
            ]);

        foreach ($urls as $url) {
            $tag = Url::create($url);

            if ($lastModifiedAt !== null) {
                $tag->setLastModificationDate($lastModifiedAt);
            }

            foreach ($urls as $alternateLocale => $alternateUrl) {
                $tag->addAlternate($alternateUrl, $alternateLocale);
            }

            $sitemap->add($tag);
        }
    }

    private function absoluteUrl(string $path): string
    {
        return Str::of((string) config('app.url'))
            ->rtrim('/')
            ->append('/')
            ->append(Str::of($path)->ltrim('/'))
            ->toString();
    }

    private function translatedSlug(
        Post|Tour|TourPackage|UmrahPackage|Vehicle $model,
        string $locale,
        bool $fallbackToKey = false,
    ): ?string {
        $slug = $model->getTranslation('slug', $locale, false)
            ?: $model->getTranslation('slug', 'id', false);

        if (filled($slug)) {
            return $slug;
        }

        return $fallbackToKey ? (string) $model->getKey() : null;
    }
}
