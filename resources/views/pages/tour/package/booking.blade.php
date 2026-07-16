@php
    $locale = app()->getLocale();
    $seoTitle = __('frontend.tour.booking.seo_title', ['package' => $package->name, 'brand' => config('app.name')]);
    $seoDescription = __('frontend.tour.booking.seo_description', ['package' => $package->name]);
@endphp

<x-layouts::app :title="$seoTitle" :meta-description="$seoDescription" robots="noindex, follow" :show-floating-whatsapp="false" :$canonicalUrl :$alternateUrls breadcrumb-name="tour.package.booking" :breadcrumb-parameters="[$locale, $tour, $package]">
    <article class="bg-white">
        <header class="border-b border-slate-100 bg-slate-50">
            <div class="mx-auto max-w-7xl px-4 pb-10 sm:px-6 sm:pb-12 lg:px-8">
                <x-ui.breadcrumbs name="tour.package.booking" :parameters="[$locale, $tour, $package]" class="mb-6" />

                <div class="max-w-3xl">
                    <p class="text-sm font-semibold uppercase text-blue-600">{{ __('frontend.tour.booking.eyebrow') }}</p>
                    <h1 class="mt-3 text-3xl font-extrabold text-balance text-slate-900 sm:text-4xl lg:text-5xl">{{ __('frontend.tour.booking.title') }}</h1>
                    <p class="mt-4 max-w-2xl text-base leading-8 text-slate-600">{{ __('frontend.tour.booking.description') }}</p>
                </div>
            </div>
        </header>

        <section class="py-12 sm:py-14" aria-label="{{ __('frontend.tour.booking.form_label') }}">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <livewire:tour-booking-form :$package :$initialTierId :$initialPax />
            </div>
        </section>
    </article>
</x-layouts::app>
