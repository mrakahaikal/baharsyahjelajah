<?php

namespace App\Data;

final readonly class SeoMetadata
{
    /**
     * @param  array<int, string>  $ogLocaleAlternates
     */
    public function __construct(
        public string $title,
        public ?string $description,
        public string $ogTitle,
        public ?string $ogDescription,
        public ?string $ogImage,
        public string $ogType,
        public string $canonicalUrl,
        public string $locale,
        public string $ogLocale = 'id_ID',
        public array $ogLocaleAlternates = [],
        public ?string $siteName = null,
        public int $ogImageWidth = 1200,
        public int $ogImageHeight = 630,
        public string $ogImageType = 'image/png',
    ) {}

    public static function toOgLocale(string $locale): string
    {
        return match (strtolower(trim($locale))) {
            'id' => 'id_ID',
            'ms' => 'ms_MY',
            'en' => 'en_US',
            default => str_contains($locale, '-') ? str_replace('-', '_', $locale) : $locale,
        };
    }
}
