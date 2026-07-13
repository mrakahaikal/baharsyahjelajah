@php
    $seoTitle = __('frontend.tour.index.seo_title', ['brand' => config('app.name')]);
    $seoDescription = __('frontend.tour.index.seo_description');
@endphp

<x-layouts::app :title="$seoTitle" :meta-description="$seoDescription" :$canonicalUrl :$alternateUrls>
    <livewire:tour-catalog :$heroImageUrl :$heroImageAlt />
</x-layouts::app>
