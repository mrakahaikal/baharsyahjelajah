@props([
    'post',
    'locale' => app()->getLocale(),
    'imageHeight' => 'h-52',
    'featured' => false,
    'stretch' => true,
    'layout' => 'vertical',
    'priority' => false,
])

@php
    $publishedAt = $post->published_at ?? $post->created_at;
    $excerpt = $post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($post->content ?? ''), 140);
    $isHorizontal = $layout === 'horizontal';
    $cardClass = 'group grid min-w-0 overflow-hidden rounded-lg border border-slate-200 bg-white shadow-xs transition-[border-color,box-shadow,transform] duration-200 hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-md';
    $cardClass .= $isHorizontal ? ' md:grid-cols-[minmax(0,1.15fr)_minmax(0,0.85fr)]' : ' grid-rows-[auto_minmax(0,1fr)]';
    $cardClass = $stretch ? $cardClass.' h-full' : $cardClass;
    $resolvedImageHeight = $isHorizontal ? 'h-64 sm:h-80 md:h-full md:min-h-96' : $imageHeight;
    $postUrl = route('blog.show', ['locale' => $locale, 'post' => $post->localizedSlug($locale)]);
@endphp

<article {{ $attributes->merge([
    'class' => $cardClass,
]) }}>
    <a href="{{ $postUrl }}" class="relative block focus-visible:outline-2 focus-visible:outline-offset-[-3px] focus-visible:outline-blue-600" aria-label="{{ $post->title }}">
        <x-ui.resilient-image
            :src="$post->cover_image_url"
            :alt="$post->title"
            :width="$featured ? 920 : 640"
            :height="$featured ? 560 : 420"
            :$priority
            class="{{ $resolvedImageHeight }}"
            image-class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
        />

        @if($post->category)
            <span class="absolute left-4 top-4 rounded-full bg-slate-950/90 px-3 py-1 text-[11px] font-bold uppercase tracking-wider text-white shadow-sm">
                {{ $post->category->name }}
            </span>
        @endif
    </a>

    <div class="flex min-w-0 flex-col p-5 {{ $featured ? 'sm:p-7' : '' }}">
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
                    <span aria-hidden="true">&bull;</span>
                    <span>{{ $post->author->name }}</span>
                </span>
            @endif
        </div>

        <h3 class="mt-3 line-clamp-3 font-bold leading-snug text-slate-900 transition-colors group-hover:text-blue-600 {{ $featured ? 'text-2xl sm:text-3xl' : 'text-lg' }}">
            <a href="{{ $postUrl }}" class="focus-visible:rounded-md focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                {{ $post->title }}
            </a>
        </h3>

        <p class="mt-3 text-sm leading-7 text-slate-500 {{ $featured ? 'line-clamp-3' : 'line-clamp-2' }}">
            {{ $excerpt }}
        </p>

        <x-ui::button tag="a" href="{{ $postUrl }}" variant="soft" size="sm" class="mt-5 w-fit rounded-full">
            {{ __('frontend.blog.read_more') }}
            <x-slot:trailingIcon><x-lucide-arrow-right class="transition-transform duration-200 group-hover:translate-x-1" /></x-slot:trailingIcon>
        </x-ui::button>
    </div>
</article>
