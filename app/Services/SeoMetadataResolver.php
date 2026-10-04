<?php

namespace App\Services;

use App\Data\SeoMetadata;
use App\Enums\StaticSeoPage;
use App\Filament\Support\FilamentLocale;
use App\Settings\GeneralSettings;
use App\Settings\SeoSettings;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class SeoMetadataResolver
{
    public function __construct(
        private readonly SeoSettings $settings,
        private readonly ?GeneralSettings $generalSettings = null,
    ) {}

    /**
     * @param  array<string, string>  $alternateUrls
     */
    public function resolve(
        StaticSeoPage|string|null $page,
        string $locale,
        ?string $fallbackTitle = null,
        ?string $fallbackDescription = null,
        ?string $fallbackOgImage = null,
        ?string $ogType = null,
        ?string $canonicalUrl = null,
        array $alternateUrls = [],
    ): SeoMetadata {
        $page = is_string($page) ? StaticSeoPage::tryFrom($page) : $page;
        $pageSettings = $page ? data_get($this->settings->pages, $page->value, []) : [];
        $fallbackLocale = (string) config('app.fallback_locale', 'id');

        $siteName = $this->firstFilled(
            $this->generalSettings ? data_get($this->generalSettings->site_name, $locale) : null,
            $this->generalSettings ? data_get($this->generalSettings->site_name, $fallbackLocale) : null,
            config('app.name'),
        ) ?? (string) config('app.name');

        $title = $this->firstFilled(
            $page ? data_get($pageSettings, "title.{$locale}") : null,
            $page ? data_get($pageSettings, "title.{$fallbackLocale}") : null,
            $fallbackTitle,
            data_get($this->settings->og_title, $locale),
            data_get($this->settings->og_title, $fallbackLocale),
            $siteName,
        );
        $description = $this->firstFilled(
            $page ? data_get($pageSettings, "description.{$locale}") : null,
            $page ? data_get($pageSettings, "description.{$fallbackLocale}") : null,
            $fallbackDescription,
            data_get($this->settings->og_description, $locale),
            data_get($this->settings->og_description, $fallbackLocale),
            $this->generalSettings ? data_get($this->generalSettings->meta_description, $locale) : null,
            $this->generalSettings ? data_get($this->generalSettings->meta_description, $fallbackLocale) : null,
        );
        $ogTitle = $this->firstFilled(
            $page ? data_get($pageSettings, "og_title.{$locale}") : null,
            $page ? data_get($pageSettings, "og_title.{$fallbackLocale}") : null,
            $title,
        );
        $ogDescription = $this->firstFilled(
            $page ? data_get($pageSettings, "og_description.{$locale}") : null,
            $page ? data_get($pageSettings, "og_description.{$fallbackLocale}") : null,
            $description,
        );
        $pageImage = $page ? data_get($pageSettings, 'og_image') : null;
        $ogImage = $this->firstFilled(
            $this->storedImageUrl($pageImage),
            $this->absoluteUrl($fallbackOgImage),
            $this->storedImageUrl($this->settings->og_image),
            $this->defaultOgImage(),
        );

        $ogLocale = SeoMetadata::toOgLocale($locale);

        $supportedLocales = ! empty($alternateUrls)
            ? array_keys($alternateUrls)
            : FilamentLocale::supported();

        $ogLocaleAlternates = collect($supportedLocales)
            ->reject(fn (string $loc): bool => strtolower($loc) === strtolower($locale))
            ->map(fn (string $loc): string => SeoMetadata::toOgLocale($loc))
            ->unique()
            ->values()
            ->all();

        $imageType = $this->inferImageType($ogImage);

        return new SeoMetadata(
            title: $title ?? $siteName,
            description: $description,
            ogTitle: $ogTitle ?? $title ?? $siteName,
            ogDescription: $ogDescription,
            ogImage: $ogImage,
            ogType: $ogType ?: 'website',
            canonicalUrl: $canonicalUrl ?: url()->current(),
            locale: $locale,
            ogLocale: $ogLocale,
            ogLocaleAlternates: $ogLocaleAlternates,
            siteName: $siteName,
            ogImageWidth: 1200,
            ogImageHeight: 630,
            ogImageType: $imageType,
        );
    }

    private function defaultOgImage(): ?string
    {
        if (file_exists(public_path('images/og-default.png'))) {
            return $this->absoluteUrl('/images/og-default.png');
        }

        if (file_exists(public_path('images/logo-baharsyah-jelajah.png'))) {
            return $this->absoluteUrl('/images/logo-baharsyah-jelajah.png');
        }

        return null;
    }

    private function inferImageType(?string $url): string
    {
        if (blank($url)) {
            return 'image/png';
        }

        $extension = strtolower(pathinfo((string) parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));

        return match ($extension) {
            'jpg', 'jpeg' => 'image/jpeg',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            default => 'image/png',
        };
    }

    private function firstFilled(?string ...$values): ?string
    {
        foreach ($values as $value) {
            if (filled($value)) {
                return trim($value);
            }
        }

        return null;
    }

    private function storedImageUrl(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        return $this->absoluteUrl(Storage::disk('public')->url($path));
    }

    private function absoluteUrl(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        return Str::startsWith($path, ['http://', 'https://']) ? $path : url($path);
    }
}
