@php
    $locale = app()->getLocale();
    $packageCount = $tour->packages->count();
    $previewPackage = $tour->packages->first();
    $coverUrl = $previewPackage?->cover_url;
    $startingPrice = $tour->packages
        ->map(fn ($package) => $package->startingPriceTier())
        ->filter()
        ->sortBy(fn ($priceTier) => (float) $priceTier->price)
        ->first();
    $minimumDuration = $tour->packages->min('duration_days');
    $maximumDuration = $tour->packages->max('duration_days');
    $durationSummary = $packageCount === 1
        ? $previewPackage?->duration_label
        : ($packageCount > 1
            ? __('frontend.tour.show.facts.duration_range', ['min' => $minimumDuration, 'max' => $maximumDuration])
            : null);
    $descriptionText = trim(strip_tags((string) $tour->description));
    $seoTitle = $tour->name.' | '.config('app.name');
    $seoDescription = \Illuminate\Support\Str::limit($tour->short_description ?: $descriptionText, 155);
    $contactUrl = route('contact.index', ['locale' => $locale, 'tour' => $tour->slug]);
    $schemaJson = json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'CollectionPage',
        'name' => $tour->name,
        'description' => $seoDescription,
        'url' => route('tour.show', ['locale' => $locale, 'tour' => $tour->slug]),
        'image' => $coverUrl,
        'numberOfItems' => $packageCount,
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
@endphp

