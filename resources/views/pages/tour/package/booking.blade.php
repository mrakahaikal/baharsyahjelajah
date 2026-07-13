@php
    $locale = app()->getLocale();
    $seoTitle = __('frontend.tour.booking.seo_title', ['package' => $package->name, 'brand' => config('app.name')]);
    $seoDescription = __('frontend.tour.booking.seo_description', ['package' => $package->name]);
@endphp

<x-layouts::app :title="$seoTitle" :meta-description="$seoDescription" robots="noindex, follow" :show-floating-whatsapp="false" :$canonicalUrl :$alternateUrls>
    <article class="bg-white">
        <header class="border-b border-slate-100 bg-slate-50">
            <div class="mx-auto max-w-7xl px-4 pb-10 sm:px-6 sm:pb-12 lg:px-8">
                <nav class="mb-6 text-sm text-slate-500" aria-label="Breadcrumb">
                    <ol class="flex min-w-0 items-center gap-2">
                        <li>
                            <a href="{{ route('tour.index', ['locale' => $locale]) }}" class="rounded-sm hover:text-slate-900 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-blue-600">
                                {{ __('frontend.tour.show.breadcrumb_tours') }}
                            </a>
                        </li>
                        <li aria-hidden="true"><x-lucide-chevron-right class="h-3.5 w-3.5" /></li>
                        <li>
                            <a href="{{ route('tour.package.show', ['locale' => $locale, 'tour' => $tour->slug, 'package' => $package->slug]) }}" class="max-w-40 truncate rounded-sm hover:text-slate-900 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-blue-600 sm:max-w-none">
                                {{ $package->name }}
                            </a>
                        </li>
                        <li aria-hidden="true"><x-lucide-chevron-right class="h-3.5 w-3.5" /></li>
                        <li class="font-medium text-slate-900" aria-current="page">{{ __('frontend.tour.booking.breadcrumb') }}</li>
                    </ol>
                </nav>

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
