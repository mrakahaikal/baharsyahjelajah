<?php

namespace App\Services;

use App\Data\SeoMetadata;
use App\Enums\StaticSeoPage;
use App\Settings\SeoSettings;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class SeoMetadataResolver
{
    public function __construct(private readonly SeoSettings $settings) {}

    public function resolve(
        StaticSeoPage|string|null $page,
        string $locale,
        ?string $fallbackTitle = null,
        ?string $fallbackDescription = null,
        ?string $fallbackOgImage = null,
        ?string $ogType = null,
        ?string $canonicalUrl = null,
    ): SeoMetadata {
        $page = is_string($page) ? StaticSeoPage::tryFrom($page) : $page;
        $pageSettings = $page ? data_get($this->settings->pages, $page->value, []) : [];
        $fallbackLocale = (string) config('app.fallback_locale', 'id');

        $title = $this->firstFilled(
            $page ? data_get($pageSettings, "title.{$locale}") : null,
            $page ? data_get($pageSettings, "title.{$fallbackLocale}") : null,
            $fallbackTitle,
            data_get($this->settings->og_title, $locale),
            data_get($this->settings->og_title, $fallbackLocale),
            config('app.name'),
        );
        $description = $this->firstFilled(
            $page ? data_get($pageSettings, "description.{$locale}") : null,
            $page ? data_get($pageSettings, "description.{$fallbackLocale}") : null,
            $fallbackDescription,
            data_get($this->settings->og_description, $locale),
            data_get($this->settings->og_description, $fallbackLocale),
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
        );

        return new SeoMetadata(
            title: $title ?? (string) config('app.name'),
            description: $description,
            ogTitle: $ogTitle ?? $title ?? (string) config('app.name'),
            ogDescription: $ogDescription,
            ogImage: $ogImage,
            ogType: $ogType ?: 'website',
            canonicalUrl: $canonicalUrl ?: url()->current(),
            locale: $locale,
        );
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
