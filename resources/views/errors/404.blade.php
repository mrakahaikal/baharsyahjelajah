@php
    $homeUrl = route('home', ['locale' => $locale]);
    $tourUrl = route('tour.index', ['locale' => $locale]);
    $blogUrl = route('blog.index', ['locale' => $locale]);
    $contactUrl = route('contact.index', ['locale' => $locale]);
@endphp

<x-layouts::app
    :title="__('frontend.error.404.seo_title', ['brand' => config('app.name')])"
    robots="noindex, follow"
    :show-floating-whatsapp="false"
>
    <div class="bg-white">
        <section class="border-b border-slate-200 bg-slate-950 text-white" aria-labelledby="not-found-heading">
            <div class="mx-auto grid max-w-7xl gap-8 px-4 py-14 sm:px-6 sm:py-18 lg:grid-cols-[minmax(0,1fr)_18rem] lg:items-end lg:px-8 lg:py-20">
                <div class="max-w-3xl">
                    <div class="flex items-center gap-3 text-sm font-semibold text-blue-300">
                        <span class="grid h-10 w-10 place-items-center rounded-full border border-white/15 bg-white/10 text-xs font-extrabold text-white" aria-hidden="true">404</span>
                        <span>{{ __('frontend.error.404.eyebrow') }}</span>
                    </div>

                    <h1 id="not-found-heading" class="mt-6 text-3xl font-extrabold leading-tight text-white sm:text-4xl lg:text-5xl">
                        {{ __('frontend.error.404.title') }}
                    </h1>
                    <p class="mt-5 max-w-2xl text-sm leading-7 text-slate-300 sm:text-base">
                        {{ __('frontend.error.404.description') }}
                    </p>

                    <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                        <a href="{{ $homeUrl }}" class="inline-flex w-full items-center justify-center gap-2 rounded-full bg-white px-5 py-3 text-sm font-semibold text-slate-950 transition-colors hover:bg-blue-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-300 sm:w-fit">
                            <x-lucide-home class="h-4 w-4" aria-hidden="true" />
                            {{ __('frontend.error.404.back_home') }}
                        </a>
                        <a href="{{ $tourUrl }}" class="inline-flex w-full items-center justify-center gap-2 rounded-full border border-white/20 bg-white/10 px-5 py-3 text-sm font-semibold text-white transition-colors hover:bg-white/15 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-300 sm:w-fit">
                            <x-lucide-compass class="h-4 w-4" aria-hidden="true" />
                            {{ __('frontend.error.404.browse_tours') }}
                        </a>
                    </div>
                </div>

                <aside class="border-l border-white/15 pl-5 sm:pl-6">
                    <p class="text-xs font-semibold uppercase text-blue-300">{{ __('frontend.error.404.quick_help') }}</p>
                    <p class="mt-3 text-sm leading-6 text-slate-300">{{ __('frontend.error.404.quick_help_description') }}</p>
                    <a href="{{ $contactUrl }}" class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-white hover:text-blue-200 focus-visible:rounded-sm focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-blue-300">
                        {{ __('frontend.error.404.contact') }}
                        <x-lucide-arrow-right class="h-4 w-4" aria-hidden="true" />
                    </a>
                </aside>
            </div>
        </section>

        @if($packages->isNotEmpty())
            <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 sm:py-16 lg:px-8" aria-labelledby="recommended-packages-heading">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase text-blue-600">{{ __('frontend.error.404.packages_eyebrow') }}</p>
                        <h2 id="recommended-packages-heading" class="mt-2 text-2xl font-extrabold text-slate-900 sm:text-3xl">{{ __('frontend.error.404.packages_title') }}</h2>
                    </div>
                    <a href="{{ $tourUrl }}" class="inline-flex w-fit items-center gap-2 text-sm font-semibold text-blue-600 hover:text-blue-700 focus-visible:rounded-sm focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-blue-600">
                        {{ __('frontend.error.404.view_all_packages') }}
                        <x-lucide-arrow-right class="h-4 w-4" aria-hidden="true" />
                    </a>
                </div>

                <div class="mt-8 grid gap-5 lg:grid-cols-2">
                    @foreach($packages as $package)
                        @php
                            $packageUrl = route('tour.package.show', [
                                'locale' => $locale,
                                'tour' => $package->tour->slug,
                                'package' => $package->slug,
                            ]);
                        @endphp
                        <article class="group grid overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm transition-[border-color,box-shadow] hover:border-blue-200 hover:shadow-md sm:grid-cols-[11rem_minmax(0,1fr)]">
                            <a href="{{ $packageUrl }}" class="block aspect-[16/9] overflow-hidden bg-slate-100 focus-visible:outline-2 focus-visible:outline-offset-[-2px] focus-visible:outline-blue-600 sm:aspect-auto" aria-label="{{ $package->name }}">
                                <img src="{{ $package->cover_url }}" alt="{{ $package->name }}" width="440" height="360" loading="lazy" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
                            </a>
                            <div class="flex min-w-0 flex-col p-5">
                                <p class="text-xs font-semibold text-blue-600">{{ $package->tour->name }}</p>
                                <h3 class="mt-2 text-lg font-bold leading-snug text-slate-900">
                                    <a href="{{ $packageUrl }}" class="hover:text-blue-600 focus-visible:rounded-sm focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-blue-600">{{ $package->name }}</a>
                                </h3>
                                <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-2 text-xs font-medium text-slate-500">
                                    <span class="inline-flex items-center gap-1.5">
                                        <x-lucide-clock-3 class="h-3.5 w-3.5 text-blue-600" aria-hidden="true" />
                                        {{ $package->duration_label }}
                                    </span>
                                </div>
                                <a href="{{ $packageUrl }}" class="mt-5 inline-flex w-fit items-center gap-1.5 text-xs font-bold text-blue-600 hover:text-blue-700 focus-visible:rounded-sm focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-blue-600">
                                    {{ __('frontend.error.404.package_details') }}
                                    <x-lucide-arrow-right class="h-3.5 w-3.5" aria-hidden="true" />
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        @if($posts->isNotEmpty())
            <section class="border-y border-slate-200 bg-slate-50 py-12 sm:py-16" aria-labelledby="recommended-posts-heading">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase text-blue-600">{{ __('frontend.error.404.posts_eyebrow') }}</p>
                            <h2 id="recommended-posts-heading" class="mt-2 text-2xl font-extrabold text-slate-900 sm:text-3xl">{{ __('frontend.error.404.posts_title') }}</h2>
                        </div>
                        <a href="{{ $blogUrl }}" class="inline-flex w-fit items-center gap-2 text-sm font-semibold text-blue-600 hover:text-blue-700 focus-visible:rounded-sm focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-blue-600">
                            {{ __('frontend.error.404.view_all_posts') }}
                            <x-lucide-arrow-right class="h-4 w-4" aria-hidden="true" />
                        </a>
                    </div>

                    <div class="mt-8 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                        @foreach($posts as $post)
                            @php
                                $postUrl = route('blog.show', ['locale' => $locale, 'post' => $post->slug]);
                                $excerpt = $post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($post->content ?? ''), 130);
                            @endphp
                            <article class="group flex h-full flex-col border-t-2 border-slate-900 bg-white p-5 shadow-sm transition-[border-color,box-shadow] hover:border-blue-600 hover:shadow-md sm:p-6">
                                <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs font-semibold text-slate-400">
                                    @if($post->category)
                                        <span class="text-blue-600">{{ $post->category->name }}</span>
                                    @endif
                                    @if($post->published_at)
                                        <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->translatedFormat('d M Y') }}</time>
                                    @endif
                                </div>
                                <h3 class="mt-4 text-lg font-bold leading-snug text-slate-900 group-hover:text-blue-600">
                                    <a href="{{ $postUrl }}" class="focus-visible:rounded-sm focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-blue-600">{{ $post->title }}</a>
                                </h3>
                                @if($excerpt)
                                    <p class="mt-3 line-clamp-3 text-sm leading-7 text-slate-500">{{ $excerpt }}</p>
                                @endif
                                <a href="{{ $postUrl }}" class="mt-5 inline-flex w-fit items-center gap-1.5 text-xs font-bold text-blue-600 hover:text-blue-700 focus-visible:rounded-sm focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-blue-600">
                                    {{ __('frontend.blog.read_more') }}
                                    <x-lucide-arrow-right class="h-3.5 w-3.5" aria-hidden="true" />
                                </a>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        @if($packages->isEmpty() && $posts->isEmpty())
            <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 sm:py-16 lg:px-8" aria-labelledby="recovery-options-heading">
                <div class="border-y border-slate-200 py-8 sm:flex sm:items-center sm:justify-between sm:gap-8">
                    <div>
                        <h2 id="recovery-options-heading" class="text-xl font-extrabold text-slate-900">{{ __('frontend.error.404.empty_title') }}</h2>
                        <p class="mt-2 max-w-2xl text-sm leading-7 text-slate-500">{{ __('frontend.error.404.empty_description') }}</p>
                    </div>
                    <div class="mt-5 flex flex-col gap-3 sm:mt-0 sm:flex-row">
                        <a href="{{ $tourUrl }}" class="inline-flex items-center justify-center gap-2 rounded-full border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-800 hover:bg-slate-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">{{ __('frontend.error.404.browse_tours') }}</a>
                        <a href="{{ $blogUrl }}" class="inline-flex items-center justify-center gap-2 rounded-full border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-800 hover:bg-slate-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">{{ __('frontend.error.404.read_guides') }}</a>
                    </div>
                </div>
            </section>
        @endif
    </div>
</x-layouts::app>
