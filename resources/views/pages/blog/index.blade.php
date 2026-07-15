@php
    $locale = app()->getLocale();
    $isFiltered = filled($searchTerm) || filled($categorySlug);
    $visiblePostCount = $posts->total() + ($featuredPost && ! $isFiltered ? 1 : 0);
    $seoTitle = __('frontend.blog.index.seo_title', ['brand' => config('app.name')]);
    $seoDescription = __('frontend.blog.index.seo_description');
    $schemaPosts = collect($showFeaturedPost ? [$featuredPost] : [])
        ->concat($posts->items())
        ->filter()
        ->take(10)
        ->values();
    $schemaJson = json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'CollectionPage',
        'name' => __('frontend.blog.title'),
        'description' => $seoDescription,
        'url' => $canonicalUrl,
        'mainEntity' => [
            '@type' => 'ItemList',
            'numberOfItems' => $visiblePostCount,
            'itemListElement' => $schemaPosts
                ->map(fn ($post, $index) => [
                    '@type' => 'ListItem',
                    'position' => $index + 1,
                    'url' => route('blog.show', ['locale' => $locale, 'post' => $post->slug]),
                    'name' => $post->title,
                ])
                ->all(),
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
@endphp

<x-layouts::app
    :title="$seoTitle"
    :meta-description="$seoDescription"
    :schema-json="$schemaJson"
    :$canonicalUrl
    :$alternateUrls
    og-type="website"
>
    <section class="border-b border-slate-200 bg-slate-50" aria-labelledby="blog-index-heading">
        <div class="mx-auto max-w-7xl px-4 pb-12 pt-5 sm:px-6 sm:pb-14 lg:px-8">
            <nav class="text-sm text-slate-500" aria-label="{{ __('frontend.blog.breadcrumb.label') }}">
                <ol class="flex items-center gap-2">
                    <li>
                        <a href="{{ route('home', ['locale' => $locale]) }}" class="rounded-sm transition-colors hover:text-slate-900 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-blue-600">
                            {{ __('frontend.blog.breadcrumb.home') }}
                        </a>
                    </li>
                    <li aria-hidden="true"><x-lucide-chevron-right class="h-3.5 w-3.5" /></li>
                    <li class="font-semibold text-slate-900" aria-current="page">{{ __('frontend.blog.breadcrumb.current') }}</li>
                </ol>
            </nav>

            <div class="mt-10 grid gap-8 lg:grid-cols-[minmax(0,1fr)_minmax(22rem,0.72fr)] lg:items-end lg:gap-16">
                <div class="max-w-3xl">
                    <p class="text-xs font-bold uppercase text-blue-600">{{ __('frontend.blog.index.eyebrow') }}</p>
                    <h1 id="blog-index-heading" class="mt-3 text-3xl font-extrabold text-balance text-slate-950 sm:text-4xl lg:text-5xl">
                        {{ __('frontend.blog.title') }}
                    </h1>
                    <p class="mt-4 max-w-2xl text-sm leading-7 text-slate-600 sm:text-base">{{ __('frontend.blog.subtitle') }}</p>
                </div>

                <form method="GET" action="{{ route('blog.index', ['locale' => $locale]) }}" role="search" class="flex items-center gap-2 rounded-lg border border-slate-200 bg-white p-2 shadow-sm focus-within:border-blue-300">
                    @if(filled($categorySlug))
                        <input type="hidden" name="category" value="{{ $categorySlug }}">
                    @endif
                    <x-lucide-search class="ml-2 h-5 w-5 shrink-0 text-slate-400" aria-hidden="true" />
                    <label for="blog-search" class="sr-only">{{ __('frontend.blog.search.label') }}</label>
                    <input
                        id="blog-search"
                        type="search"
                        name="q"
                        value="{{ $searchTerm }}"
                        maxlength="100"
                        autocomplete="off"
                        placeholder="{{ __('frontend.blog.search.placeholder') }}"
                        class="min-h-11 min-w-0 flex-1 bg-transparent px-1 text-sm font-semibold text-slate-900 outline-none placeholder:font-normal placeholder:text-slate-400"
                    >
                    <x-ui::button type="submit" variant="secondary" size="icon" aria-label="{{ __('frontend.blog.search.submit') }}">
                        <x-slot:icon><x-lucide-arrow-right /></x-slot:icon>
                        <span class="sr-only">{{ __('frontend.blog.search.submit') }}</span>
                    </x-ui::button>
                </form>
            </div>
        </div>
    </section>

    <div class="bg-white">
        <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 sm:py-12 lg:px-8">
            @if($categories->isNotEmpty())
                <nav class="flex items-center gap-2 overflow-x-auto pb-3" aria-label="{{ __('frontend.blog.categories.label') }}">
                    <x-ui::button
                        tag="a"
                        href="{{ route('blog.index', array_filter(['locale' => $locale, 'q' => $searchTerm])) }}"
                        variant="{{ filled($categorySlug) ? 'outline' : 'soft' }}"
                        size="sm"
                        class="rounded-full"
                        :aria-current="blank($categorySlug) ? 'page' : null"
                    >
                        {{ __('frontend.blog.categories.all') }}
                        <span class="ml-1.5 text-[11px] opacity-70">{{ $categories->sum('published_posts_count') }}</span>
                    </x-ui::button>

                    @foreach($categories as $category)
                        @php($isCurrent = $activeCategory?->is($category) ?? false)
                        <x-ui::button
                            tag="a"
                            href="{{ route('blog.index', array_filter(['locale' => $locale, 'category' => $category->slug, 'q' => $searchTerm])) }}"
                            variant="{{ $isCurrent ? 'soft' : 'outline' }}"
                            size="sm"
                            class="rounded-full"
                            :aria-current="$isCurrent ? 'page' : null"
                        >
                            {{ $category->name }}
                            <span class="ml-1.5 text-[11px] opacity-70">{{ $category->published_posts_count }}</span>
                        </x-ui::button>
                    @endforeach
                </nav>
            @endif

            @if($showFeaturedPost)
                <section class="mt-7" aria-labelledby="featured-post-heading">
                    <div class="mb-5 flex items-end justify-between gap-4">
                        <div>
                            <p class="text-xs font-bold uppercase text-blue-600">{{ __('frontend.blog.featured.eyebrow') }}</p>
                            <h2 id="featured-post-heading" class="mt-2 text-2xl font-extrabold text-slate-950 sm:text-3xl">
                                {{ __('frontend.blog.featured.title') }}
                            </h2>
                        </div>
                    </div>
                    <x-ui.post-card :post="$featuredPost" :$locale featured layout="horizontal" priority image-height="h-72" />
                </section>
            @endif

            @if($posts->isNotEmpty() || $isFiltered)
                <section class="{{ $showFeaturedPost ? 'mt-14' : 'mt-7' }}" aria-labelledby="blog-results-heading">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="text-xs font-bold uppercase text-blue-600">{{ __('frontend.blog.results.eyebrow') }}</p>
                            <h2 id="blog-results-heading" class="mt-2 text-2xl font-extrabold text-slate-950 sm:text-3xl">
                                {{ $isFiltered ? __('frontend.blog.results.filtered_title') : __('frontend.blog.results.latest_title') }}
                            </h2>
                        </div>
                        <p class="text-sm font-semibold text-slate-500" aria-live="polite">
                            {{ trans_choice('frontend.blog.results.count', $posts->total(), ['count' => $posts->total()]) }}
                        </p>
                    </div>

                    @if($isFiltered)
                        <div class="mt-4 flex flex-wrap items-center gap-2 text-sm text-slate-600">
                            @if(filled($searchTerm))
                                <span>{{ __('frontend.blog.results.search_for', ['query' => $searchTerm]) }}</span>
                            @endif
                            @if($activeCategory)
                                <span>{{ __('frontend.blog.results.in_category', ['category' => $activeCategory->name]) }}</span>
                            @endif
                            <a href="{{ route('blog.index', ['locale' => $locale]) }}" class="font-bold text-blue-600 hover:text-blue-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                                {{ __('frontend.blog.results.reset') }}
                            </a>
                        </div>
                    @endif

                    @if($posts->isNotEmpty())
                        <div class="mt-7 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                            @foreach($posts as $post)
                                <x-ui.post-card :$post :$locale />
                            @endforeach
                        </div>

                        @if($posts->hasPages())
                            <div class="mt-10">
                                {{ $posts->links() }}
                            </div>
                        @endif
                    @else
                        <div class="mt-7 border-y border-slate-200 bg-slate-50 px-5 py-14 text-center sm:px-10">
                            <x-lucide-newspaper class="mx-auto h-10 w-10 text-slate-400" aria-hidden="true" />
                            <h3 class="mt-4 text-lg font-bold text-slate-950">{{ __('frontend.blog.empty.title') }}</h3>
                            <p class="mx-auto mt-2 max-w-md text-sm leading-7 text-slate-600">{{ __('frontend.blog.empty.subtitle') }}</p>
                            <div class="mt-6 flex flex-col justify-center gap-3 sm:flex-row">
                                <x-ui::button tag="a" href="{{ route('blog.index', ['locale' => $locale]) }}" variant="outline">
                                    <x-slot:icon><x-lucide-rotate-ccw /></x-slot:icon>
                                    {{ __('frontend.blog.results.reset') }}
                                </x-ui::button>
                                <x-ui::button tag="a" href="{{ route('tour.index', ['locale' => $locale]) }}">
                                    {{ __('frontend.blog.cta.browse_tours') }}
                                    <x-slot:trailingIcon><x-lucide-arrow-right /></x-slot:trailingIcon>
                                </x-ui::button>
                            </div>
                        </div>
                    @endif
                </section>
            @endif
        </div>
    </div>

    <section class="border-t border-slate-200 bg-slate-50 py-12 sm:py-14" aria-labelledby="blog-cta-heading">
        <div class="mx-auto flex max-w-7xl flex-col gap-6 px-4 sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:px-8">
            <div class="max-w-2xl">
                <p class="text-xs font-bold uppercase text-blue-600">{{ __('frontend.blog.cta.eyebrow') }}</p>
                <h2 id="blog-cta-heading" class="mt-2 text-2xl font-extrabold text-balance text-slate-950 sm:text-3xl">{{ __('frontend.blog.cta.title') }}</h2>
                <p class="mt-3 text-sm leading-7 text-slate-600">{{ __('frontend.blog.cta.description') }}</p>
            </div>
            <div class="flex flex-col gap-3 sm:flex-row">
                <x-ui::button tag="a" href="{{ route('tour.index', ['locale' => $locale]) }}" variant="outline">
                    {{ __('frontend.blog.cta.browse_tours') }}
                </x-ui::button>
                <x-ui::button tag="a" href="{{ route('contact.index', ['locale' => $locale]) }}">
                    {{ __('frontend.blog.cta.consult') }}
                    <x-slot:trailingIcon><x-lucide-message-circle /></x-slot:trailingIcon>
                </x-ui::button>
            </div>
        </div>
    </section>
</x-layouts::app>
