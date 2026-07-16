@php
    $locale = app()->getLocale();
    $cover = $package->getFirstMedia(\App\Models\TourPackage::MEDIA_COLLECTION_COVER);
    $galleryImages = collect();

    if ($cover) {
        $galleryImages->push(['src' => $cover->getUrl(), 'alt' => $package->name, 'caption' => $package->name]);
    }

    foreach ($package->getMedia(\App\Models\TourPackage::MEDIA_COLLECTION_GALLERY) as $media) {
        $caption = $package->localizedMediaCaption($media);
        $galleryImages->push(['src' => $media->getUrl(), 'alt' => $caption ?: $package->name, 'caption' => $caption]);
    }

    $includedItems = $package->includes->where('type', 'include');
    $excludedItems = $package->includes->where('type', 'exclude');
    $notes = $package->includes->where('type', 'note');
    $allPrices = $package->tiers->flatMap(fn ($tier) => $tier->priceTiers);
    $priceCurrencies = $allPrices->pluck('currency')->unique();
    $packageUrl = route('tour.package.show', ['locale' => $locale, 'tour' => $tour->slug, 'package' => $package->slug]);
    $seoDescription = \Illuminate\Support\Str::limit(strip_tags((string) ($tour->short_description ?: $tour->description)), 155);
    $productSchema = [
        '@type' => 'Product',
        'name' => $package->name,
        'description' => $seoDescription,
        'url' => $packageUrl,
        'brand' => ['@type' => 'Brand', 'name' => config('app.name')],
    ];

    if ($galleryImages->isNotEmpty()) {
        $productSchema['image'] = $galleryImages->pluck('src')->all();
    }

    if ($allPrices->isNotEmpty() && $priceCurrencies->count() === 1) {
        $productSchema['offers'] = [
            '@type' => 'AggregateOffer',
            'lowPrice' => (float) $allPrices->min('price'),
            'highPrice' => (float) $allPrices->max('price'),
            'priceCurrency' => $priceCurrencies->first(),
            'offerCount' => $allPrices->count(),
            'availability' => 'https://schema.org/InStock',
        ];
    }

    $schemaJson = json_encode([
        '@context' => 'https://schema.org',
        '@graph' => [$productSchema],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
@endphp

<x-layouts::app :title="$package->name.' | '.$tour->name" :meta-description="$seoDescription" :schema-json="$schemaJson" :$canonicalUrl :$alternateUrls breadcrumb-name="tour.package.show" :breadcrumb-parameters="[$locale, $tour, $package]">
    <article class="bg-white">
        <header class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <x-ui.breadcrumbs name="tour.package.show" :parameters="[$locale, $tour, $package]" class="mb-5" />
        </header>

        @if($galleryImages->isNotEmpty())
            <section class="mx-auto max-w-7xl px-4 py-5 sm:px-6 lg:px-8" aria-label="{{ __('frontend.tour.gallery') }}">
                <x-ui.lightbox-gallery :images="$galleryImages" :alt="$package->name" :label="__('frontend.tour.gallery')" />
            </section>
        @else
            <section class="mx-auto max-w-7xl px-4 py-5 sm:px-6 lg:px-8" aria-label="{{ __('frontend.tour.gallery') }}">
                <img src="{{ $package->cover_url }}" alt="{{ $package->name }}" width="1200" height="675" class="aspect-video w-full rounded-lg object-cover">
            </section>
        @endif

        <div class="sticky top-20 z-20 border-y border-slate-100 bg-white/95 backdrop-blur">
            <nav class="mx-auto flex max-w-7xl gap-2 overflow-x-auto px-4 py-3 text-sm font-semibold text-slate-600 sm:px-6 lg:px-8" aria-label="Package sections">
                <a href="#overview" class="min-w-max rounded-full px-3 py-1.5 hover:bg-slate-100">{{ __('frontend.tour.overview') }}</a>
                <a href="#itinerary" class="min-w-max rounded-full px-3 py-1.5 hover:bg-slate-100">{{ __('frontend.tour.itinerary') }}</a>
                <a href="#includes" class="min-w-max rounded-full px-3 py-1.5 hover:bg-slate-100">{{ __('frontend.tour.whats_included') }}</a>
                <a href="#pricing" class="min-w-max rounded-full px-3 py-1.5 hover:bg-slate-100">{{ __('frontend.tour.package.pricing_nav') }}</a>
            </nav>
        </div>

        <div class="mx-auto grid max-w-7xl gap-10 px-4 py-12 sm:px-6 lg:grid-cols-[1fr_22rem] lg:px-8">
            <div>
                <section id="overview" class="scroll-mt-36">
                    <p class="text-sm font-semibold uppercase text-blue-600">{{ $tour->name }}</p>
                    <h1 class="mt-3 text-4xl font-extrabold text-balance text-slate-900 lg:text-5xl">{{ $package->name }}</h1>
                    <p class="mt-5 max-w-3xl text-base leading-8 text-slate-500">{{ $tour->short_description }}</p>

                    <div class="mt-7 grid grid-cols-2 gap-3 sm:grid-cols-3">
                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                            <x-lucide-clock class="h-5 w-5 text-blue-600" aria-hidden="true" />
                            <p class="mt-3 text-xs text-slate-500">{{ __('frontend.tour.duration') }}</p>
                            <p class="mt-1 font-bold text-slate-900">{{ $package->duration_label }}</p>
                        </div>
                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                            <x-lucide-layers class="h-5 w-5 text-blue-600" aria-hidden="true" />
                            <p class="mt-3 text-xs text-slate-500">{{ __('frontend.tour.package.tier') }}</p>
                            <p class="mt-1 font-bold text-slate-900">{{ trans_choice('frontend.tour.package.tier_count', $package->tiers->count(), ['count' => $package->tiers->count()]) }}</p>
                        </div>
                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                            <x-lucide-map class="h-5 w-5 text-blue-600" aria-hidden="true" />
                            <p class="mt-3 text-xs text-slate-500">{{ __('frontend.tour.package.itinerary_days') }}</p>
                            <p class="mt-1 font-bold text-slate-900">{{ trans_choice('frontend.tour.package.day_count', $package->itineraries->count(), ['count' => $package->itineraries->count()]) }}</p>
                        </div>
                    </div>
                </section>

                <section id="itinerary" class="scroll-mt-36 pt-12" x-data="{ activeDay: {{ $package->itineraries->first()?->day_number ?? 'null' }} }">
                    <h2 class="text-2xl font-extrabold text-slate-900">{{ __('frontend.tour.itinerary') }}</h2>
                    @forelse($package->itineraries as $itinerary)
                        <div class="border-b border-slate-100 py-5">
                            <button type="button" class="flex w-full items-start justify-between gap-4 text-left" @click="activeDay = activeDay === {{ $itinerary->day_number }} ? null : {{ $itinerary->day_number }}" :aria-expanded="activeDay === {{ $itinerary->day_number }}">
                                <span><span class="text-sm font-semibold text-blue-600">{{ __('frontend.tour.day') }} {{ $itinerary->day_number }}</span><span class="mt-1 block text-lg font-bold text-slate-900">{{ $itinerary->title }}</span></span>
                                <x-lucide-chevron-down class="h-5 w-5 text-slate-400" aria-hidden="true" />
                            </button>
                            <div x-show="activeDay === {{ $itinerary->day_number }}" x-collapse class="pt-4">
                                <div class="text-sm leading-7 text-slate-600">{!! $itinerary->description !!}</div>

                                @if($itinerary->destinations->isNotEmpty())
                                    <div class="mt-5 grid max-w-2xl grid-cols-2 gap-3 sm:grid-cols-3">
                                        @foreach($itinerary->destinations as $destination)
                                            <x-ui.destination-highlight-card :$destination data-destination-card />
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="mt-4 text-sm text-slate-500">{{ __('frontend.tour.package.itinerary_empty') }}</p>
                    @endforelse
                </section>

                <section id="includes" class="scroll-mt-36 pt-12">
                    <div class="grid gap-6 md:grid-cols-2">
                        <div>
                            <h2 class="text-2xl font-extrabold text-slate-900">{{ __('frontend.tour.whats_included') }}</h2>
                            <ul class="mt-5 space-y-3 rounded-lg border border-slate-200 p-5">
                                @forelse($includedItems as $item)<li class="flex gap-3 text-sm text-slate-700"><x-lucide-check class="h-5 w-5 shrink-0 text-green-600" />{{ $item->item }}</li>@empty<li class="text-sm text-slate-500">{{ __('frontend.tour.no_items') }}</li>@endforelse
                            </ul>
                        </div>
                        <div>
                            <h2 class="text-2xl font-extrabold text-slate-900">{{ __('frontend.tour.whats_excluded') }}</h2>
                            <ul class="mt-5 space-y-3 rounded-lg border border-slate-200 p-5">
                                @forelse($excludedItems as $item)<li class="flex gap-3 text-sm text-slate-700"><x-lucide-x class="h-5 w-5 shrink-0 text-red-500" />{{ $item->item }}</li>@empty<li class="text-sm text-slate-500">{{ __('frontend.tour.no_items') }}</li>@endforelse
                            </ul>
                        </div>
                    </div>
                    @if($notes->isNotEmpty())
                        <div class="mt-6 rounded-lg border border-amber-200 bg-amber-50 p-5 text-sm text-amber-900">@foreach($notes as $note)<p>{{ $note->item }}</p>@endforeach</div>
                    @endif
                </section>

                <section id="pricing" class="scroll-mt-36 pt-12">
                    <h2 class="text-2xl font-extrabold text-slate-900">{{ __('frontend.tour.package.pricing_title') }}</h2>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">{{ __('frontend.tour.package.pricing_description') }}</p>
                    <div class="mt-6 grid gap-4 md:grid-cols-2">
                        @forelse($package->tiers as $tier)
                            <div class="rounded-lg border border-slate-200 p-5">
                                <div class="flex items-start justify-between gap-3"><h3 class="font-bold text-slate-900">{{ $tier->name }}</h3>@if($tier->hotel_stars)<span class="text-xs font-semibold text-slate-500">{{ trans_choice('frontend.tour.package.hotel_stars', $tier->hotel_stars, ['count' => $tier->hotel_stars]) }}</span>@endif</div>
                                <ul class="mt-4 space-y-2 text-sm text-slate-600">
                                    @foreach($tier->priceTiers as $priceTier)
                                        <li class="flex justify-between gap-4"><span>{{ $priceTier->max_pax
                                            ? __('frontend.tour.package.pax_range', ['min' => $priceTier->min_pax, 'max' => $priceTier->max_pax])
                                            : __('frontend.tour.package.pax_from', ['min' => $priceTier->min_pax]) }}</span><strong class="text-slate-900">{{ $priceTier->formatted_price }}</strong></li>
                                    @endforeach
                                </ul>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500">{{ __('frontend.tour.package.pricing_empty') }}</p>
                        @endforelse
                    </div>
                </section>
            </div>

            <aside>
                <livewire:tour-package-calculator :$package />
            </aside>
        </div>
    </article>
</x-layouts::app>
