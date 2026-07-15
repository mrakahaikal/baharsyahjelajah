@props([
    'src' => null,
    'alt' => '',
    'width' => 1200,
    'height' => 720,
    'loading' => 'lazy',
    'priority' => false,
    'imageClass' => 'h-full w-full object-cover',
])

<div
    {{ $attributes->class('relative overflow-hidden bg-slate-100') }}
    @if($src) x-data="{ imageFailed: false }" @endif
>
    <div class="absolute inset-0 flex flex-col items-center justify-center gap-2 bg-slate-100 px-5 text-center text-slate-400" aria-hidden="true">
        <x-lucide-image-off class="h-8 w-8" />
        <span class="text-xs font-semibold">{{ __('frontend.blog.image_unavailable') }}</span>
    </div>

    @if($src)
        <img
            src="{{ $src }}"
            alt="{{ $alt }}"
            width="{{ $width }}"
            height="{{ $height }}"
            loading="{{ $priority ? 'eager' : $loading }}"
            @if($priority) fetchpriority="high" @endif
            x-show="!imageFailed"
            x-on:error="imageFailed = true"
            class="absolute inset-0 text-transparent {{ $imageClass }}"
        >
    @endif
</div>