<x-layouts::app :title="$seoTitle" :meta-description="$seoDescription" :schema-json="$schemaJson" :$canonicalUrl :$alternateUrls>
    <article class="bg-white">
        <header class="relative isolate min-h-[31rem] overflow-hidden bg-slate-950 text-white sm:min-h-[34rem]">
            @if($coverUrl)
                <img src="{{ $coverUrl }}" alt="{{ $tour->name }}" class="absolute inset-0 -z-20 h-full w-full object-cover" width="1800" height="1000">
            @endif
            <div class="absolute inset-0 -z-10 bg-slate-950/65"></div>

            <div class="mx-auto flex min-h-[31rem] max-w-7xl flex-col px-4 pb-10 pt-5 sm:min-h-[34rem] sm:px-6 sm:pb-14 lg:px-8">
                <nav class="text-sm text-white/70" aria-label="Breadcrumb">
                    <ol class="flex min-w-0 items-center gap-2">
                        <li>
                            <a href="{{ route('home', ['locale' => $locale]) }}" class="rounded-sm transition-colors hover:text-white focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-white">
                                {{ __('frontend.tour.show.breadcrumb_home') }}
                            </a>
                        </li>
                        <li aria-hidden="true"><x-lucide-chevron-right class="h-3.5 w-3.5" /></li>
                        <li>
                            <a href="{{ route('tour.index', ['locale' => $locale]) }}" class="rounded-sm transition-colors hover:text-white focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-white">
                                {{ __('frontend.tour.show.breadcrumb_tours') }}
                            </a>
                        </li>
                        <li aria-hidden="true"><x-lucide-chevron-right class="h-3.5 w-3.5" /></li>
                        <li class="truncate font-medium text-white" aria-current="page">{{ $tour->name }}</li>
                    </ol>
                </nav>

                <div class="mt-auto max-w-4xl pt-16">
                    <p class="text-xs font-semibold uppercase text-blue-200">{{ __('frontend.tour.show.hero_eyebrow') }}</p>
                    <div class="mt-4 flex flex-wrap gap-2 text-xs font-semibold">
                        @if($tour->category)
                            <span class="rounded-full border border-white/20 bg-black/20 px-3 py-1.5">{{ $tour->category->name }}</span>
                        @endif
                        <span class="rounded-full border border-white/20 bg-black/20 px-3 py-1.5">{{ $tour->tour_type->getLabel() }}</span>
                    </div>
                    <h1 class="mt-5 max-w-3xl text-3xl font-extrabold text-balance sm:text-5xl lg:text-6xl">{{ $tour->name }}</h1>
                    @if($tour->short_description)
                        <p class="mt-5 max-w-3xl text-base leading-7 text-slate-100 sm:text-lg sm:leading-8">{{ $tour->short_description }}</p>
                    @endif

                    <div class="mt-7 flex flex-col gap-3 sm:flex-row sm:items-center">
                        @if($packageCount > 0)
                            <x-ui::button tag="a" href="#packages" variant="light">
                                {{ __('frontend.tour.show.explore_packages') }}
                                <x-slot:trailingIcon><x-lucide-arrow-down /></x-slot:trailingIcon>
                            </x-ui::button>
                        @endif
                        <x-ui::button tag="a" href="{{ $contactUrl }}" variant="inverse">
                            {{ __('frontend.tour.show.custom_trip') }}
                            <x-slot:trailingIcon><x-lucide-message-circle /></x-slot:trailingIcon>
                        </x-ui::button>
                    </div>
                </div>
            </div>
        </header>

        <section class="border-b border-slate-200 bg-slate-50" aria-label="{{ __('frontend.tour.quick_facts') }}">
            <dl class="mx-auto grid max-w-7xl grid-cols-2 px-4 sm:px-6 lg:grid-cols-5 lg:px-8">
                @if($tour->category)
                    <div class="border-b border-r border-slate-200 py-5 pr-4 lg:border-b-0">
                        <dt class="text-xs font-semibold uppercase text-slate-400">{{ __('frontend.tour.show.facts.category') }}</dt>
                        <dd class="mt-1.5 text-sm font-bold text-slate-900">{{ $tour->category->name }}</dd>
                    </div>
                @endif
                <div class="border-b border-slate-200 py-5 pl-4 lg:border-b-0 lg:border-r lg:pr-4">
                    <dt class="text-xs font-semibold uppercase text-slate-400">{{ __('frontend.tour.show.facts.type') }}</dt>
                    <dd class="mt-1.5 text-sm font-bold text-slate-900">{{ $tour->tour_type->getLabel() }}</dd>
                </div>
                <div class="border-r border-slate-200 py-5 pr-4 lg:pl-4">
                    <dt class="text-xs font-semibold uppercase text-slate-400">{{ __('frontend.tour.show.facts.packages') }}</dt>
                    <dd class="mt-1.5 text-sm font-bold text-slate-900">{{ trans_choice('frontend.tour.show.facts.package_count', $packageCount, ['count' => $packageCount]) }}</dd>
                </div>
                @if($durationSummary)
                    <div class="py-5 pl-4 lg:border-r lg:border-slate-200 lg:pr-4">
                        <dt class="text-xs font-semibold uppercase text-slate-400">{{ __('frontend.tour.show.facts.duration') }}</dt>
                        <dd class="mt-1.5 text-sm font-bold text-slate-900">{{ $durationSummary }}</dd>
                    </div>
                @endif
                @if($startingPrice)
                    <div class="col-span-2 border-t border-slate-200 py-5 lg:col-span-1 lg:border-t-0 lg:pl-4">
                        <dt class="text-xs font-semibold uppercase text-slate-400">{{ __('frontend.tour.show.facts.starting_price') }}</dt>
                        <dd class="mt-1.5 text-sm font-extrabold text-blue-600">{{ $startingPrice->formatted_price }}</dd>
                    </div>
                @endif
            </dl>
        </section>

        @if($destinationHighlights->isNotEmpty())
            <section class="border-b border-slate-100 py-10 sm:py-12" aria-labelledby="destination-highlights-heading">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="max-w-2xl">
                        <p class="text-sm font-semibold uppercase text-blue-600">{{ __('frontend.tour.show.destinations.eyebrow') }}</p>
                        <h2 id="destination-highlights-heading" class="mt-2 text-2xl font-extrabold text-slate-900 sm:text-3xl">
                            {{ __('frontend.tour.show.destinations.title') }}
                        </h2>
                        <p class="mt-3 text-sm leading-7 text-slate-600">{{ __('frontend.tour.show.destinations.description') }}</p>
                    </div>

                    <div class="mt-6 grid max-w-4xl grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                        @foreach($destinationHighlights as $destination)
                            <x-ui.destination-highlight-card
                                :$destination
                                data-tour-destination-id="{{ $destination->id }}"
                            />
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        <section id="packages" class="scroll-mt-24 py-14 sm:py-18" aria-labelledby="package-options-heading">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl">
                    <p class="text-sm font-semibold uppercase text-blue-600">{{ __('frontend.tour.show.packages.eyebrow') }}</p>
                    <h2 id="package-options-heading" class="mt-2 text-3xl font-extrabold text-balance text-slate-900 sm:text-4xl">
                        {{ $packageCount === 1
                            ? __('frontend.tour.show.packages.single_title')
                            : __('frontend.tour.show.packages.multiple_title', ['count' => $packageCount]) }}
                    </h2>
                    <p class="mt-4 text-base leading-7 text-slate-600">
                        {{ $packageCount === 1
                            ? __('frontend.tour.show.packages.single_description')
                            : __('frontend.tour.show.packages.multiple_description') }}
                    </p>
                </div>

                @if($tour->packages->isNotEmpty())
                    <div class="mt-8 grid gap-6">
                        @foreach($tour->packages as $package)
                            <x-ui.tour-package-card :$package :$tour :$locale />
                        @endforeach
                    </div>
                @else
                    <div class="mt-8 rounded-lg border border-slate-200 bg-slate-50 p-6 sm:p-8">
                        <x-lucide-route class="h-7 w-7 text-blue-600" aria-hidden="true" />
                        <h3 class="mt-4 text-xl font-bold text-slate-900">{{ __('frontend.tour.show.packages.empty_title') }}</h3>
                        <p class="mt-2 max-w-2xl text-sm leading-7 text-slate-600">{{ __('frontend.tour.show.packages.empty_description') }}</p>
                        <x-ui::button tag="a" href="{{ $contactUrl }}" class="mt-5 hover:bg-blue-600">
                            {{ __('frontend.tour.show.custom_trip') }}
                            <x-slot:trailingIcon><x-lucide-message-circle /></x-slot:trailingIcon>
                        </x-ui::button>
                    </div>
                @endif
            </div>
        </section>

        <section class="border-y border-slate-100 bg-slate-50 py-14 sm:py-18" aria-labelledby="tour-overview-heading">
            <div class="mx-auto grid max-w-7xl gap-10 px-4 sm:px-6 lg:grid-cols-[minmax(0,1fr)_21rem] lg:gap-16 lg:px-8">
                <div>
                    <p class="text-sm font-semibold uppercase text-blue-600">{{ __('frontend.tour.show.overview.eyebrow') }}</p>
                    <h2 id="tour-overview-heading" class="mt-2 text-3xl font-extrabold text-slate-900">{{ __('frontend.tour.show.overview.title') }}</h2>
                    @if($tour->description)
                        <div class="mt-6 max-w-3xl text-slate-600 [&_a]:font-semibold [&_a]:text-blue-600 [&_li]:mt-2 [&_ol]:list-decimal [&_ol]:pl-5 [&_p]:leading-8 [&_ul]:list-disc [&_ul]:pl-5">{!! $tour->description !!}</div>
                    @endif
                </div>

                <aside class="border-t border-slate-200 pt-8 lg:border-l lg:border-t-0 lg:pl-8 lg:pt-0">
                    <p class="text-xs font-semibold uppercase text-slate-400">{{ __('frontend.tour.show.consultation.eyebrow') }}</p>
                    <h2 class="mt-3 text-2xl font-extrabold text-slate-900">{{ __('frontend.tour.show.consultation.title') }}</h2>
                    <p class="mt-3 text-sm leading-7 text-slate-600">{{ __('frontend.tour.consultation_panel_text') }}</p>
                    <x-ui::button tag="a" href="{{ $contactUrl }}" class="mt-5 w-full hover:bg-blue-600">
                        {{ __('frontend.tour.custom_trip_cta') }}
                        <x-slot:trailingIcon><x-lucide-message-circle /></x-slot:trailingIcon>
                    </x-ui::button>
                </aside>
            </div>
        </section>

        @if($relatedTours->isNotEmpty())
            <section class="py-14 sm:py-18" aria-labelledby="related-tours-heading">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <h2 id="related-tours-heading" class="text-2xl font-extrabold text-slate-900">{{ __('frontend.tour.show.related_title') }}</h2>
                    <div class="mt-7 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                        @foreach($relatedTours as $relatedTour)
                            <x-ui.tour-card :tour="$relatedTour" :$locale />
                        @endforeach
                    </div>
                </div>
            </section>
        @endif
    </article>
</x-layouts::app>
