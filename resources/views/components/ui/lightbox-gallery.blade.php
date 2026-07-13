@props([
    'images' => [],
    'alt' => 'Gallery image',
    'label' => null,
    'variant' => 'default',
])

@php
    $galleryImages = collect($images)
        ->map(function ($image) use ($alt) {
            $src = is_array($image) ? ($image['src'] ?? $image['url'] ?? null) : $image;

            if (blank($src)) {
                return null;
            }

            $imageAlt = is_array($image) ? ($image['alt'] ?? $alt) : $alt;
            $caption = is_array($image) ? ($image['caption'] ?? null) : null;

            return [
                'src' => (string) $src,
                'alt' => (string) ($imageAlt ?: $alt),
                'caption' => filled($caption) ? (string) $caption : null,
            ];
        })
        ->filter()
        ->values();

    $previewImages = $galleryImages->take(5);
    $previewCount = $previewImages->count();
    $totalImages = $galleryImages->count();
    $galleryLabel = $label ?? $alt;
    $isCompact = $variant === 'compact';

    $gridClass = match (true) {
        $isCompact => 'grid-cols-1',
        $previewCount === 1 => 'grid-cols-1',
        $previewCount >= 5 => 'grid-cols-2 md:grid-cols-4',
        default => 'grid-cols-2',
    };

    $heightClass = match (true) {
        $isCompact => 'h-auto',
        $previewCount === 1 => 'h-[18rem] sm:h-[24rem] md:h-[32rem]',
        $previewCount <= 2 => 'h-[18rem] sm:h-[22rem] md:h-[28rem]',
        default => 'h-auto md:h-[28rem]',
    };

    $tileClass = function (int $index, int $count) use ($isCompact): string {
        if ($isCompact) {
            return 'aspect-4/3';
        }

        if ($count === 1) {
            return 'aspect-[16/10] md:aspect-auto';
        }

        if ($count === 2) {
            return 'aspect-square md:aspect-auto';
        }

        if ($count === 3 && $index === 0) {
            return 'col-span-2 aspect-[16/9] md:aspect-auto';
        }

        if ($count >= 5 && $index === 1) {
            return 'aspect-square md:col-span-2 md:row-span-2 md:aspect-auto';
        }

        return 'aspect-square md:aspect-auto';
    };
@endphp

