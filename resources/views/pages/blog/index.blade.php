<x-layouts::app>
    @php
        $locale = app()->getLocale();
        $activeCategory = request('category');
    @endphp

    <section class="border-b border-slate-100 bg-slate-50">
        <div class="mx-auto max-w-7xl px-4 pb-14 sm:px-6 lg:px-8">
            <nav class="mb-5 flex text-sm text-slate-500" aria-label="Breadcrumb">
                <ol class="flex items-center gap-2">
                    <li>
                        <a href="{{ route('home', ['locale' => $locale]) }}" class="rounded-md transition-colors hover:text-slate-900 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                            {{ $locale === 'id' ? 'Beranda' : ($locale === 'ms' ? 'Utama' : 'Home') }}
                        </a>
                    </li>
                    <li aria-hidden="true">/</li>
                    <li class="font-medium text-slate-900" aria-current="page">Blog</li>
                </ol>
            </nav>

            <div class="max-w-3xl">
                <p class="text-sm font-semibold uppercase tracking-wider text-blue-600">Blog</p>
                <h1 class="mt-3 text-3xl font-extrabold tracking-tight text-slate-900 text-balance sm:text-4xl lg:text-5xl">
                    {{ __('frontend.blog.title') }}
                </h1>
                <p class="mt-5 text-sm leading-7 text-slate-500">{{ __('frontend.blog.subtitle') }}</p>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8" aria-labelledby="blog-list-heading">
        <h2 id="blog-list-heading" class="sr-only">Daftar artikel blog</h2>

        @if($categories->isNotEmpty())
            <div class="mb-8 flex gap-2 overflow-x-auto pb-2" aria-label="Filter kategori blog">
                <a href="{{ route('blog.index', ['locale' => $locale]) }}"
                   class="shrink-0 rounded-full border px-4 py-2 text-xs font-bold transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 {{ $activeCategory ? 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50' : 'border-blue-200 bg-blue-50 text-blue-700' }}">
                    Semua
                </a>

                @foreach($categories as $category)
                    @php $isCurrent = $activeCategory === $category->slug; @endphp
                    <a href="{{ route('blog.index', ['locale' => $locale, 'category' => $category->slug]) }}"
                       class="shrink-0 rounded-full border px-4 py-2 text-xs font-bold transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 {{ $isCurrent ? 'border-blue-200 bg-blue-50 text-blue-700' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50' }}">
                        {{ $category->name }}
                    </a>
                @endforeach
            </div>
        @endif

        @if($posts->isNotEmpty())
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                @foreach($posts as $post)
                    <x-ui.post-card :$post :$locale />
                @endforeach
            </div>

            <div class="mt-10">
                {{ $posts->links() }}
            </div>
        @else
            <div class="rounded-2xl border border-slate-200/80 bg-white p-12 text-center shadow-xs">
                <x-lucide-newspaper class="mx-auto h-10 w-10 text-slate-400" aria-hidden="true" />
                <h3 class="mt-4 font-bold text-slate-900">{{ __('frontend.blog.empty.title') }}</h3>
                <p class="mx-auto mt-2 max-w-sm text-sm leading-7 text-slate-500">{{ __('frontend.blog.empty.subtitle') }}</p>
            </div>
        @endif
    </section>
</x-layouts::app>
