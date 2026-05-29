@if($banners->isNotEmpty())
<!-- Promotional Banner Slider -->
<section x-data="{
    active: 0,
    total: {{ $banners->count() }},
    init() {
        if (this.total > 1) {
            setInterval(() => {
                this.active = (this.active + 1) % this.total;
            }, 5000);
        }
    }
}" class="py-10 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="relative overflow-hidden rounded-2xl h-52 sm:h-64 lg:h-72 bg-gray-200">

            <!-- Slides -->
            @foreach($banners as $index => $banner)
                <div x-show="active === {{ $index }}"
                     x-transition:enter="transition ease-in-out duration-500"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="transition ease-in-out duration-300"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="absolute inset-0 group">
                    <img src="{{ $banner->image_path }}"
                         alt="{{ $banner->title }}"
                         loading="lazy"
                         class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/25 to-transparent"></div>
                    <div class="absolute inset-0 flex flex-col justify-end p-6 sm:p-8">
                        <p class="text-white/65 text-xs mb-1 line-clamp-1">{{ $banner->subtitle }}</p>
                        <h3 class="text-white font-bold text-lg sm:text-xl leading-snug mb-3 line-clamp-2">
                            {{ $banner->title }}
                        </h3>
                        @if($banner->cta_label && $banner->cta_url)
                            <a href="{{ $banner->cta_url }}"
                               @if($banner->cta_type === 'whatsapp' || $banner->cta_type === 'url') target="_blank" @endif
                               class="self-start inline-flex items-center gap-1.5 bg-white/95 hover:bg-white text-gray-900 text-xs font-semibold px-4 py-2 rounded-lg transition-colors shadow-sm">
                                @if($banner->cta_type === 'whatsapp')
                                    <x-lucide-message-circle class="w-3.5 h-3.5 text-primary" />
                                @else
                                    <x-lucide-arrow-right class="w-3.5 h-3.5 text-primary" />
                                @endif
                                {{ $banner->cta_label }}
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach

            @if($banners->count() > 1)
                <!-- Prev / Next arrows -->
                <button @click="active = (active - 1 + total) % total"
                        class="absolute left-3 top-1/2 -translate-y-1/2 z-10 w-8 h-8 bg-black/30 hover:bg-black/50 text-white rounded-full flex items-center justify-center transition-colors backdrop-blur-sm">
                    <x-lucide-chevron-left class="w-4 h-4" />
                </button>
                <button @click="active = (active + 1) % total"
                        class="absolute right-3 top-1/2 -translate-y-1/2 z-10 w-8 h-8 bg-black/30 hover:bg-black/50 text-white rounded-full flex items-center justify-center transition-colors backdrop-blur-sm">
                    <x-lucide-chevron-right class="w-4 h-4" />
                </button>

                <!-- Dot indicators -->
                <div class="absolute bottom-3 right-4 z-10 flex gap-1.5">
                    @foreach($banners as $index => $banner)
                        <button @click="active = {{ $index }}"
                                :class="active === {{ $index }} ? 'w-5 bg-white' : 'w-1.5 bg-white/45 hover:bg-white/70'"
                                class="h-1.5 rounded-full transition-all duration-300"></button>
                    @endforeach
                </div>
            @endif

        </div>
    </div>
</section>
@endif
