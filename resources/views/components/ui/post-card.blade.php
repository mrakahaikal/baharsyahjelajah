@props([
    'post',
    'locale' => app()->getLocale(),
    'imageHeight' => 'h-52',
    'featured' => false,
])

@php
    $publishedAt = $post->published_at ?? $post->created_at;
    $excerpt = $post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($post->content ?? ''), 140);
@endphp

<article {{ $attributes->merge([
    'class' => 'group flex h-full flex-col overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs transition-[border-color,box-shadow,transform] duration-200 hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-md',
]) }}>
    <a href="{{ route('blog.show', ['locale' => $locale, 'post' => $post->slug]) }}" class="block focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600" aria-label="{{ $post->title }}">
        <div class="relative {{ $imageHeight }} overflow-hidden bg-slate-100">
            <img
                src="{{ $post->cover_image_url }}"
                alt="{{ $post->title }}"
                width="{{ $featured ? 920 : 640 }}"
                height="{{ $featured ? 560 : 420 }}"
                loading="lazy"
                class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
            >

            @if($post->category)
                <span class="absolute left-4 top-4 rounded-full bg-slate-950/90 px-3 py-1 text-[11px] font-bold uppercase tracking-wider text-white shadow-sm">
                    {{ $post->category->name }}
                </span>
            @endif
        </div>
    </a>

    <div class="flex flex-1 flex-col p-5 {{ $featured ? 'sm:p-6' : '' }}">
        <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs font-semibold text-slate-400">
            @if($publishedAt)
                <span class="inline-flex items-center gap-1.5">
                    <x-lucide-calendar-days class="h-3.5 w-3.5" aria-hidden="true" />
                    <time datetime="{{ $publishedAt->toDateString() }}">
                        {{ $publishedAt->translatedFormat('d M Y') }}
                    </time>
                </span>
            @endif

            @if($post->author)
                <span class="inline-flex items-center gap-1.5">
                    <span aria-hidden="true">/</span>
                    <span>{{ $post->author->name }}</span>
                </span>
            @endif
        </div>

        <h3 class="mt-3 font-bold leading-snug text-slate-900 transition-colors group-hover:text-blue-600 {{ $featured ? 'text-2xl sm:text-3xl' : 'text-lg' }}">
            <a href="{{ route('blog.show', ['locale' => $locale, 'post' => $post->slug]) }}" class="focus-visible:rounded-md focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                {{ $post->title }}
            </a>
        </h3>

        <p class="mt-3 text-sm leading-7 text-slate-500 {{ $featured ? 'line-clamp-3' : 'line-clamp-2' }}">
            {{ $excerpt }}
        </p>

        <a href="{{ route('blog.show', ['locale' => $locale, 'post' => $post->slug]) }}" class="mt-5 inline-flex w-fit items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-blue-600 transition-colors hover:text-blue-700 focus-visible:rounded-md focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
            {{ __('frontend.blog.read_more') }}
            <x-lucide-arrow-right class="h-3.5 w-3.5 transition-transform duration-200 group-hover:translate-x-1" aria-hidden="true" />
        </a>
    </div>
</article>
