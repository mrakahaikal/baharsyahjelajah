@php
    $locale = app()->getLocale();
    $publishedAt = $post->published_at ?? $post->created_at;
    $seoTitle = $post->title.' | '.config('app.name');
    $seoDescription = \Illuminate\Support\Str::limit(
        $post->excerpt ?: strip_tags((string) $post->content),
        155,
    );
    $coverUrl = $post->cover_image_url;
    $contentHtml = $presentedContent['html'];
    $tocHeadings = $presentedContent['headings'];
    $readingMinutes = $presentedContent['readingMinutes'];
    $showTableOfContents = count($tocHeadings) >= 2;
    $whatsappShareUrl = 'https://wa.me/?text='.rawurlencode($post->title.' '.$canonicalUrl);
    $schemaJson = json_encode([
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'Article',
                'headline' => $post->title,
                'description' => $seoDescription,
                'url' => $canonicalUrl,
                'image' => $coverUrl,
                'datePublished' => $publishedAt?->toIso8601String(),
                'dateModified' => $post->updated_at?->toIso8601String(),
                'author' => $post->author ? [
                    '@type' => 'Person',
                    'name' => $post->author->name,
                ] : [
                    '@type' => 'Organization',
                    'name' => config('app.name'),
                ],
                'publisher' => [
                    '@type' => 'Organization',
                    'name' => config('app.name'),
                    'logo' => [
                        '@type' => 'ImageObject',
                        'url' => asset('images/logo-baharsyah-jelajah.webp'),
                    ],
                ],
            ],
            [
                '@type' => 'BreadcrumbList',
                'itemListElement' => [
                    [
                        '@type' => 'ListItem',
                        'position' => 1,
                        'name' => __('frontend.blog.breadcrumb.home'),
                        'item' => route('home', ['locale' => $locale]),
                    ],
                    [
                        '@type' => 'ListItem',
                        'position' => 2,
                        'name' => __('frontend.blog.breadcrumb.current'),
                        'item' => route('blog.index', ['locale' => $locale]),
                    ],
                    [
                        '@type' => 'ListItem',
                        'position' => 3,
                        'name' => $post->title,
                        'item' => $canonicalUrl,
                    ],
                ],
            ],
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
@endphp

<x-layouts::app
    :title="$seoTitle"
    :meta-description="$seoDescription"
    :schema-json="$schemaJson"
    :$canonicalUrl
    :$alternateUrls
    og-type="article"
    :og-image="$coverUrl"
>
    <article class="bg-white">
        <header class="border-b border-slate-200 bg-slate-50">
            <div class="mx-auto max-w-5xl px-4 pb-12 pt-5 sm:px-6 sm:pb-14 lg:px-8">
                <nav class="text-sm text-slate-500" aria-label="{{ __('frontend.blog.breadcrumb.label') }}">
                    <ol class="flex min-w-0 items-center gap-2">
                        <li>
                            <a href="{{ route('home', ['locale' => $locale]) }}" class="rounded-sm transition-colors hover:text-slate-900 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-blue-600">
                                {{ __('frontend.blog.breadcrumb.home') }}
                            </a>
                        </li>
                        <li aria-hidden="true"><x-lucide-chevron-right class="h-3.5 w-3.5" /></li>
                        <li>
                            <a href="{{ route('blog.index', ['locale' => $locale]) }}" class="rounded-sm transition-colors hover:text-slate-900 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-blue-600">
                                {{ __('frontend.blog.breadcrumb.current') }}
                            </a>
                        </li>
                        <li aria-hidden="true"><x-lucide-chevron-right class="h-3.5 w-3.5" /></li>
                        <li class="min-w-0 truncate font-semibold text-slate-900" aria-current="page">{{ $post->title }}</li>
                    </ol>
                </nav>

                <div class="mt-10 max-w-4xl">
                    @if($post->category)
                        <p class="text-xs font-bold uppercase text-blue-600">{{ $post->category->name }}</p>
                    @endif
                    <h1 class="mt-3 text-3xl font-extrabold text-balance text-slate-950 sm:text-4xl lg:text-5xl">
                        {{ $post->title }}
                    </h1>
                    @if($post->excerpt)
                        <p class="mt-5 max-w-3xl text-base leading-8 text-slate-600">{{ $post->excerpt }}</p>
                    @endif

                    <div class="mt-6 flex flex-wrap items-center gap-x-4 gap-y-2 text-sm font-semibold text-slate-500">
                        @if($publishedAt)
                            <span class="inline-flex items-center gap-1.5">
                                <x-lucide-calendar-days class="h-4 w-4 text-slate-400" aria-hidden="true" />
                                <time datetime="{{ $publishedAt->toDateString() }}">{{ $publishedAt->translatedFormat('d M Y') }}</time>
                            </span>
                        @endif
                        <span class="inline-flex items-center gap-1.5">
                            <x-lucide-clock-3 class="h-4 w-4 text-slate-400" aria-hidden="true" />
                            {{ trans_choice('frontend.blog.reading_time', $readingMinutes, ['count' => $readingMinutes]) }}
                        </span>
                        @if($post->author)
                            <span class="inline-flex items-center gap-1.5">
                                <x-lucide-circle-user-round class="h-4 w-4 text-slate-400" aria-hidden="true" />
                                {{ $post->author->name }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </header>

        <div class="mx-auto max-w-6xl px-4 pt-8 sm:px-6 sm:pt-10 lg:px-8">
            <x-ui.resilient-image
                :src="$coverUrl"
                :alt="$post->title"
                :priority="true"
                width="1400"
                height="760"
                class="h-56 rounded-lg sm:h-96 lg:h-[30rem]"
                image-class="h-full w-full object-cover"
            />
        </div>

        <div class="mx-auto grid max-w-6xl gap-10 px-4 py-10 sm:px-6 sm:py-12 lg:grid-cols-[minmax(0,1fr)_16rem] lg:items-start lg:gap-16 lg:px-8">
            <div class="min-w-0">
                @if($showTableOfContents)
                    <details class="mb-8 rounded-lg border border-slate-200 bg-slate-50 p-4 lg:hidden">
                        <summary class="cursor-pointer font-bold text-slate-950">{{ __('frontend.blog.toc.title') }}</summary>
                        <nav class="mt-4" aria-label="{{ __('frontend.blog.toc.label') }}">
                            <ol class="space-y-2.5 text-sm">
                                @foreach($tocHeadings as $heading)
                                    <li class="{{ $heading['level'] === 3 ? 'pl-4' : '' }}">
                                        <a href="#{{ $heading['id'] }}" class="text-slate-600 hover:text-blue-600">{{ $heading['title'] }}</a>
                                    </li>
                                @endforeach
                            </ol>
                        </nav>
                    </details>
                @endif

                <div
                    class="mb-8 flex flex-wrap items-center gap-2 border-b border-slate-200 pb-6 lg:hidden"
                    x-data="{
                        copied: false,
                        async copyLink() {
                            await navigator.clipboard.writeText(@js($canonicalUrl));
                            this.copied = true;
                            window.setTimeout(() => this.copied = false, 2000);
                        }
                    }"
                >
                    <span class="mr-1 text-xs font-bold uppercase text-slate-400">{{ __('frontend.blog.share.title') }}</span>
                    <x-ui::button tag="a" href="{{ $whatsappShareUrl }}" target="_blank" rel="noopener" variant="soft" size="sm" class="rounded-full">
                        <x-slot:icon><x-lucide-message-circle /></x-slot:icon>
                        {{ __('frontend.blog.share.whatsapp') }}
                    </x-ui::button>
                    <x-ui::button type="button" variant="outline" size="sm" class="rounded-full" x-on:click="copyLink()">
                        <x-slot:icon><x-lucide-link /></x-slot:icon>
                        <span x-text="copied ? @js(__('frontend.blog.share.copied')) : @js(__('frontend.blog.share.copy'))">{{ __('frontend.blog.share.copy') }}</span>
                    </x-ui::button>
                    <p class="sr-only" aria-live="polite" x-text="copied ? @js(__('frontend.blog.share.copied_status')) : ''"></p>
                </div>

                <div
                    class="text-base text-slate-700
                        [&>*+*]:mt-5
                        [&_a]:font-semibold [&_a]:text-blue-600 [&_a]:underline [&_a]:decoration-blue-200 [&_a]:decoration-2 [&_a]:underline-offset-4 [&_a:hover]:text-blue-700
                        [&_blockquote]:border-l-4 [&_blockquote]:border-blue-300 [&_blockquote]:bg-blue-50 [&_blockquote]:px-5 [&_blockquote]:py-4 [&_blockquote]:text-slate-700
                        [&_code]:rounded [&_code]:bg-slate-100 [&_code]:px-1.5 [&_code]:py-0.5 [&_code]:text-sm [&_code]:text-slate-900
                        [&_h2]:scroll-mt-28 [&_h2]:pt-4 [&_h2]:text-2xl [&_h2]:font-extrabold [&_h2]:text-balance [&_h2]:text-slate-950 sm:[&_h2]:text-3xl
                        [&_h3]:scroll-mt-28 [&_h3]:pt-2 [&_h3]:text-xl [&_h3]:font-bold [&_h3]:text-slate-950
                        [&_h4]:text-lg [&_h4]:font-bold [&_h4]:text-slate-950
                        [&_img]:h-auto [&_img]:max-w-full [&_img]:rounded-lg
                        [&_li]:mt-2 [&_ol]:list-decimal [&_ol]:pl-6
                        [&_p]:leading-8
                        [&_pre]:overflow-x-auto [&_pre]:rounded-lg [&_pre]:bg-slate-950 [&_pre]:p-5 [&_pre]:text-sm [&_pre]:text-slate-100
                        [&_strong]:font-bold [&_strong]:text-slate-950
                        [&_table]:block [&_table]:max-w-full [&_table]:overflow-x-auto [&_table]:border-collapse
                        [&_td]:border [&_td]:border-slate-200 [&_td]:p-3
                        [&_th]:border [&_th]:border-slate-200 [&_th]:bg-slate-50 [&_th]:p-3 [&_th]:text-left
                        [&_ul]:list-disc [&_ul]:pl-6"
                >
                    {!! $contentHtml !!}
                </div>

                <section class="mt-12 border-y border-slate-200 bg-slate-50 px-5 py-8 sm:px-8" aria-labelledby="article-cta-heading">
                    <p class="text-xs font-bold uppercase text-blue-600">{{ __('frontend.blog.cta.eyebrow') }}</p>
                    <h2 id="article-cta-heading" class="mt-2 text-2xl font-extrabold text-balance text-slate-950">{{ __('frontend.blog.article_cta.title') }}</h2>
                    <p class="mt-3 text-sm leading-7 text-slate-600">{{ __('frontend.blog.article_cta.description') }}</p>
                    <div class="mt-5 flex flex-col gap-3 sm:flex-row">
                        <x-ui::button tag="a" href="{{ route('tour.index', ['locale' => $locale]) }}">
                            {{ __('frontend.blog.cta.browse_tours') }}
                            <x-slot:trailingIcon><x-lucide-arrow-right /></x-slot:trailingIcon>
                        </x-ui::button>
                        <x-ui::button tag="a" href="{{ route('contact.index', ['locale' => $locale]) }}" variant="outline">
                            {{ __('frontend.blog.cta.consult') }}
                        </x-ui::button>
                    </div>
                </section>
            </div>

            <aside class="hidden space-y-8 border-l border-slate-200 pl-6 lg:sticky lg:top-28 lg:block">
                @if($showTableOfContents)
                    <div>
                        <p class="text-xs font-bold uppercase text-slate-400">{{ __('frontend.blog.toc.title') }}</p>
                        <nav class="mt-4" aria-label="{{ __('frontend.blog.toc.label') }}">
                            <ol class="space-y-3 text-sm">
                                @foreach($tocHeadings as $heading)
                                    <li class="{{ $heading['level'] === 3 ? 'pl-4' : '' }}">
                                        <a href="#{{ $heading['id'] }}" class="block border-l-2 border-transparent pl-3 leading-6 text-slate-600 transition-colors hover:border-blue-400 hover:text-blue-600">
                                            {{ $heading['title'] }}
                                        </a>
                                    </li>
                                @endforeach
                            </ol>
                        </nav>
                    </div>
                @endif

                <div
                    class="border-t border-slate-200 pt-6"
                    x-data="{
                        copied: false,
                        async copyLink() {
                            await navigator.clipboard.writeText(@js($canonicalUrl));
                            this.copied = true;
                            window.setTimeout(() => this.copied = false, 2000);
                        }
                    }"
                >
                    <p class="text-xs font-bold uppercase text-slate-400">{{ __('frontend.blog.share.title') }}</p>
                    <div class="mt-3 flex flex-col gap-2">
                        <x-ui::button tag="a" href="{{ $whatsappShareUrl }}" target="_blank" rel="noopener" variant="soft" size="sm" class="w-full">
                            <x-slot:icon><x-lucide-message-circle /></x-slot:icon>
                            {{ __('frontend.blog.share.whatsapp') }}
                        </x-ui::button>
                        <x-ui::button type="button" variant="outline" size="sm" class="w-full" x-on:click="copyLink()">
                            <x-slot:icon><x-lucide-link /></x-slot:icon>
                            <span x-text="copied ? @js(__('frontend.blog.share.copied')) : @js(__('frontend.blog.share.copy'))">{{ __('frontend.blog.share.copy') }}</span>
                        </x-ui::button>
                    </div>
                    <p class="sr-only" aria-live="polite" x-text="copied ? @js(__('frontend.blog.share.copied_status')) : ''"></p>
                </div>
            </aside>
        </div>
    </article>

    @if($relatedPosts->isNotEmpty())
        <section class="border-t border-slate-200 bg-slate-50 py-12 sm:py-14" aria-labelledby="related-posts-heading">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase text-blue-600">{{ __('frontend.blog.related.eyebrow') }}</p>
                        <h2 id="related-posts-heading" class="mt-2 text-2xl font-extrabold text-slate-950 sm:text-3xl">
                            {{ __('frontend.blog.related.title') }}
                        </h2>
                    </div>
                    <x-ui::button tag="a" href="{{ route('blog.index', ['locale' => $locale]) }}" variant="outline" size="sm">
                        {{ __('frontend.blog.view_all') }}
                        <x-slot:trailingIcon><x-lucide-arrow-right /></x-slot:trailingIcon>
                    </x-ui::button>
                </div>

                <div class="mt-7 grid grid-cols-1 gap-6 md:grid-cols-3">
                    @foreach($relatedPosts as $relatedPost)
                        <x-ui.post-card :post="$relatedPost" :$locale />
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</x-layouts::app>
