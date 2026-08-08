@props(['locale', 'heroBanners' => collect()])

@php
    $slides = collect();

    if ($heroBanners instanceof \Illuminate\Support\Collection && $heroBanners->isNotEmpty()) {
        foreach ($heroBanners as $banner) {
            $slides->push([
                'id' => $banner->id,
                'imageUrl' => $banner->image_url ?? 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1800&q=85',
                'title' => filled($banner->title) ? $banner->title : __('home.hero.title'),
                'subtitle' => filled($banner->subtitle) ? $banner->subtitle : __('home.hero.subtitle'),
                'primaryCtaUrl' => $banner->ctaUrl($locale) ?? '#search-panel',
                'primaryCtaLabel' => filled($banner->cta_label) ? $banner->cta_label : __('home.hero.search'),
                'opensInNewTab' => $banner->opensCtaInNewTab() ?? false,
            ]);
        }
    } else {
        $slides->push([
            'id' => 'default',
            'imageUrl' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1800&q=85',
            'title' => __('home.hero.title'),
            'subtitle' => __('home.hero.subtitle'),
            'primaryCtaUrl' => '#search-panel',
            'primaryCtaLabel' => __('home.hero.search'),
            'opensInNewTab' => false,
        ]);
    }

    $slideCount = $slides->count();
@endphp

<section
    class="group relative isolate min-h-150 overflow-hidden bg-slate-950 text-white sm:min-h-155 lg:min-h-170"
    aria-labelledby="home-hero-heading"
    @if($slideCount > 1)
        x-data="{
            activeIndex: 0,
            total: {{ $slideCount }},
            timer: null,
            touchStartX: 0,
            touchEndX: 0,
            startAutoplay() {
                this.stopAutoplay();
                this.timer = setInterval(() => { this.nextSlide(); }, 6000);
            },
            stopAutoplay() {
                if (this.timer) {
                    clearInterval(this.timer);
                    this.timer = null;
                }
            },
            nextSlide() {
                this.activeIndex = (this.activeIndex + 1) % this.total;
            },
            prevSlide() {
                this.activeIndex = (this.activeIndex - 1 + this.total) % this.total;
            },
            goToSlide(index) {
                this.activeIndex = index;
            },
            handleSwipe() {
                if (this.touchEndX < this.touchStartX - 40) { this.nextSlide(); }
                else if (this.touchEndX > this.touchStartX + 40) { this.prevSlide(); }
            }
        }"
        x-init="startAutoplay()"
        @mouseenter="stopAutoplay()"
        @mouseleave="startAutoplay()"
        @touchstart="touchStartX = $event.changedTouches[0].screenX"
        @touchend="touchEndX = $event.changedTouches[0].screenX; handleSwipe()"
    @endif
