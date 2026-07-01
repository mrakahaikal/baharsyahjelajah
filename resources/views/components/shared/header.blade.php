@php
    $locale = app()->getLocale();
    $waNumber = app(\App\Settings\GeneralSettings::class)->whatsapp_number;
    $navLinks = [
//        ['route' => 'transport.index', 'match' => 'transport.*', 'label' => __('frontend.nav.transport'), 'icon' => 'car'],
        ['route' => 'tour.index', 'match' => 'tour.*', 'label' => __('frontend.nav.tour'), 'icon' => 'map'],
//        ['route' => 'umroh.index', 'match' => 'umroh.*', 'label' => __('frontend.nav.umroh'), 'icon' => 'moon'],
//        ['route' => 'visa.index', 'match' => 'visa.*', 'label' => __('frontend.nav.visa'), 'icon' => 'badge'],
        ['route' => 'blog.index', 'match' => 'blog.*', 'label' => __('frontend.nav.blog'), 'icon' => 'file'],
    ];

    $menuCategories = \App\Models\TourCategory::ordered()
        ->withCount(['tours' => function ($query) {
            $query->where('is_active', true);
        }])
        ->take(4)
        ->get();

    $menuFeaturedTours = \App\Models\Tour::where('is_featured', true)
        ->where('is_active', true)
        ->take(2)
        ->get();
@endphp

