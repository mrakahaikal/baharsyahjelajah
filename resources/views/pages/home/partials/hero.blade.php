<!-- Hero Section — Fullscreen Cinematic -->
<section x-data="{ searchOpen: false }" class="relative h-svh min-h-150 max-h-250 overflow-hidden">
    <!-- Background image with subtle zoom-in on load -->
    <img src="https://images.unsplash.com/photo-1523906834658-6e24ef2386f9?auto=format&fit=crop&q=80&w=1920"
         alt="Baharsyah Jelajah"
         class="absolute inset-0 w-full h-full object-cover object-center scale-[1.04] motion-safe:animate-[heroZoom_12s_ease-out_forwards]">

    <!-- Gradient layers for depth & legibility -->
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,transparent_40%,rgba(0,0,0,0.5)_100%)]"></div>
    <div class="absolute inset-0 bg-linear-to-t from-black/80 via-black/25 to-transparent"></div>
    <div class="absolute inset-0 bg-linear-to-r from-black/45 via-transparent to-transparent"></div>

    <!-- Hero text content — bottom-left -->
    <div class="absolute inset-0 flex items-end pb-40 sm:pb-44 lg:pb-32 z-10">
        <div class="w-full max-w-7xl mx-auto px-6 sm:px-10 lg:px-16">
            <div class="max-w-[90%] lg:max-w-[52%] reveal-on-scroll">
                <!-- Badge -->
                <span class="inline-flex items-center gap-1.5 bg-white/15 backdrop-blur-sm border border-white/25
                             text-white text-xs font-semibold px-3.5 py-1.5 rounded-full mb-5 uppercase tracking-wide">
                    <x-lucide-map-pin class="w-3 h-3 text-[#89D4CF]" />
                    Destinasi Pilihan
                </span>

                <!-- Headline — Playfair Display -->
                <h1 class="font-display font-bold text-white text-[2.6rem] sm:text-6xl lg:text-7xl leading-[1.1] tracking-tight mb-4">
                    {!! nl2br(e(__('frontend.hero.slides.0.title'))) !!}
                </h1>

                <!-- Subtitle -->
                <p class="text-white/70 text-base sm:text-lg leading-relaxed max-w-md mb-6">
                    {{ __('frontend.hero.slides.0.subtitle') }}
                </p>

                <!-- Trust indicators -->
                <div class="flex flex-wrap items-center gap-x-5 gap-y-2">
                    <div class="flex items-center gap-1.5 text-white/60 text-xs">
                        <x-lucide-shield-check class="w-4 h-4 text-[#89D4CF]" /> 10K+ Pelanggan
                    </div>
                    <div class="hidden sm:block w-px h-3 bg-white/20"></div>
                    <div class="flex items-center gap-1.5 text-white/60 text-xs">
                        <x-lucide-star class="w-4 h-4 text-amber-400 fill-amber-400" /> 98% Kepuasan
                    </div>
                    <div class="hidden sm:block w-px h-3 bg-white/20"></div>
                    <div class="flex items-center gap-1.5 text-white/60 text-xs">
                        <x-lucide-award class="w-4 h-4 text-[#89D4CF]" /> 15+ Tahun
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Frosted glass search bar — anchored to hero bottom -->
    <div class="absolute bottom-0 left-0 right-0 z-20 bg-white/10 backdrop-blur-xl border-t border-white/15">
        <div class="max-w-7xl mx-auto px-6 sm:px-10 lg:px-16 py-4 lg:py-5">

            <!-- Mobile: collapsed toggle that expands the full form -->
            <div class="lg:hidden">
                <button @click="searchOpen = !searchOpen"
                        class="w-full flex items-center justify-between bg-white/20 backdrop-blur-sm
                               border border-white/25 text-white rounded-xl px-4 py-3">
                    <span class="flex items-center gap-2 text-sm font-semibold">
                        <x-lucide-search class="w-4 h-4" />
                        {{ __('frontend.search.buttons.search_tour') }}
                    </span>
                    <x-lucide-chevron-up class="w-4 h-4 transition-transform duration-200"
                                         x-bind:class="searchOpen ? '' : 'rotate-180'" />
                </button>
                <div x-show="searchOpen"
                     x-cloak
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 translate-y-2"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="mt-3 bg-white rounded-2xl p-5 shadow-2xl">
                    @include('pages.home.partials.search-form')
                </div>
            </div>

            <!-- Desktop: inline horizontal search bar -->
            <div class="hidden lg:block" x-data="{ activeTab: 'tour' }">
                <!-- Tab pills -->
                <div class="flex items-center gap-1 mb-4">
                    @php
                        $heroTabs = [
                            'tour' => ['icon' => 'map-pin', 'label' => __('frontend.search.tabs.tour')],
                            'transport' => ['icon' => 'car', 'label' => __('frontend.search.tabs.transport')],
                            'umroh' => ['icon' => 'moon', 'label' => __('frontend.search.tabs.umroh')],
                        ];
                    @endphp
                    @foreach($heroTabs as $tab => $info)
                        <button @click="activeTab = '{{ $tab }}'"
                                :class="activeTab === '{{ $tab }}'
                                    ? 'bg-white text-[#796FE1] shadow-sm'
                                    : 'text-white/80 hover:text-white hover:bg-white/15'"
                                class="px-4 py-1.5 text-xs font-semibold rounded-full transition-all flex items-center gap-1.5">
                            @if($info['icon'] === 'map-pin')
                                <x-lucide-map-pin class="w-3 h-3" />
                            @elseif($info['icon'] === 'car')
                                <x-lucide-car class="w-3 h-3" />
                            @else
                                <x-lucide-moon class="w-3 h-3" />
                            @endif
                            {{ $info['label'] }}
                        </button>
                    @endforeach
                </div>

                <!-- Tour form row -->
                <div x-show="activeTab === 'tour'" class="flex items-center gap-3">
                    <div class="flex-1 relative">
                        <x-lucide-map-pin class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" />
                        <input type="text" placeholder="{{ __('frontend.search.fields.destination_placeholder') }}"
                               class="w-full pl-10 pr-4 py-3 bg-white border-0 rounded-xl text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-[#796FE1]/40">
                    </div>
                    <div class="w-48">
                        <select class="w-full px-4 py-3 bg-white border-0 rounded-xl text-sm shadow-sm appearance-none focus:outline-none focus:ring-2 focus:ring-[#796FE1]/40">
                            <option>{{ __('frontend.search.fields.all_regions') }}</option>
                            <option>Jakarta-Bandung</option>
                            <option>Mesir</option>
                            <option>Internasional</option>
                        </select>
                    </div>
                    <button class="shrink-0 bg-[#796FE1] hover:bg-[#6860d4] text-white font-semibold px-6 py-3 rounded-xl text-sm flex items-center gap-2 transition-colors shadow-sm">
                        <x-lucide-search class="w-4 h-4" />
                        {{ __('frontend.search.buttons.search_tour') }}
                    </button>
                </div>

                <!-- Transport form row -->
                <div x-show="activeTab === 'transport'" x-cloak class="flex items-center gap-3">
                    <div class="flex-1 relative">
                        <x-lucide-users class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" />
                        <input type="number" min="1" placeholder="{{ __('frontend.search.fields.pax_placeholder') }}"
                               class="w-full pl-10 pr-4 py-3 bg-white border-0 rounded-xl text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-[#796FE1]/40">
                    </div>
                    <div class="w-48">
                        <select class="w-full px-4 py-3 bg-white border-0 rounded-xl text-sm shadow-sm appearance-none focus:outline-none focus:ring-2 focus:ring-[#796FE1]/40">
                            <option>{{ __('frontend.search.fields.all_vehicles') }}</option>
                            <option>Ekonomis</option>
                            <option>Premium</option>
                            <option>Bus / Van</option>
                        </select>
                    </div>
                    <button class="shrink-0 bg-[#796FE1] hover:bg-[#6860d4] text-white font-semibold px-6 py-3 rounded-xl text-sm flex items-center gap-2 transition-colors shadow-sm">
                        <x-lucide-search class="w-4 h-4" />
                        {{ __('frontend.search.buttons.search_transport') }}
                    </button>
                </div>

                <!-- Umroh form row -->
                <div x-show="activeTab === 'umroh'" x-cloak class="flex items-center gap-3">
                    <div class="flex-1 relative">
                        <x-lucide-calendar class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" />
                        <select class="w-full pl-10 pr-4 py-3 bg-white border-0 rounded-xl text-sm shadow-sm appearance-none focus:outline-none focus:ring-2 focus:ring-[#796FE1]/40">
                            <option>{{ __('frontend.search.fields.select_month') }}</option>
                            <option>Ramadhan 2026</option>
                            <option>Syawal 2026</option>
                            <option>Dzulhijjah 2026</option>
                        </select>
                    </div>
                    <div class="w-48">
                        <select class="w-full px-4 py-3 bg-white border-0 rounded-xl text-sm shadow-sm appearance-none focus:outline-none focus:ring-2 focus:ring-[#796FE1]/40">
                            <option>{{ __('frontend.search.fields.all_packages') }}</option>
                            <option>All-in (Regular)</option>
                            <option>DIY Umroh</option>
                            <option>Umroh Request</option>
                        </select>
                    </div>
                    <button class="shrink-0 bg-[#796FE1] hover:bg-[#6860d4] text-white font-semibold px-6 py-3 rounded-xl text-sm flex items-center gap-2 transition-colors shadow-sm">
                        <x-lucide-search class="w-4 h-4" />
                        {{ __('frontend.search.buttons.search_umroh') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scroll indicator (desktop) -->
    <div class="absolute bottom-28 left-1/2 -translate-x-1/2 z-10 hidden lg:flex flex-col items-center gap-1 text-white/35 animate-bounce pointer-events-none">
        <span class="text-[10px] uppercase tracking-widest font-semibold">Scroll</span>
        <x-lucide-chevron-down class="w-4 h-4" />
    </div>
</section>
