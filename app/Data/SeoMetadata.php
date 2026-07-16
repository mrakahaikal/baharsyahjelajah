<?php

namespace App\Data;

final readonly class SeoMetadata
{
    public function __construct(
        public string $title,
        public ?string $description,
        public string $ogTitle,
        public ?string $ogDescription,
        public ?string $ogImage,
        public string $ogType,
        public string $canonicalUrl,
        public string $locale,
    ) {}
}
