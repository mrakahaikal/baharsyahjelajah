@php
    $locale = app()->getLocale();
    $seoTitle = __('frontend.testimonials_page.seo_title', ['brand' => config('app.name')]);
    $seoDescription = __('frontend.testimonials_page.seo_description');

    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'CollectionPage',
        'name' => $seoTitle,
        'description' => $seoDescription,
        'url' => $canonicalUrl,
        'inLanguage' => $locale,
        'publisher' => [
            '@type' => 'TravelAgency',
            'name' => config('app.name'),
            'url' => url('/'),
        ],
    ];

    if ($testimonials->isNotEmpty()) {
        $schema['review'] = $testimonials->map(function ($t) use ($locale) {
            $authorName = is_array($t->reviewer_name) ? ($t->reviewer_name[$locale] ?? $t->reviewer_name['id'] ?? 'Wisatawan') : $t->reviewer_name;
            $body = is_array($t->content) ? ($t->content[$locale] ?? $t->content['id'] ?? '') : $t->content;

            return [
                '@type' => 'Review',
                'author' => [
                    '@type' => 'Person',
                    'name' => $authorName,
                ],
                'reviewRating' => [
                    '@type' => 'Rating',
                    'ratingValue' => (string) $t->rating,
                    'bestRating' => '5',
                ],
                'reviewBody' => $body,
            ];
        })->values()->all();
    }
@endphp

<x-layouts::app
    seo-page="testimonials_index"
    :title="$seoTitle"
    :meta-description="$seoDescription"
    breadcrumb-name="testimonials.index"
    :breadcrumb-parameters="[$locale]"
    :$canonicalUrl
    :$alternateUrls
    :schema="$schema">

    <!-- Hero Section -->
    <section class="bg-slate-950 text-white">
        <div class="mx-auto max-w-7xl px-4 pb-14 pt-5 sm:px-6 sm:pb-16 lg:px-8 lg:pb-20">
            <x-ui.breadcrumbs name="testimonials.index" :parameters="[$locale]" variant="dark" class="mb-10" />

            <div class="max-w-3xl">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-amber-400">
                    {{ __('frontend.home.testimonials_badge', ['default' => 'Kepuasan Jamaah & Wisatawan']) }}
                </p>
                <h1 class="mt-4 text-balance text-3xl font-black leading-tight sm:text-4xl lg:text-5xl">
                    {{ __('frontend.testimonials_page.title') }}
                </h1>
                <p class="mt-5 text-pretty text-base leading-8 text-slate-300 sm:text-lg">
                    {{ __('frontend.testimonials_page.subtitle') }}
                </p>
            </div>
        </div>
    </section>

    <!-- Testimonials List Section -->
    <section class="bg-slate-50 py-12 sm:py-16 lg:py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @if($testimonials->isNotEmpty())
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($testimonials as $item)
                        @php
                            $author = is_array($item->reviewer_name)
                                ? ($item->reviewer_name[$locale] ?? $item->reviewer_name['id'] ?? 'Pelanggan')
                                : $item->reviewer_name;
                            $content = is_array($item->content)
                                ? ($item->content[$locale] ?? $item->content['id'] ?? '')
                                : $item->content;
                        @endphp
                        <article class="flex flex-col justify-between rounded-2xl border border-slate-200/80 bg-white p-6 shadow-xs transition duration-300 hover:-translate-y-1 hover:shadow-md sm:p-7">
                            <div>
                                <!-- Rating Stars -->
                                <div class="flex items-center gap-1 text-amber-400" aria-label="Rating {{ $item->rating }} dari 5">
                                    @for($i = 1; $i <= 5; $i++)
                                        <x-lucide-star class="h-4 w-4 {{ $i <= $item->rating ? 'fill-amber-400 text-amber-400' : 'text-slate-200' }}" aria-hidden="true" />
                                    @endfor
                                    <span class="ml-2 text-xs font-bold text-slate-700">{{ $item->rating }}.0</span>
                                </div>

                                <!-- Review Body -->
                                <blockquote class="mt-4 text-sm leading-7 text-slate-600">
                                    &ldquo;{{ $content }}&rdquo;
                                </blockquote>
                            </div>

                            <!-- Reviewer Meta -->
                            <div class="mt-6 flex items-center gap-3 border-t border-slate-100 pt-5">
                                <div class="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-blue-50 font-bold text-blue-700">
                                    {{ Str::substr($author, 0, 1) }}
                                </div>
                                <div class="min-w-0">
                                    <h3 class="truncate text-sm font-bold text-slate-900">
                                        {{ $author }}
                                    </h3>
                                    <p class="mt-0.5 flex items-center gap-1.5 text-xs text-slate-500">
                                        @if($item->reviewer_flag)
                                            <span>{{ $item->reviewer_flag }}</span>
                                        @endif
                                        <span>{{ $item->reviewer_country ?? 'Wisatawan' }}</span>
                                        @if($item->product)
                                            <span class="text-slate-300">&bull;</span>
                                            <span class="truncate text-blue-600">{{ $item->product->name }}</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="mt-12">
                    {{ $testimonials->links() }}
                </div>
            @else
                <div class="rounded-3xl border border-slate-200 bg-white p-12 text-center shadow-sm">
                    <div class="mx-auto grid h-16 w-16 place-items-center rounded-2xl bg-amber-50 text-amber-600">
                        <x-lucide-message-square-quote class="h-8 w-8" aria-hidden="true" />
                    </div>
                    <h2 class="mt-4 text-lg font-bold text-slate-900">{{ __('frontend.testimonials_page.empty') }}</h2>
                    <p class="mt-2 text-sm text-slate-500 max-w-md mx-auto">
                        {{ __('frontend.testimonials_page.subtitle') }}
                    </p>
                </div>
            @endif

            <!-- Bottom CTA -->
            <div class="mt-16 rounded-3xl bg-radial from-slate-900 via-slate-950 to-neutral-950 p-8 text-center text-white sm:p-12 border border-white/10">
                <p class="text-xs font-bold uppercase tracking-wider text-amber-400">Baharsyah Jelajah</p>
                <h2 class="mt-2 text-2xl font-black sm:text-3xl">
                    {{ __('frontend.footer.consultation_prompt') }}
                </h2>
                <p class="mx-auto mt-3 max-w-2xl text-sm text-slate-300 sm:text-base">
                    {{ __('frontend.contact_page.hero.description') }}
                </p>
                <div class="mt-6 flex flex-wrap items-center justify-center gap-4">
                    <x-ui::button tag="a" href="{{ route('contact.index', ['locale' => $locale]) }}" class="bg-amber-400 text-slate-950 hover:bg-amber-300 font-bold">
                        {{ __('frontend.contact_page.title') }}
                    </x-ui::button>
                    <x-ui::button tag="a" href="{{ route('tour.index', ['locale' => $locale]) }}" variant="outline" class="border-white/30 text-white hover:bg-white/10">
                        {{ __('frontend.tour.show.breadcrumb_tours') }}
                    </x-ui::button>
                </div>
            </div>
        </div>
    </section>
</x-layouts::app>