@props(['banner', 'locale'])

@php
    $ctaUrl = $banner->ctaUrl($locale);
    $opensInNewTab = $banner->opensCtaInNewTab();
@endphp

<section class="relative isolate min-h-90 overflow-hidden bg-neutral-950 text-white sm:min-h-105" aria-labelledby="home-promo-heading">
    <img
        src="{{ $banner->image_url }}"
        alt=""
        width="1800"
        height="720"
        loading="lazy"
        class="absolute inset-0 h-full w-full object-cover"
    >
    <div class="absolute inset-0 bg-linear-to-r from-neutral-950 via-neutral-950/80 to-neutral-950/20"></div>
    <div class="absolute inset-0 bg-linear-to-t from-neutral-950/60 via-transparent to-transparent"></div>

    <div class="relative mx-auto flex min-h-90 max-w-7xl items-end px-4 py-12 sm:min-h-105 sm:items-center sm:px-6 sm:py-16 lg:px-8">
        <div class="max-w-2xl">
            <p class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-[0.18em] text-amber-300">
                <x-lucide-sparkles class="h-4 w-4" aria-hidden="true" />
                {{ __('home.featured.eyebrow') }}
            </p>
            <h2 id="home-promo-heading" class="mt-4 text-balance text-3xl font-extrabold leading-tight sm:text-4xl">
                {{ $banner->title }}
            </h2>
            @if(filled($banner->subtitle))
                <p class="mt-4 max-w-xl text-pretty text-sm leading-7 text-neutral-200 sm:text-base">
                    {{ $banner->subtitle }}
                </p>
            @endif
            @if($ctaUrl && filled($banner->cta_label))
                <x-ui::button
                    tag="a"
                    href="{{ $ctaUrl }}"
                    :target="$opensInNewTab ? '_blank' : null"
                    :rel="$opensInNewTab ? 'noopener noreferrer' : null"
                    variant="gold-outline"
                    class="mt-7">
                    {{ $banner->cta_label }}
                    <x-slot:trailingIcon><x-lucide-arrow-right /></x-slot:trailingIcon>
                </x-ui::button>
            @endif
        </div>
    </div>
</section>
