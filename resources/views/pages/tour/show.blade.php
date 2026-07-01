@php
    $locale = app()->getLocale();
    $descriptionText = trim(strip_tags((string) $tour->description));
    $seoTitle = $tour->name.' | '.config('app.name');
    $seoDescription = \Illuminate\Support\Str::limit($descriptionText ?: $tour->name, 155);
    $contactUrl = route('contact.index', ['locale' => $locale, 'tour' => $tour->slug]);

    $galleryImages = collect([$tour->thumbnail_url]);
    foreach ($tour->galleries as $gallery) {
        $galleryImages->push(str_starts_with($gallery->image_path, 'http') ? $gallery->image_path : \Illuminate\Support\Facades\Storage::url($gallery->image_path));
    }
    $placeholders = [
        'https://images.unsplash.com/photo-1493976040374-85c8e12f0c0e?q=80&w=900&auto=format&fit=crop',
        'https://images.unsplash.com/photo-1490806843957-31f4c9a91c65?q=80&w=1200&auto=format&fit=crop',
        'https://images.unsplash.com/photo-1503899036084-c55cdd92da26?q=80&w=900&auto=format&fit=crop',
        'https://images.unsplash.com/photo-1524413840807-0c3cb6fa808d?q=80&w=900&auto=format&fit=crop',
        'https://images.unsplash.com/photo-1590559899731-a382839ce501?q=80&w=900&auto=format&fit=crop',
    ];
    while ($galleryImages->count() < 5) {
        $galleryImages->push($placeholders[$galleryImages->count()]);
    }
    $images = $galleryImages->take(5);

    $highlightLines = collect(preg_split('/\r\n|\r|\n/', trim(strip_tags((string) $tour->highlights))))
        ->map(fn (string $highlight) => trim($highlight))
        ->filter();

    $schemaJson = json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'TouristTrip',
        'name' => $tour->name,
        'description' => $seoDescription,
        'image' => $images->values()->all(),
        'url' => route('tour.show', ['locale' => $locale, 'tour' => $tour->slug]),
        'touristType' => __('frontend.tour.' . $tour->tour_type) ?? ucfirst($tour->tour_type),
        'offers' => [
            '@type' => 'Offer',
            'price' => $tour->price,
            'priceCurrency' => $tour->currency,
            'availability' => 'https://schema.org/InStock',
            'url' => $contactUrl,
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
@endphp

<x-layouts::app :title="$seoTitle" :meta-description="$seoDescription" :schema-json="$schemaJson">
    <article class="bg-white">
        <header class="border-b border-slate-100 bg-slate-50">
            <div class="mx-auto max-w-7xl px-4 pb-10 sm:px-6 lg:px-8">
                <nav class="mb-5 flex text-sm text-slate-500" aria-label="Breadcrumb">
                    <ol class="flex min-w-0 items-center gap-2">
                        <li>
                            <a href="{{ route('home', ['locale' => $locale]) }}" class="rounded-md transition-colors hover:text-slate-900 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                                {{ $locale === 'id' ? 'Beranda' : ($locale === 'ms' ? 'Utama' : 'Home') }}
                            </a>
                        </li>
                        <li aria-hidden="true">/</li>
                        <li>
                            <a href="{{ route('tour.index', ['locale' => $locale]) }}" class="rounded-md transition-colors hover:text-slate-900 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                                {{ $locale === 'id' ? 'Tour' : ($locale === 'ms' ? 'Lawatan' : 'Tours') }}
                            </a>
                        </li>
                        @if($tour->category)
                            <li aria-hidden="true">/</li>
                            <li>
                                <a href="{{ route('tour.index', ['locale' => $locale, 'category' => $tour->category->slug]) }}" class="rounded-md transition-colors hover:text-slate-900 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                                    {{ $tour->category->name }}
                                </a>
                            </li>
                        @endif
                        <li aria-hidden="true">/</li>
                        <li class="truncate font-medium text-slate-900" aria-current="page">{{ $tour->name }}</li>
                    </ol>
                </nav>

                <div class="grid gap-8 lg:grid-cols-[1fr_22rem] lg:items-end">
                    <div>
                        @if($tour->category)
                            <p class="text-sm font-semibold uppercase tracking-wider text-blue-600">{{ $tour->category->name }}</p>
                        @endif
                        <h1 class="mt-3 max-w-4xl text-3xl font-extrabold tracking-tight text-slate-900 text-balance sm:text-4xl lg:text-5xl">
                            {{ $tour->name }}
                        </h1>
                        @if($descriptionText)
                            <p class="mt-5 max-w-3xl text-sm leading-7 text-slate-500 sm:text-base">
                                {{ \Illuminate\Support\Str::limit($descriptionText, 220) }}
                            </p>
                        @endif
                    </div>

                    <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-xs">
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ __('frontend.featured_tour.labels.start_from') }}</p>
                        <p class="mt-2 text-3xl font-extrabold tracking-tight text-slate-900">{{ $tour->formatted_price }}</p>
                        <p class="mt-2 text-sm leading-6 text-slate-500">{{ __('frontend.tour.price_context') }}</p>
                        <a href="{{ $contactUrl }}" class="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-full bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition-colors hover:bg-slate-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-900">
                            {{ __('frontend.tour.consult_tour') }}
                            <x-lucide-message-circle class="h-4 w-4" aria-hidden="true" />
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <section class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8" aria-label="{{ __('frontend.tour.gallery') }}">
            <div class="grid h-[21rem] grid-cols-2 grid-rows-2 gap-3 md:h-[28rem] md:grid-cols-4 md:gap-4">
                <img src="{{ $images[0] }}" alt="{{ $tour->name }}" class="h-full w-full rounded-2xl object-cover shadow-sm">
                <img src="{{ $images[1] }}" alt="{{ $tour->name }}" class="col-span-1 row-span-2 h-full w-full rounded-2xl object-cover shadow-sm md:col-span-2">
                <img src="{{ $images[2] }}" alt="{{ $tour->name }}" class="h-full w-full rounded-2xl object-cover shadow-sm">
                <img src="{{ $images[3] }}" alt="{{ $tour->name }}" class="h-full w-full rounded-2xl object-cover shadow-sm">
                <img src="{{ $images[4] }}" alt="{{ $tour->name }}" class="h-full w-full rounded-2xl object-cover shadow-sm">
            </div>
        </section>

        <div class="sticky top-20 z-20 border-y border-slate-100 bg-white/95 backdrop-blur">
            <nav class="mx-auto flex max-w-7xl gap-2 overflow-x-auto px-4 py-3 text-sm font-semibold text-slate-600 sm:px-6 lg:px-8" aria-label="{{ __('frontend.tour.page_nav') }}">
                <a href="#overview" class="min-w-max rounded-full px-3 py-1.5 hover:bg-slate-100 hover:text-slate-900 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">{{ __('frontend.tour.overview') }}</a>
                <a href="#highlights" class="min-w-max rounded-full px-3 py-1.5 hover:bg-slate-100 hover:text-slate-900 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">{{ __('frontend.tour.highlights') }}</a>
                <a href="#itinerary" class="min-w-max rounded-full px-3 py-1.5 hover:bg-slate-100 hover:text-slate-900 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">{{ __('frontend.tour.itinerary') }}</a>
                <a href="#includes" class="min-w-max rounded-full px-3 py-1.5 hover:bg-slate-100 hover:text-slate-900 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">{{ __('frontend.tour.whats_included') }}</a>
                <a href="#related" class="min-w-max rounded-full px-3 py-1.5 hover:bg-slate-100 hover:text-slate-900 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">{{ __('frontend.tour.related_content') }}</a>
            </nav>
        </div>

        <div class="mx-auto grid max-w-7xl gap-10 px-4 py-12 sm:px-6 lg:grid-cols-[1fr_22rem] lg:px-8">
            <div>
                <section class="grid grid-cols-2 gap-3 sm:grid-cols-4" aria-label="{{ __('frontend.tour.quick_facts') }}">
                    <div class="rounded-2xl border border-slate-200/80 bg-slate-50 p-4">
                        <x-lucide-clock class="h-5 w-5 text-blue-600" aria-hidden="true" />
                        <p class="mt-3 text-xs font-medium text-slate-500">{{ __('frontend.tour.duration') }}</p>
                        <p class="mt-1 text-sm font-bold text-slate-900">{{ $tour->duration_label }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200/80 bg-slate-50 p-4">
                        <x-lucide-activity class="h-5 w-5 text-blue-600" aria-hidden="true" />
                        <p class="mt-3 text-xs font-medium text-slate-500">{{ __('frontend.tour.difficulty') }}</p>
                        <p class="mt-1 text-sm font-bold text-slate-900">{{ __('frontend.tour.' . $tour->difficulty) ?? ucfirst($tour->difficulty) }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200/80 bg-slate-50 p-4">
                        <x-lucide-users class="h-5 w-5 text-blue-600" aria-hidden="true" />
                        <p class="mt-3 text-xs font-medium text-slate-500">{{ __('frontend.tour.group_size') }}</p>
                        <p class="mt-1 text-sm font-bold text-slate-900">{{ $tour->max_pax ? __('frontend.tour.max_pax', ['count' => $tour->max_pax]) : __('frontend.tour.flexible_pax') }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200/80 bg-slate-50 p-4">
                        <x-lucide-map class="h-5 w-5 text-blue-600" aria-hidden="true" />
                        <p class="mt-3 text-xs font-medium text-slate-500">{{ __('frontend.tour.tour_type') }}</p>
                        <p class="mt-1 text-sm font-bold text-slate-900">{{ __('frontend.tour.' . $tour->tour_type) ?? ucfirst($tour->tour_type) }}</p>
                    </div>
                </section>

                <section id="overview" class="scroll-mt-36 pt-12">
                    <h2 class="text-2xl font-extrabold tracking-tight text-slate-900">{{ __('frontend.tour.overview') }}</h2>
                    <div class="mt-5 max-w-3xl text-slate-600 [&_*+*]:mt-5 [&_a]:font-semibold [&_a]:text-blue-600 [&_h3]:text-xl [&_h3]:font-bold [&_h3]:text-slate-900 [&_li]:mt-2 [&_ol]:list-decimal [&_ol]:pl-5 [&_p]:leading-8 [&_strong]:font-bold [&_strong]:text-slate-900 [&_ul]:list-disc [&_ul]:pl-5">
                        {!! $tour->description !!}
                    </div>
                </section>

                @if($highlightLines->isNotEmpty())
                    <section id="highlights" class="scroll-mt-36 pt-12">
                        <h2 class="text-2xl font-extrabold tracking-tight text-slate-900">{{ __('frontend.tour.highlights') }}</h2>
                        <ul class="mt-5 grid grid-cols-1 gap-3 md:grid-cols-2">
                            @foreach($highlightLines as $highlight)
                                <li class="flex items-start gap-3 rounded-2xl border border-slate-200/80 bg-white p-4 text-sm leading-6 text-slate-600">
                                    <x-lucide-check-circle class="mt-0.5 h-5 w-5 shrink-0 text-blue-600" aria-hidden="true" />
                                    <span>{{ $highlight }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif

                <section id="itinerary" x-data="{ activeDay: {{ $tour->itineraries->first()?->day_number ?? 1 }} }" class="scroll-mt-36 pt-12">
                    <h2 class="text-2xl font-extrabold tracking-tight text-slate-900">{{ __('frontend.tour.itinerary') }}</h2>
                    <div class="mt-6 space-y-0 border-l-2 border-slate-200">
                        @foreach($tour->itineraries as $itinerary)
                            <div class="relative pb-8 pl-8">
                                <div class="absolute -left-2 top-1 h-4 w-4 rounded-full border-2 bg-white transition-colors duration-200"
                                     :class="activeDay === {{ $itinerary->day_number }} ? 'border-slate-900 bg-slate-900' : 'border-slate-300'"></div>

                                <button type="button" @click="activeDay = activeDay === {{ $itinerary->day_number }} ? null : {{ $itinerary->day_number }}"
                                        class="group flex w-full items-start justify-between gap-4 text-left"
                                        :aria-expanded="activeDay === {{ $itinerary->day_number }}">
                                    <span>
                                        <span class="text-sm font-semibold text-blue-600">{{ __('frontend.tour.day') }} {{ $itinerary->day_number }}</span>
                                        <span class="mt-1 block text-lg font-bold text-slate-900 transition-colors group-hover:text-blue-600">{{ $itinerary->title }}</span>
                                    </span>
                                    <x-lucide-chevron-down class="mt-1 h-5 w-5 shrink-0 text-slate-400 transition-transform duration-200" x-bind:class="activeDay === {{ $itinerary->day_number }} ? 'rotate-180' : ''" aria-hidden="true" />
                                </button>

                                <div x-show="activeDay === {{ $itinerary->day_number }}" x-collapse>
                                    <div class="pt-4 text-sm leading-7 text-slate-600">
                                        <p>{{ $itinerary->description }}</p>

                                        @if(!empty($itinerary->meal_labels) || ($itinerary->accommodation && $itinerary->accommodation !== '-'))
                                            <div class="mt-4 flex flex-wrap gap-3 rounded-xl border border-slate-100 bg-slate-50 p-4">
                                                @if(!empty($itinerary->meal_labels))
                                                    <div class="flex flex-wrap items-center gap-2">
                                                        <span class="text-xs font-semibold text-slate-500">{{ __('frontend.tour.meals') }}:</span>
                                                        @foreach($itinerary->meal_labels as $mealLabel)
                                                            <span class="rounded-md border border-slate-200 bg-white px-2.5 py-1 text-xs font-medium">{{ $mealLabel }}</span>
                                                        @endforeach
                                                    </div>
                                                @endif

                                                @if($itinerary->accommodation && $itinerary->accommodation !== '-')
                                                    <div class="flex items-center gap-2">
                                                        <span class="text-xs font-semibold text-slate-500">{{ __('frontend.tour.accommodation') }}:</span>
                                                        <span class="rounded-md border border-slate-200 bg-white px-2.5 py-1 text-xs font-medium">{{ $itinerary->accommodation }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section id="includes" class="scroll-mt-36 pt-12">
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <h2 class="text-2xl font-extrabold tracking-tight text-slate-900">{{ __('frontend.tour.whats_included') }}</h2>
                            <div class="mt-5 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-xs">
                                <ul class="space-y-3.5">
                                    @forelse($tour->includes_only as $include)
                                        <li class="flex items-start gap-3 text-sm font-medium text-slate-700">
                                            <x-lucide-check class="mt-0.5 h-5 w-5 shrink-0 text-green-600" aria-hidden="true" />
                                            <span>{{ $include->item }}</span>
                                        </li>
                                    @empty
                                        <li class="text-sm italic text-slate-500">{{ __('frontend.tour.no_items') }}</li>
                                    @endforelse
                                </ul>
                            </div>
                        </div>

                        <div>
                            <h2 class="text-2xl font-extrabold tracking-tight text-slate-900">{{ __('frontend.tour.whats_excluded') }}</h2>
                            <div class="mt-5 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-xs">
                                <ul class="space-y-3.5">
                                    @forelse($tour->excludes_only as $exclude)
                                        <li class="flex items-start gap-3 text-sm font-medium text-slate-700">
                                            <x-lucide-x class="mt-0.5 h-5 w-5 shrink-0 text-red-500" aria-hidden="true" />
                                            <span>{{ $exclude->item }}</span>
                                        </li>
                                    @empty
                                        <li class="text-sm italic text-slate-500">{{ __('frontend.tour.no_items') }}</li>
                                    @endforelse
                                </ul>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <aside class="lg:pt-12">
                <div class="sticky top-36 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-lg shadow-slate-900/5">
                    @if($tour->is_featured)
                        <span class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-bold uppercase tracking-wider text-amber-800">{{ __('frontend.tour.featured') }}</span>
                    @elseif($tour->category)
                        <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-bold uppercase tracking-wider text-slate-700">{{ $tour->category->name }}</span>
                    @endif

                    <h2 class="mt-4 text-xl font-extrabold tracking-tight text-slate-900">{{ __('frontend.tour.consultation_panel_title') }}</h2>
                    <p class="mt-3 text-sm leading-7 text-slate-500">{{ __('frontend.tour.consultation_panel_text') }}</p>

                    <dl class="mt-5 space-y-3 rounded-xl bg-slate-50 p-4">
                        <div class="flex items-center justify-between gap-4 text-sm">
                            <dt class="text-slate-500">{{ __('frontend.tour.duration') }}</dt>
                            <dd class="font-semibold text-slate-900">{{ $tour->duration_label }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4 text-sm">
                            <dt class="text-slate-500">{{ __('frontend.featured_tour.labels.start_from') }}</dt>
                            <dd class="font-semibold text-slate-900">{{ $tour->formatted_price }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4 text-sm">
                            <dt class="text-slate-500">{{ __('frontend.tour.group_size') }}</dt>
                            <dd class="font-semibold text-slate-900">{{ $tour->max_pax ? __('frontend.tour.max_pax', ['count' => $tour->max_pax]) : __('frontend.tour.flexible_pax') }}</dd>
                        </div>
                    </dl>

                    <a href="{{ $contactUrl }}" class="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-full bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition-colors hover:bg-slate-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-900">
                        {{ __('frontend.tour.consult_tour') }}
                        <x-lucide-arrow-right class="h-4 w-4" aria-hidden="true" />
                    </a>

                    <a href="{{ $tour->whatsappUrl() }}" target="_blank" rel="noopener" class="mt-3 inline-flex w-full items-center justify-center gap-2 rounded-full border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-800 transition-colors hover:bg-slate-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                        {{ __('frontend.cta.button_whatsapp') }}
                        <x-lucide-message-circle class="h-4 w-4" aria-hidden="true" />
                    </a>
                </div>
            </aside>
        </div>
    </article>

    <section id="related" class="scroll-mt-36 bg-slate-50 py-14" aria-labelledby="related-tour-heading">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @if($relatedTours->isNotEmpty())
                <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-wider text-blue-600">{{ __('frontend.tour.related_label') }}</p>
                        <h2 id="related-tour-heading" class="mt-2 text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">{{ __('frontend.tour.related_title') }}</h2>
                    </div>
                    <a href="{{ route('tour.index', ['locale' => $locale]) }}" class="inline-flex w-fit items-center gap-1.5 rounded-full border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition-colors hover:bg-slate-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                        {{ __('frontend.tour.see_all') }}
                        <x-lucide-arrow-right class="h-4 w-4" aria-hidden="true" />
                    </a>
                </div>

                <div class="mt-8 grid grid-cols-1 gap-6 md:grid-cols-3">
                    @foreach($relatedTours as $relatedTour)
                        <x-ui.tour-card :tour="$relatedTour" :$locale imageHeight="h-48" :showMaxPax="false" />
                    @endforeach
                </div>
            @endif

            @if($relatedPosts->isNotEmpty())
                <div class="{{ $relatedTours->isNotEmpty() ? 'mt-14 border-t border-slate-200 pt-12' : '' }}">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-wider text-blue-600">Blog</p>
                            <h2 class="mt-2 text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">{{ __('frontend.tour.related_articles') }}</h2>
                        </div>
                        <a href="{{ route('blog.index', ['locale' => $locale]) }}" class="inline-flex w-fit items-center gap-1.5 rounded-full border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition-colors hover:bg-slate-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                            {{ __('frontend.blog.view_all') }}
                            <x-lucide-arrow-right class="h-4 w-4" aria-hidden="true" />
                        </a>
                    </div>

                    <div class="mt-8 grid grid-cols-1 gap-6 md:grid-cols-3">
                        @foreach($relatedPosts as $relatedPost)
                            <x-ui.post-card :post="$relatedPost" :$locale />
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </section>
</x-layouts::app>
