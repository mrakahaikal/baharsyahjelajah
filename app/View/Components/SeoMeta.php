<?php

namespace App\View\Components;

use App\Data\SeoMetadata;
use App\Services\SeoMetadataResolver;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class SeoMeta extends Component
{
    public SeoMetadata $metadata;

    /** @param array<string, string> $alternateUrls */
    public function __construct(
        SeoMetadataResolver $resolver,
        public ?string $page = null,
        public ?string $fallbackTitle = null,
        public ?string $fallbackDescription = null,
        public ?string $fallbackOgImage = null,
        public ?string $ogType = null,
        public ?string $canonicalUrl = null,
        public array $alternateUrls = [],
        public ?string $robots = null,
    ) {
        $this->metadata = $resolver->resolve(
            page: $page,
            locale: app()->getLocale(),
            fallbackTitle: $fallbackTitle,
            fallbackDescription: $fallbackDescription,
            fallbackOgImage: $fallbackOgImage,
            ogType: $ogType,
            canonicalUrl: $canonicalUrl,
        );
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.seo-meta');
    }
}
