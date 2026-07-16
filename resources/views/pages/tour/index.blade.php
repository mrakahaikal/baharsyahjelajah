@php
    $locale = app()->getLocale();
    $seoTitle = __('frontend.tour.index.seo_title', ['brand' => config('app.name')]);
    $seoDescription = __('frontend.tour.index.seo_description');
@endphp

<x-layouts::app :title="$seoTitle" :meta-description="$seoDescription" :$canonicalUrl :$alternateUrls breadcrumb-name="tour.index" :breadcrumb-parameters="[$locale]">
    <div class="border-b border-slate-200 bg-white">
        <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8">
            <x-ui.breadcrumbs name="tour.index" :parameters="[$locale]" />
        </div>
    </div>

    <livewire:tour-catalog :$heroImageUrl :$heroImageAlt />
</x-layouts::app>
