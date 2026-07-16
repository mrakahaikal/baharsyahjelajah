@php
    $locale = app()->getLocale();
    $galleryImages = $destination->gallery_urls
        ->map(fn ($url) => ['src' => $url, 'alt' => $destination->name])
        ->values();
    $hasRelatedContent = $tourPackages->isNotEmpty() || $umrahPackages->isNotEmpty() || $posts->isNotEmpty();
@endphp

<x-layouts::app
    :title="__('destination.seo.show_title', ['destination' => $destination->name])"
    :meta-description="$description"
    :og-image="$destination->cover_url"
    :$schemaJson
    breadcrumb-name="destination.show"
    :breadcrumb-parameters="[$locale, $destination]"
    :$canonicalUrl
    :$alternateUrls>
    <div class="border-b border-slate-200 bg-white">
        <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8">
            <x-ui.breadcrumbs name="destination.show" :parameters="[$locale, $destination]" />
        </div>
    </div>

    <section class="relative isolate overflow-hidden bg-slate-950 text-white" aria-labelledby="destination-heading">
        @if($destination->cover_url)
            <img src="{{ $destination->cover_url }}" alt="{{ $destination->name }}" width="1800" height="1000" fetchpriority="high" class="absolute inset-0 h-full w-full object-cover">
            <div class="absolute inset-0 bg-linear-to-r from-slate-950 via-slate-950/75 to-slate-950/20"></div>
            <div class="absolute inset-0 bg-linear-to-t from-slate-950/70 via-transparent to-slate-950/15"></div>
        @endif
        <div class="relative mx-auto flex min-h-95 max-w-7xl items-end px-4 py-12 sm:min-h-115 sm:px-6 sm:py-16 lg:px-8">
            <div class="max-w-3xl">
                <p class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-[0.18em] text-blue-300">
                    <x-lucide-map-pin class="h-4 w-4" aria-hidden="true" />
                    {{ $destination->location ?: __('destination.show.eyebrow') }}
                </p>
                <h1 id="destination-heading" class="mt-4 text-balance text-4xl font-extrabold leading-tight sm:text-5xl lg:text-6xl">{{ $destination->name }}</h1>
                @if(filled($destination->description))
                    <p class="mt-5 max-w-2xl text-pretty text-base leading-8 text-slate-200 sm:text-lg">
                        {{ str(strip_tags((string) $destination->description))->limit(210) }}
                    </p>
                @endif
            </div>
        </div>
    </section>

    <section class="bg-white py-14 sm:py-20" aria-labelledby="destination-overview-heading">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-10 lg:grid-cols-[minmax(0,1fr)_20rem] lg:items-start">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-600">{{ __('destination.show.overview_eyebrow') }}</p>
                    <h2 id="destination-overview-heading" class="mt-2 text-2xl font-extrabold text-slate-950 sm:text-3xl">{{ __('destination.show.overview_title', ['destination' => $destination->name]) }}</h2>
                    <p class="mt-5 max-w-3xl whitespace-pre-line text-sm leading-7 text-slate-600 sm:text-base">{{ $destination->description ?: __('destination.show.fallback_description', ['name' => $destination->name]) }}</p>
                </div>
                <aside class="border-y border-slate-200 py-5 lg:border lg:p-6" aria-label="{{ __('destination.show.location_title') }}">
                    <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">{{ __('destination.show.location_title') }}</p>
                    <p class="mt-2 font-bold text-slate-950">{{ $destination->location ?: $destination->name }}</p>
                    @if($destination->safe_map_url)
                        <x-ui::button tag="a" href="{{ $destination->safe_map_url }}" target="_blank" rel="noopener noreferrer" variant="outline" class="mt-5 w-full">
                            <x-slot:icon><x-lucide-map /></x-slot:icon>
                            {{ __('destination.show.open_map') }}
                        </x-ui::button>
                    @endif
                </aside>
            </div>

            @if($galleryImages->isNotEmpty())
                <div class="mt-12">
                    <x-ui.lightbox-gallery :images="$galleryImages" :alt="$destination->name" :label="__('destination.show.gallery')" />
                </div>
            @endif
        </div>
    </section>

    @if($tourPackages->isNotEmpty())
        <section class="bg-slate-50 py-14 sm:py-20" aria-labelledby="destination-tours-heading">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-600">{{ __('destination.show.tours_eyebrow') }}</p>
                <h2 id="destination-tours-heading" class="mt-2 text-2xl font-extrabold text-slate-950 sm:text-3xl">{{ __('destination.show.tours_title', ['destination' => $destination->name]) }}</h2>
                <div class="mt-8 grid gap-6" data-destination-tour-layout="single-column">
                    @foreach($tourPackages as $package)
                        <x-ui.tour-package-card :$package :tour="$package->tour" :$locale />
                    @endforeach
                </div>
                <div class="mt-10">{{ $tourPackages->links() }}</div>
            </div>
        </section>
    @endif

    @if($umrahPackages->isNotEmpty())
        <section class="bg-neutral-950 py-14 text-white sm:py-20" aria-labelledby="destination-umrah-heading">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-amber-300">{{ __('destination.show.umrah_eyebrow') }}</p>
                <h2 id="destination-umrah-heading" class="mt-2 text-2xl font-extrabold sm:text-3xl">{{ __('destination.show.umrah_title', ['destination' => $destination->name]) }}</h2>
                <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($umrahPackages as $package)
                        <x-ui.umrah-package-card :$package :$locale :$whatsappNumber dark />
                    @endforeach
                </div>
                <div class="mt-10 [&_a]:text-white [&_span]:text-white">{{ $umrahPackages->links() }}</div>
            </div>
        </section>
    @endif

    @if($posts->isNotEmpty())
        <section class="bg-white py-14 sm:py-20" aria-labelledby="destination-articles-heading">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-emerald-700">{{ __('destination.show.articles_eyebrow') }}</p>
                <h2 id="destination-articles-heading" class="mt-2 text-2xl font-extrabold text-slate-950 sm:text-3xl">{{ __('destination.show.articles_title', ['destination' => $destination->name]) }}</h2>
                <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($posts as $post)
                        <x-ui.post-card :$post :$locale />
                    @endforeach
                </div>
                <div class="mt-10">{{ $posts->links() }}</div>
            </div>
        </section>
    @endif

    @unless($hasRelatedContent)
        <section class="bg-slate-50 py-14 sm:py-20">
            <div class="mx-auto max-w-3xl px-4 text-center sm:px-6">
                <x-lucide-route class="mx-auto h-10 w-10 text-slate-300" aria-hidden="true" />
                <h2 class="mt-4 text-xl font-bold text-slate-950">{{ __('destination.show.empty_title') }}</h2>
                <p class="mt-2 text-sm leading-7 text-slate-500">{{ __('destination.show.empty_text') }}</p>
                <x-ui::button tag="a" href="{{ route('tour.index', ['locale' => $locale, 'place' => $destination->slug]) }}" class="mt-6">
                    {{ __('destination.show.explore_tours') }}
                    <x-slot:trailingIcon><x-lucide-arrow-right /></x-slot:trailingIcon>
                </x-ui::button>
            </div>
        </section>
    @endunless
</x-layouts::app>
