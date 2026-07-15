@props(['locale', 'heroBanner' => null])

@php
    $heroImageUrl = $heroBanner?->image_url
        ?? 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1800&q=85';
    $heroTitle = filled($heroBanner?->title) ? $heroBanner->title : __('home.hero.title');
    $heroSubtitle = filled($heroBanner?->subtitle) ? $heroBanner->subtitle : __('home.hero.subtitle');
    $primaryCtaUrl = $heroBanner?->ctaUrl($locale) ?? '#search-panel';
    $primaryCtaLabel = filled($heroBanner?->cta_label) ? $heroBanner->cta_label : __('home.hero.search');
    $opensInNewTab = $heroBanner?->opensCtaInNewTab() ?? false;
@endphp

<section class="relative isolate min-h-150 overflow-hidden bg-slate-950 text-white sm:min-h-155 lg:min-h-170" aria-labelledby="home-hero-heading">
    <img
        src="{{ $heroImageUrl }}"
        alt=""
        width="1800"
        height="1100"
        fetchpriority="high"
        class="absolute inset-0 h-full w-full object-cover"
    >
    <div class="absolute inset-0 bg-linear-to-r from-slate-950 via-slate-950/82 to-slate-950/25"></div>
    <div class="absolute inset-0 bg-linear-to-t from-slate-950/55 via-transparent to-transparent"></div>

    <div class="relative mx-auto flex min-h-150 max-w-7xl items-center px-4 pb-28 pt-24 sm:min-h-155 sm:px-6 lg:min-h-170 lg:px-8 lg:pb-32">
        <div class="max-w-3xl">
            <p class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-[0.18em] text-blue-200">
                <x-lucide-shield-check class="h-4 w-4" aria-hidden="true" />
                {{ __('home.hero.eyebrow') }}
            </p>

            <h1 id="home-hero-heading" class="mt-5 max-w-2xl text-balance text-4xl font-extrabold leading-tight sm:text-5xl lg:text-6xl">
                {{ $heroTitle }}
            </h1>

            <p class="mt-5 max-w-2xl text-pretty text-base leading-8 text-slate-200 sm:text-lg">
                {{ $heroSubtitle }}
            </p>

            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                <x-ui::button
                    tag="a"
                    href="{{ $primaryCtaUrl }}"
                    :target="$opensInNewTab ? '_blank' : null"
                    :rel="$opensInNewTab ? 'noopener noreferrer' : null"
                    variant="secondary"
                    size="lg"
                    class="shadow-lg shadow-blue-950/25 hover:-translate-y-0.5">
                    {{ $primaryCtaLabel }}
                    <x-slot:trailingIcon><x-lucide-arrow-right /></x-slot:trailingIcon>
                </x-ui::button>
                <x-ui::button tag="a" href="{{ route('contact.index', ['locale' => $locale]) }}" variant="inverse" size="lg" class="backdrop-blur-sm">
                    <x-slot:icon><x-lucide-message-circle /></x-slot:icon>
                    {{ __('home.hero.consult') }}
                </x-ui::button>
            </div>
        </div>
    </div>
</section>
