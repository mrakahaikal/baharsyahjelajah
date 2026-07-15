@php
    $locale = app()->getLocale();
    $cover = $package->getFirstMedia(\App\Models\UmrahPackage::MEDIA_COLLECTION_COVER);
    $galleryImages = collect([$cover])
        ->merge($package->getMedia(\App\Models\UmrahPackage::MEDIA_COLLECTION_GALLERY))
        ->filter()
        ->map(fn ($media) => ['src' => $media->getUrl(), 'alt' => $package->name])
        ->values();

    if ($galleryImages->isEmpty()) {
        $galleryImages = collect([['src' => $package->thumbnail_url, 'alt' => $package->name]]);
    }

    $includedItems = $package->includes->where('type', 'include');
    $excludedItems = $package->includes->where('type', 'exclude');
    $requirements = $package->includes->where('type', 'requirement');
    $notes = $package->includes->where('type', 'note');
@endphp

<x-layouts::app
    :title="__('umrah.show.seo_title', ['package' => $package->name, 'brand' => config('app.name')])"
    :meta-description="\Illuminate\Support\Str::limit(strip_tags($package->description ?? ''), 155)"
    :show-floating-whatsapp="false"
    theme-class="theme-umrah"
    :$schemaJson
    :$canonicalUrl
    :$alternateUrls>
    <section class="relative isolate overflow-hidden bg-neutral-950 text-white" aria-labelledby="umrah-package-heading">
        <img src="{{ $package->thumbnail_url }}" alt="" width="1800" height="900" fetchpriority="high" class="absolute inset-0 h-full w-full object-cover opacity-35">
        <div class="absolute inset-0 bg-linear-to-r from-neutral-950 via-neutral-950/90 to-neutral-950/45"></div>
        <div class="relative mx-auto max-w-7xl px-4 py-14 sm:px-6 sm:py-18 lg:px-8 lg:py-22">
            <nav class="text-sm text-neutral-400" aria-label="Breadcrumb">
                <ol class="flex flex-wrap items-center gap-2">
                    <li><a href="{{ route('home', ['locale' => $locale]) }}" class="hover:text-white">{{ __('umrah.show.home') }}</a></li>
                    <li aria-hidden="true">/</li>
                    <li><a href="{{ route('umroh.index', ['locale' => $locale]) }}" class="hover:text-white">Umrah</a></li>
                    <li aria-hidden="true">/</li>
                    <li class="max-w-52 truncate text-amber-200" aria-current="page">{{ $package->name }}</li>
                </ol>
            </nav>

            <div class="mt-10 max-w-4xl">
                <div class="flex flex-wrap items-center gap-3 text-xs font-bold uppercase text-amber-300">
                    <span>{{ __('umrah.types.'.$package->package_type) }}</span>
                    <span aria-hidden="true">/</span>
                    <span>{{ __('umrah.card.days', ['count' => $package->duration_days]) }}</span>
                </div>
                <h1 id="umrah-package-heading" class="mt-4 text-balance text-3xl font-extrabold leading-tight sm:text-5xl lg:text-6xl">{{ $package->name }}</h1>
                <p class="mt-5 max-w-2xl text-sm leading-7 text-neutral-300 sm:text-base">{{ strip_tags($package->description ?? '') }}</p>
                <div class="mt-7 flex flex-wrap items-end gap-x-8 gap-y-4">
                    <div>
                        <p class="text-xs font-bold uppercase text-neutral-400">{{ __('umrah.card.start_from') }}</p>
                        <p class="mt-1 text-2xl font-extrabold text-amber-300">{{ $package->formatted_price }}</p>
                    </div>
                    @if($package->upcomingDepartures->first())
                        <div>
                            <p class="text-xs font-bold uppercase text-neutral-400">{{ __('umrah.card.departure') }}</p>
                            <p class="mt-1 font-bold text-white">{{ $package->upcomingDepartures->first()->departure_date->translatedFormat('d M Y') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <div class="bg-stone-50">
        <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 sm:py-14 lg:px-8">
            <x-ui.lightbox-gallery :images="$galleryImages" :alt="$package->name" />

            <div class="mt-12 grid gap-10 lg:grid-cols-[minmax(0,1fr)_23rem] lg:items-start">
                <div class="min-w-0 space-y-12">
                    <section aria-labelledby="overview-heading">
                        <p class="text-xs font-bold uppercase text-amber-700">{{ __('umrah.show.overview_eyebrow') }}</p>
                        <h2 id="overview-heading" class="mt-2 text-2xl font-extrabold text-neutral-950 sm:text-3xl">{{ __('umrah.show.overview_title') }}</h2>
                        <div class="prose prose-stone mt-5 max-w-none text-sm leading-7">{!! $package->description !!}</div>

                        <dl class="mt-8 grid gap-px overflow-hidden rounded-lg border border-stone-200 bg-stone-200 sm:grid-cols-2">
                            @foreach([
                                [__('umrah.show.airline'), $package->airline, 'plane'],
                                [__('umrah.show.room'), $package->room_type ? __('umrah.rooms.'.$package->room_type) : null, 'bed-double'],
                                [__('umrah.show.makkah_hotel'), $package->hotel_makkah, 'building-2'],
                                [__('umrah.show.madinah_hotel'), $package->hotel_madinah, 'building-2'],
                            ] as [$label, $value, $icon])
                                @if($value)
                                    <div class="bg-white p-5">
                                        <dt class="text-xs font-bold uppercase text-stone-400">{{ $label }}</dt>
                                        <dd class="mt-2 text-sm font-bold text-neutral-950">{{ $value }}</dd>
                                    </div>
                                @endif
                            @endforeach
                        </dl>
                    </section>

                    @if($package->prices->isNotEmpty())
                        <section aria-labelledby="room-prices-heading">
                            <h2 id="room-prices-heading" class="text-2xl font-extrabold text-neutral-950">{{ __('umrah.show.prices_title') }}</h2>
                            <div class="mt-5 divide-y divide-stone-200 border-y border-stone-200">
                                @foreach($package->prices as $price)
                                    <div class="flex items-center justify-between gap-6 py-4">
                                        <span class="text-sm font-semibold text-neutral-700">{{ __('umrah.rooms.'.$price->room_type) }}</span>
                                        <span class="text-base font-extrabold text-amber-700">{{ $price->formatted_price }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    @endif

                    @if($package->upcomingDepartures->isNotEmpty())
                        <section aria-labelledby="departures-heading">
                            <h2 id="departures-heading" class="text-2xl font-extrabold text-neutral-950">{{ __('umrah.show.departures_title') }}</h2>
                            <div class="mt-5 grid gap-3 sm:grid-cols-2">
                                @foreach($package->upcomingDepartures as $departure)
                                    <article class="rounded-lg border border-stone-200 bg-white p-5">
                                        <div class="flex items-start justify-between gap-4">
                                            <div>
                                                <time datetime="{{ $departure->departure_date->toDateString() }}" class="font-extrabold text-neutral-950">{{ $departure->departure_date->translatedFormat('d M Y') }}</time>
                                                <p class="mt-1 text-xs text-stone-500">{{ __('umrah.show.return_date', ['date' => $departure->return_date->translatedFormat('d M Y')]) }}</p>
                                            </div>
                                            <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-bold text-amber-800">{{ $departure->status_label }}</span>
                                        </div>
                                        <div class="mt-4 h-1.5 overflow-hidden rounded-full bg-stone-100" aria-hidden="true">
                                            <div class="h-full rounded-full bg-amber-500" style="width: {{ min(100, $departure->quota_percentage) }}%"></div>
                                        </div>
                                        <p class="mt-2 text-xs font-semibold text-stone-500">{{ __('umrah.show.remaining_quota', ['count' => $departure->quota_sisa]) }}</p>
                                    </article>
                                @endforeach
                            </div>
                        </section>
                    @endif

                    @if($package->itineraries->isNotEmpty())
                        <section aria-labelledby="itinerary-heading">
                            <h2 id="itinerary-heading" class="text-2xl font-extrabold text-neutral-950">{{ __('umrah.show.itinerary_title') }}</h2>
                            <div class="mt-5 divide-y divide-stone-200 border-y border-stone-200">
                                @foreach($package->itineraries as $itinerary)
                                    <details class="group py-5" @if($loop->first) open @endif>
                                        <summary class="flex cursor-pointer list-none items-start justify-between gap-4">
                                            <span class="flex items-start gap-4">
                                                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-neutral-950 text-xs font-bold text-white">{{ $itinerary->day_number }}</span>
                                                <span>
                                                    <span class="block font-bold text-neutral-950">{{ $itinerary->title }}</span>
                                                    @if($itinerary->location)<span class="mt-1 block text-xs text-amber-700">{{ $itinerary->location }}</span>@endif
                                                </span>
                                            </span>
                                            <x-lucide-chevron-down class="mt-2 h-4 w-4 shrink-0 text-stone-400 transition group-open:rotate-180" aria-hidden="true" />
                                        </summary>
                                        @if($itinerary->description)<div class="ml-13 mt-4 text-sm leading-7 text-stone-600">{!! $itinerary->description !!}</div>@endif
                                    </details>
                                @endforeach
                            </div>
                        </section>
                    @endif

                    @foreach([
                        ['items' => $includedItems, 'title' => __('umrah.show.included_title'), 'icon' => 'check', 'color' => 'text-emerald-700'],
                        ['items' => $excludedItems, 'title' => __('umrah.show.excluded_title'), 'icon' => 'x', 'color' => 'text-red-700'],
                        ['items' => $requirements, 'title' => __('umrah.show.requirements_title'), 'icon' => 'file-check-2', 'color' => 'text-amber-700'],
                        ['items' => $notes, 'title' => __('umrah.show.notes_title'), 'icon' => 'info', 'color' => 'text-blue-700'],
                    ] as $group)
                        @if($group['items']->isNotEmpty())
                            <section>
                                <h2 class="text-xl font-extrabold text-neutral-950">{{ $group['title'] }}</h2>
                                <ul class="mt-4 grid gap-3 sm:grid-cols-2">
                                    @foreach($group['items'] as $item)
                                        <li class="flex items-start gap-3 text-sm leading-6 text-stone-700">
                                            <x-lucide-circle-check class="mt-0.5 h-4 w-4 shrink-0 {{ $group['color'] }}" aria-hidden="true" />
                                            <span>{{ $item->item }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </section>
                        @endif
                    @endforeach

                    @if($package->testimonials->isNotEmpty())
                        <section aria-labelledby="umrah-testimonials-heading">
                            <h2 id="umrah-testimonials-heading" class="text-2xl font-extrabold text-neutral-950">{{ __('umrah.show.testimonials_title') }}</h2>
                            <div class="mt-5 grid gap-4 sm:grid-cols-2">
                                @foreach($package->testimonials->take(4) as $testimonial)
                                    <figure class="rounded-lg border border-stone-200 bg-white p-5">
                                        <div class="text-sm text-amber-500" aria-label="{{ $testimonial->rating }} dari 5 bintang">{{ str_repeat('★', $testimonial->rating) }}</div>
                                        <blockquote class="mt-3 text-sm leading-7 text-stone-600">“{{ $testimonial->content }}”</blockquote>
                                        <figcaption class="mt-4 text-sm font-bold text-neutral-950">{{ $testimonial->reviewer_name }}</figcaption>
                                    </figure>
                                @endforeach
                            </div>
                        </section>
                    @endif
                </div>

                <aside aria-label="{{ __('umrah.inquiry.title') }}">
                    <livewire:umrah-package-inquiry :$package />
                </aside>
            </div>
        </div>
    </div>

    @if($relatedPackages->isNotEmpty())
        <section class="bg-neutral-950 py-14 text-white" aria-labelledby="related-packages-heading">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <h2 id="related-packages-heading" class="text-2xl font-extrabold">{{ __('umrah.show.related_title') }}</h2>
                <div class="mt-7 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($relatedPackages as $relatedPackage)
                        <x-ui.umrah-package-card :package="$relatedPackage" :$locale dark />
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</x-layouts::app>