>
    <!-- Background Slides -->
    @foreach($slides as $index => $slide)
        <div
            @if($slideCount > 1)
                x-show="activeIndex === {{ $index }}"
                x-transition:enter="transition ease-out duration-1000"
                x-transition:enter-start="opacity-0 scale-105"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-700"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
            @endif
            class="absolute inset-0 h-full w-full"
            @if($slideCount > 1 && $index > 0) x-cloak @endif
        >
            <img
                src="{{ $slide['imageUrl'] }}"
                alt=""
                width="1800"
                height="1100"
                @if($index === 0) fetchpriority="high" @endif
                class="h-full w-full object-cover"
            >
        </div>
    @endforeach

    <!-- Overlays -->
    <div class="absolute inset-0 bg-linear-to-r from-slate-950 via-slate-950/82 to-slate-950/25 pointer-events-none"></div>
    <div class="absolute inset-0 bg-linear-to-t from-slate-950/60 via-transparent to-transparent pointer-events-none"></div>

    <!-- Content Slides -->
    <div class="relative mx-auto flex min-h-150 max-w-7xl items-center px-4 pb-28 pt-24 sm:min-h-155 sm:px-6 lg:min-h-170 lg:px-8 lg:pb-32">
        <div class="max-w-3xl">
            <p class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-[0.18em] text-blue-200">
                <x-lucide-shield-check class="h-4 w-4" aria-hidden="true" />
                {{ __('home.hero.eyebrow') }}
            </p>

            @foreach($slides as $index => $slide)
                <div
                    @if($slideCount > 1)
                        x-show="activeIndex === {{ $index }}"
                        x-transition:enter="transition ease-out duration-700 delay-200"
                        x-transition:enter-start="opacity-0 translate-y-4"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-300"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 -translate-y-2"
                    @endif
                    @if($slideCount > 1 && $index > 0) x-cloak @endif
                >
                    <h1 id="home-hero-heading" class="mt-5 max-w-2xl text-balance text-4xl font-extrabold leading-tight sm:text-5xl lg:text-6xl">
                        {{ $slide['title'] }}
                    </h1>

                    <p class="mt-5 max-w-2xl text-pretty text-base leading-8 text-slate-200 sm:text-lg">
                        {{ $slide['subtitle'] }}
                    </p>

                    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                        <x-ui::button
                            tag="a"
                            href="{{ $slide['primaryCtaUrl'] }}"
                            :target="$slide['opensInNewTab'] ? '_blank' : null"
                            :rel="$slide['opensInNewTab'] ? 'noopener noreferrer' : null"
                            variant="secondary"
                            size="lg"
                            class="shadow-lg shadow-blue-950/25 hover:-translate-y-0.5">
                            {{ $slide['primaryCtaLabel'] }}
                            <x-slot:trailingIcon><x-lucide-arrow-right /></x-slot:trailingIcon>
                        </x-ui::button>
                        <x-ui::button tag="a" href="{{ route('contact.index', ['locale' => $locale]) }}" variant="inverse" size="lg" class="backdrop-blur-sm">
                            <x-slot:icon><x-lucide-message-circle /></x-slot:icon>
                            {{ __('home.hero.consult') }}
                        </x-ui::button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Navigation Controls (Displayed only if more than 1 banner) -->
    @if($slideCount > 1)
        <!-- Arrow Buttons -->
        <div class="absolute inset-y-0 left-4 right-4 z-10 hidden items-center justify-between pointer-events-none md:flex lg:left-8 lg:right-8">
            <button
                type="button"
                @click="prevSlide()"
                aria-label="Previous Banner"
                class="pointer-events-auto flex h-11 w-11 items-center justify-center rounded-full border border-white/20 bg-slate-950/40 text-white backdrop-blur-md transition-all hover:border-white/50 hover:bg-slate-900/80 focus-visible:outline-2 focus-visible:outline-white"
            >
                <x-lucide-chevron-left class="h-6 w-6" aria-hidden="true" />
            </button>
            <button
                type="button"
                @click="nextSlide()"
                aria-label="Next Banner"
                class="pointer-events-auto flex h-11 w-11 items-center justify-center rounded-full border border-white/20 bg-slate-950/40 text-white backdrop-blur-md transition-all hover:border-white/50 hover:bg-slate-900/80 focus-visible:outline-2 focus-visible:outline-white"
            >
                <x-lucide-chevron-right class="h-6 w-6" aria-hidden="true" />
            </button>
        </div>

        <!-- Dots Pagination Indicator -->
        <div class="absolute bottom-6 left-1/2 z-10 flex -translate-x-1/2 items-center gap-2 rounded-full border border-white/15 bg-slate-950/50 px-3 py-1.5 backdrop-blur-md sm:bottom-8">
            @foreach($slides as $index => $slide)
                <button
                    type="button"
                    @click="goToSlide({{ $index }})"
                    :aria-label="'Slide ' + ({{ $index }} + 1)"
                    :class="activeIndex === {{ $index }} ? 'w-6 bg-blue-500' : 'w-2.5 bg-white/50 hover:bg-white/80'"
                    class="h-2.5 rounded-full transition-all duration-300 focus-visible:outline-2 focus-visible:outline-white"
                ></button>
            @endforeach
        </div>
    @endif
</section>
