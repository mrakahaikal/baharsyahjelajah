<x-layouts::app>
    @php
        $locale = app()->getLocale();
        $publishedAt = $post->published_at ?? $post->created_at;
    @endphp

    <article class="bg-slate-50">
        <header class="border-b border-slate-100 bg-slate-50">
            <div class="mx-auto max-w-4xl px-4 pb-12 sm:px-6 lg:px-8">
                <nav class="mb-5 flex text-sm text-slate-500" aria-label="Breadcrumb">
                    <ol class="flex min-w-0 items-center gap-2">
                        <li>
                            <a href="{{ route('home', ['locale' => $locale]) }}" class="rounded-md transition-colors hover:text-slate-900 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                                {{ $locale === 'id' ? 'Beranda' : ($locale === 'ms' ? 'Utama' : 'Home') }}
                            </a>
                        </li>
                        <li aria-hidden="true">/</li>
                        <li>
                            <a href="{{ route('blog.index', ['locale' => $locale]) }}" class="rounded-md transition-colors hover:text-slate-900 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                                Blog
                            </a>
                        </li>
                        <li aria-hidden="true">/</li>
                        <li class="truncate font-medium text-slate-900" aria-current="page">{{ $post->title }}</li>
                    </ol>
                </nav>

                @if($post->category)
                    <p class="text-sm font-semibold uppercase tracking-wider text-blue-600">{{ $post->category->name }}</p>
                @endif

                <h1 class="mt-3 text-3xl font-extrabold tracking-tight text-slate-900 text-balance sm:text-4xl lg:text-5xl">
                    {{ $post->title }}
                </h1>

                <div class="mt-5 flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-slate-500">
                    @if($publishedAt)
                        <time datetime="{{ $publishedAt->toDateString() }}">{{ $publishedAt->translatedFormat('d M Y') }}</time>
                    @endif

                    @if($post->author)
                        <span>{{ $post->author->name }}</span>
                    @endif
                </div>
            </div>
        </header>

        <div class="bg-white">
            <div class="mx-auto max-w-5xl px-4 py-10 sm:px-6 lg:px-8">
                <img src="{{ $post->cover_image_url }}" alt="{{ $post->title }}" width="1200" height="720" class="aspect-[16/9] w-full rounded-2xl object-cover shadow-sm">

                <div class="mx-auto mt-10 max-w-3xl text-slate-600 [&_*+*]:mt-5 [&_a]:font-semibold [&_a]:text-blue-600 [&_blockquote]:border-l-4 [&_blockquote]:border-blue-200 [&_blockquote]:pl-5 [&_h2]:text-2xl [&_h2]:font-extrabold [&_h2]:tracking-tight [&_h2]:text-slate-900 [&_h3]:text-xl [&_h3]:font-bold [&_h3]:text-slate-900 [&_h4]:text-base [&_h4]:font-bold [&_h4]:text-slate-900 [&_li]:mt-2 [&_ol]:list-decimal [&_ol]:pl-5 [&_p]:leading-8 [&_strong]:font-bold [&_strong]:text-slate-900 [&_ul]:list-disc [&_ul]:pl-5">
                    {!! $post->content !!}
                </div>
            </div>
        </div>
    </article>

    @if($relatedPosts->isNotEmpty())
        <section class="bg-slate-50 py-14" aria-labelledby="related-posts-heading">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-wider text-blue-600">Blog</p>
                        <h2 id="related-posts-heading" class="mt-2 text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">
                            Artikel terkait
                        </h2>
                    </div>
                    <a href="{{ route('blog.index', ['locale' => $locale]) }}" class="inline-flex w-fit items-center gap-1.5 rounded-full border border-slate-200 bg-white px-5 py-2.5 text-xs font-semibold text-slate-700 transition-colors hover:bg-slate-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                        {{ __('frontend.blog.view_all') }}
                        <x-lucide-arrow-right class="h-3.5 w-3.5" aria-hidden="true" />
                    </a>
                </div>

                <div class="mt-8 grid grid-cols-1 gap-6 md:grid-cols-3">
                    @foreach($relatedPosts as $relatedPost)
                        <x-ui.post-card :post="$relatedPost" :$locale />
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</x-layouts::app>
