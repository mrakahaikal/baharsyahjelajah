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
        $currentLocale = app()->getLocale();

        if (empty($this->alternateUrls) && request()->route()) {
            $routeName = request()->route()->getName();
            if ($routeName && in_array('locale', request()->route()->parameterNames(), true)) {
                $parameters = request()->route()->parameters();
                $supportedLocales = ['id', 'ms', 'en'];
                $generated = [];
                try {
                    foreach ($supportedLocales as $loc) {
                        $generated[$loc] = route($routeName, array_merge($parameters, ['locale' => $loc]));
                    }
                    $this->alternateUrls = $generated;
                } catch (\Throwable) {
                    // Fall back if route parameters cannot be resolved
                }
            }
        }

        if (blank($this->canonicalUrl)) {
            $this->canonicalUrl = $this->alternateUrls[$currentLocale] ?? url()->current();
        }

        if (blank($this->robots)) {
            $this->robots = 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1';
        }

        $this->metadata = $resolver->resolve(
            page: $page,
            locale: $currentLocale,
            fallbackTitle: $fallbackTitle,
            fallbackDescription: $fallbackDescription,
            fallbackOgImage: $fallbackOgImage,
            ogType: $ogType,
            canonicalUrl: $this->canonicalUrl,
            alternateUrls: $this->alternateUrls,
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