@if($totalImages > 0)
    <div
        {{ $attributes->merge(['class' => 'relative']) }}
        data-lightbox-variant="{{ $variant }}"
        x-data='{
            images: @json($galleryImages, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT),
            currentIndex: 0,
            isOpen: false,
            open(index) {
                this.currentIndex = index;
                this.isOpen = true;
                document.body.classList.add("overflow-hidden");
            },
            close() {
                this.isOpen = false;
                document.body.classList.remove("overflow-hidden");
            },
            next() {
                this.currentIndex = (this.currentIndex + 1) % this.images.length;
            },
            previous() {
                this.currentIndex = (this.currentIndex - 1 + this.images.length) % this.images.length;
            },
        }'
        @keydown.escape.window="close()"
        @keydown.arrow-right.window="isOpen && next()"
        @keydown.arrow-left.window="isOpen && previous()"
    >
        <div class="grid {{ $heightClass }} {{ $gridClass }} auto-rows-fr {{ $isCompact ? 'gap-0' : 'gap-3 md:gap-4' }}">
            @foreach($previewImages as $image)
                <button
                    type="button"
                    class="group relative block overflow-hidden {{ $isCompact ? 'rounded-lg bg-slate-900' : 'rounded-2xl bg-slate-100 shadow-sm' }} {{ $tileClass($loop->index, $previewCount) }} focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600"
                    @click="open({{ $loop->index }})"
                    aria-label="{{ $galleryLabel }}: {{ $image['alt'] }}"
                >
                    <img
                        src="{{ $image['src'] }}"
                        alt="{{ $image['alt'] }}"
                        class="h-full w-full object-cover transition duration-500 group-hover:scale-105 group-hover:brightness-95"
                        loading="{{ $isCompact ? 'lazy' : ($loop->first ? 'eager' : 'lazy') }}"
                    >

                    @if($isCompact)
                        <span class="absolute inset-0 bg-linear-to-t from-slate-950/90 via-slate-950/25 to-transparent" aria-hidden="true"></span>
                        <span class="absolute inset-x-0 bottom-0 flex items-center gap-1.5 p-3 text-left text-xs font-bold leading-5 text-white sm:text-sm">
                            <x-lucide-map-pin class="h-3.5 w-3.5 shrink-0 text-blue-300" aria-hidden="true" />
                            <span class="line-clamp-2">{{ $image['caption'] ?: $image['alt'] }}</span>
                        </span>
                    @endif

                    @if($totalImages > 5 && $loop->index === 4)
                        <span class="absolute inset-0 flex flex-col items-center justify-center gap-2 bg-slate-950/65 text-white">
                            <x-lucide-images class="h-7 w-7" aria-hidden="true" />
                            <span class="text-2xl font-extrabold tracking-normal">{{ $totalImages }}+</span>
                        </span>
                    @endif
                </button>
            @endforeach
        </div>

        <div
            x-cloak
            x-show="isOpen"
            x-transition.opacity
            class="fixed inset-0 z-[80] bg-slate-950/95 px-4 py-5 sm:px-6"
            role="dialog"
            aria-modal="true"
            aria-label="{{ $galleryLabel }}"
            @click.self="close()"
        >
            <div class="mx-auto flex h-full max-w-7xl flex-col gap-4">
                <div class="flex items-center justify-between gap-4 text-white">
                    <p class="min-w-0 truncate text-sm font-semibold">
                        <span x-text="currentIndex + 1"></span>/<span x-text="images.length"></span>
                        <span class="ml-3 hidden text-white/70 sm:inline" x-text="images[currentIndex]?.caption || images[currentIndex]?.alt"></span>
                    </p>

                    <button
                        type="button"
                        class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white/10 text-white transition-colors hover:bg-white/20 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white"
                        @click="close()"
                        aria-label="Close gallery"
                    >
                        <x-lucide-x class="h-5 w-5" aria-hidden="true" />
                    </button>
                </div>

                <div class="relative flex min-h-0 flex-1 items-center justify-center">
                    <button
                        type="button"
                        class="absolute left-0 top-1/2 z-10 inline-flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full bg-white/10 text-white transition-colors hover:bg-white/20 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white"
                        @click="previous()"
                        aria-label="Previous image"
                    >
                        <x-lucide-chevron-left class="h-6 w-6" aria-hidden="true" />
                    </button>

                    <img
                        x-bind:src="images[currentIndex]?.src"
                        x-bind:alt="images[currentIndex]?.alt"
                        class="max-h-full max-w-full rounded-2xl object-contain shadow-2xl shadow-black/40"
                    >

                    <button
                        type="button"
                        class="absolute right-0 top-1/2 z-10 inline-flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full bg-white/10 text-white transition-colors hover:bg-white/20 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white"
                        @click="next()"
                        aria-label="Next image"
                    >
                        <x-lucide-chevron-right class="h-6 w-6" aria-hidden="true" />
                    </button>
                </div>

                <div class="mx-auto flex max-w-full gap-2 overflow-x-auto pb-1">
                    <template x-for="(image, index) in images" :key="image.src + index">
                        <button
                            type="button"
                            class="h-14 w-20 shrink-0 overflow-hidden rounded-lg border transition"
                            :class="currentIndex === index ? 'border-white' : 'border-white/20 opacity-60 hover:opacity-100'"
                            @click="currentIndex = index"
                            :aria-label="`Open image ${index + 1}`"
                        >
                            <img :src="image.src" :alt="image.alt" class="h-full w-full object-cover">
                        </button>
                    </template>
                </div>
            </div>
        </div>
    </div>
@endif