<div class="sticky top-4 z-50 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pointer-events-none w-full">
    <header class="bg-white/80 backdrop-blur-md border border-slate-200/50 shadow-lg rounded-2xl pointer-events-auto transition-all duration-200" x-data="{ mobileMenuOpen: false, tourMenuOpen: false }" @mouseleave="tourMenuOpen = false">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-20">
            <a href="{{ route('home', ['locale' => $locale]) }}" class="flex items-center gap-2" aria-label="Baharsyah Jelajah Home">
                <img src="{{ asset('images/logo-baharsyah-jelajah.png') }}" alt="Logo Baharsyah Jelajah" title="Logo Baharsyah Jelajah" class="h-10 w-auto object-contain lg:h-11">
            </a>

            <nav class="hidden md:flex gap-8 items-center" aria-label="Main Navigation">
                @foreach($navLinks as $link)
                    @php $isActive = request()->routeIs($link['match']); @endphp
                    @if($link['route'] === 'tour.index')
                        <div class="relative inline-flex items-center" @mouseenter="tourMenuOpen = true">
                            <a href="{{ route($link['route'], ['locale' => $locale]) }}" class="text-sm font-medium inline-flex items-center gap-1 {{ $isActive ? 'text-slate-900' : 'text-slate-500' }} hover:text-blue-600 transition-colors py-6 -my-6">
                                <span>{{ $link['label'] }}</span>
                                <x-lucide-chevron-down class="h-3.5 w-3.5 text-slate-400 transition-transform duration-200" ::class="{ 'rotate-180': tourMenuOpen }" />
                            </a>
                        </div>
                    @else
                        <a href="{{ route($link['route'], ['locale' => $locale]) }}" class="text-sm font-medium {{ $isActive ? 'text-slate-900' : 'text-slate-500' }} hover:text-blue-600 transition-colors">
                            {{ $link['label'] }}
                        </a>
                    @endif
                @endforeach
            </nav>

            <div class="hidden md:flex items-center gap-4">
                <div class="hidden lg:flex items-center gap-2">
                    <div x-data="{ open: false }" class="relative">
                        <button type="button" @click="open = !open"
                                class="inline-flex items-center gap-1.5 rounded-full border border-slate-200/80 bg-slate-50/50 px-3.5 py-2 text-xs font-semibold text-slate-700 backdrop-blur-md transition-all duration-200 hover:bg-slate-100 hover:border-slate-300 hover:text-slate-900"
                                :aria-expanded="open">
                            <x-lucide-languages class="h-3.5 w-3.5 text-slate-500" />
                            <span class="uppercase tracking-wider">{{ $locale }}</span>
                            <x-lucide-chevron-down class="h-3.5 w-3.5 text-slate-400 transition-transform duration-200" x-bind:class="{ 'rotate-180': open }" />
                        </button>
                        <div x-show="open" x-cloak @click.away="open = false"
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-52 origin-top-right overflow-hidden rounded-2xl border border-slate-100 bg-white/95 p-1.5 shadow-xl shadow-slate-900/10 backdrop-blur-lg">
                            @foreach(['id' => 'Bahasa Indonesia', 'ms' => 'Bahasa Melayu', 'en' => 'English'] as $code => $label)
                                @php $isCurrent = $locale === $code; @endphp
                                <a href="{{ route(Route::currentRouteName() ?? 'home', array_merge(Route::current()?->parameters() ?? [], ['locale' => $code])) }}"
                                   class="flex items-center justify-between rounded-xl px-3.5 py-2 text-xs font-semibold transition-all duration-200 {{ $isCurrent ? 'bg-slate-50 text-blue-600 font-bold' : 'text-slate-600 hover:bg-slate-50/80 hover:text-slate-900' }}">
                                    <span>{{ $label }}</span>
                                    @if($isCurrent)
                                        <x-lucide-check class="h-3.5 w-3.5 text-blue-600" />
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    </div>

                    <div x-data="{ open: false }" class="relative">
                        <button type="button" @click="open = !open"
                                class="inline-flex items-center gap-1.5 rounded-full border border-slate-200/80 bg-slate-50/50 px-3.5 py-2 text-xs font-semibold text-slate-700 backdrop-blur-md transition-all duration-200 hover:bg-slate-100 hover:border-slate-300 hover:text-slate-900"
                                :aria-expanded="open">
                            <x-lucide-circle-dollar-sign class="h-3.5 w-3.5 text-slate-500" />
                            <span class="tracking-wider">{{ \App\Helpers\LocaleHelper::currency() }}</span>
                            <x-lucide-chevron-down class="h-3.5 w-3.5 text-slate-400 transition-transform duration-200" x-bind:class="{ 'rotate-180': open }" />
                        </button>
                        <div x-show="open" x-cloak @click.away="open = false"
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-48 origin-top-right overflow-hidden rounded-2xl border border-slate-100 bg-white/95 p-1.5 shadow-xl shadow-slate-900/10 backdrop-blur-lg">
                            @foreach(['IDR' => 'Rupiah', 'MYR' => 'Ringgit', 'SGD' => 'Dollar'] as $code => $label)
                                @php $isCurrent = \App\Helpers\LocaleHelper::currency() === $code; @endphp
                                <a href="{{ route('set.currency', ['currency' => $code]) }}"
                                   class="flex items-center justify-between rounded-xl px-3.5 py-2 text-xs font-semibold transition-all duration-200 {{ $isCurrent ? 'bg-slate-50 text-blue-600 font-bold' : 'text-slate-600 hover:bg-slate-50/80 hover:text-slate-900' }}">
                                    <div class="flex items-center gap-1.5">
                                        <span>{{ $label }}</span>
                                        <span class="text-[10px] text-slate-400 font-medium bg-slate-100 px-1.5 py-0.5 rounded">{{ $code }}</span>
                                    </div>
                                    @if($isCurrent)
                                        <x-lucide-check class="h-3.5 w-3.5 text-blue-600" />
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>

                <button class="bg-slate-900 hover:bg-slate-800 text-white px-6 py-2.5 rounded-full text-sm font-semibold transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-900">
                    Book Trip
                </button>
            </div>

            <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden p-2 text-slate-600" aria-label="Toggle Menu" :aria-expanded="mobileMenuOpen">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    <path x-show="mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" x-cloak/>
                </svg>
            </button>
        </div>
    </div>

    <div x-show="mobileMenuOpen" x-collapse x-cloak class="md:hidden bg-white/95 backdrop-blur-md border-t border-slate-200/50 shadow-lg rounded-b-2xl">
        <div class="px-4 pt-2 pb-6 space-y-4">
            <nav class="grid grid-cols-2 gap-2" aria-label="Mobile Navigation">
                @foreach($navLinks as $link)
                    @php $isActive = request()->routeIs($link['match']); @endphp
                    <a href="{{ route($link['route'], ['locale' => $locale]) }}"
                       class="flex items-center gap-2 rounded-xl px-3.5 py-3 text-sm font-semibold transition-all duration-200 {{ $isActive ? 'bg-blue-50 text-blue-700' : 'bg-slate-50 text-slate-700 hover:bg-slate-100' }}">
                        @if($link['icon'] === 'car')
                            <x-lucide-car class="h-4 w-4 shrink-0 text-blue-500" />
                        @elseif($link['icon'] === 'map')
                            <x-lucide-map class="h-4 w-4 shrink-0 text-blue-500" />
                        @elseif($link['icon'] === 'moon')
                            <x-lucide-moon class="h-4 w-4 shrink-0 text-blue-500" />
                        @elseif($link['icon'] === 'badge')
                            <x-lucide-badge-check class="h-4 w-4 shrink-0 text-blue-500" />
                        @elseif($link['icon'] === 'bag')
                            <x-lucide-shopping-bag class="h-4 w-4 shrink-0 text-blue-500" />
                        @else
                            <x-lucide-file-text class="h-4 w-4 shrink-0 text-blue-500" />
                        @endif
                        <span>{{ $link['label'] }}</span>
                    </a>
                @endforeach
            </nav>

            <div class="border-t border-slate-100 pt-4 space-y-3">
                <div class="flex flex-col gap-1.5">
                    <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Pilih Bahasa</span>
                    <div class="flex flex-wrap gap-2">
                        @foreach(['id' => 'Indonesian', 'ms' => 'Malay', 'en' => 'English'] as $code => $label)
                            @php $isCurrent = $locale === $code; @endphp
                            <a href="{{ route(Route::currentRouteName() ?? 'home', array_merge(Route::current()?->parameters() ?? [], ['locale' => $code])) }}"
                               class="rounded-full px-4 py-2 text-xs font-semibold border transition-all duration-200 {{ $isCurrent ? 'bg-blue-50 border-blue-200 text-blue-700 font-bold' : 'bg-slate-50 border-slate-200/60 text-slate-600 hover:bg-slate-100' }}">
                                {{ $label }}
                            </a>
                        @endforeach
                    </div>
                </div>

                <div class="flex flex-col gap-1.5 pt-2">
                    <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Pilih Mata Uang</span>
                    <div class="flex flex-wrap gap-2">
                        @foreach(['IDR', 'MYR', 'SGD'] as $code)
                            @php $isCurrent = \App\Helpers\LocaleHelper::currency() === $code; @endphp
                            <a href="{{ route('set.currency', ['currency' => $code]) }}"
                               class="rounded-full px-4 py-2 text-xs font-semibold border transition-all duration-200 {{ $isCurrent ? 'bg-blue-50 border-blue-200 text-blue-700 font-bold' : 'bg-slate-50 border-slate-200/60 text-slate-600 hover:bg-slate-100' }}">
                                {{ $code }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="pt-2">
                <button class="w-full bg-slate-900 hover:bg-slate-800 text-white px-6 py-3 rounded-full text-sm font-semibold transition-colors shadow-lg shadow-slate-900/10">
                    Book Trip
                </button>
            </div>
        </div>
    </div>

    <!-- Mega Menu for Tours -->
    <div x-show="tourMenuOpen"
         x-cloak
         @mouseenter="tourMenuOpen = true"
         @mouseleave="tourMenuOpen = false"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         class="absolute top-full left-0 w-full pt-3 z-40">
        <div class="bg-white/95 backdrop-blur-md border border-slate-200/50 shadow-2xl rounded-2xl py-8 px-8">
            <div class="grid grid-cols-12 gap-8">
                <!-- Categories & Destinations (col-span-4) -->
                <div class="col-span-4 border-r border-slate-100 pr-8">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-4">Kategori & Destinasi</h3>
                    <div class="space-y-1">
                        @foreach($menuCategories as $cat)
                            <a href="{{ route('tour.index', ['locale' => $locale, 'category' => $cat->slug]) }}"
                               class="group flex items-center justify-between rounded-2xl p-3 hover:bg-slate-50 transition-colors">
                                <div>
                                    <h4 class="text-sm font-bold text-slate-900 group-hover:text-blue-600 transition-colors">{{ $cat->name }}</h4>
                                    <p class="text-xs text-slate-500 mt-0.5">{{ $cat->tours_count }} paket tersedia</p>
                                </div>
                                <x-lucide-chevron-right class="h-4 w-4 text-slate-400 group-hover:translate-x-1 transition-transform duration-200" />
                            </a>
                        @endforeach
                    </div>
                </div>

                <!-- Featured Recommendations (col-span-8) -->
                <div class="col-span-8">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Rekomendasi Wisata Utama</h3>
                        <a href="{{ route('tour.index', ['locale' => $locale]) }}" class="text-xs font-bold text-blue-600 hover:text-blue-700 inline-flex items-center gap-1">
                            Lihat Semua Tour
                            <x-lucide-arrow-right class="h-3.5 w-3.5" />
                        </a>
                    </div>
                    <div class="grid grid-cols-2 gap-6">
                        @foreach($menuFeaturedTours as $menuTour)
                            <x-ui.tour-card :tour="$menuTour" :locale="$locale" imageHeight="h-36" />
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
</div>
