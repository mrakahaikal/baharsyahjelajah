@php
    $locale = app()->getLocale();
    $contactUrl = route('contact.index', ['locale' => $locale]);
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

<header class="bg-white sticky top-0 z-50 border-b border-gray-100" x-data="{ mobileMenuOpen: false, tourMenuOpen: false }" @mouseleave="tourMenuOpen = false" @keydown.escape.window="tourMenuOpen = false; mobileMenuOpen = false">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-20">
            <a href="{{ route('home', ['locale' => $locale]) }}" class="flex items-center gap-2" aria-label="Baharsyah Jelajah Home">
                @if(request()->routeIs('umroh.*'))
                    <img src="{{ asset('images/logo-baharsyah-jelajah-umrah.png') }}" alt="Logo Baharsyah Jelajah Umrah" title="Logo Baharsyah Jelajah Umrah" width="176" height="44" class="h-10 w-auto object-contain lg:h-11">
                @else
                    <img src="{{ asset('images/logo-baharsyah-jelajah.webp') }}" alt="Logo Baharsyah Jelajah" title="Logo Baharsyah Jelajah" width="176" height="44" class="h-10 w-auto object-contain lg:h-11">
                @endif
            </a>

            <nav class="hidden md:flex gap-8" aria-label="Main Navigation">
                @foreach($navLinks as $link)
                    @php $isActive = request()->routeIs($link['match']); @endphp
                    @if($link['route'] === 'tour.index')
                        <div class="relative inline-flex items-center" @mouseenter="tourMenuOpen = true" @focusin="tourMenuOpen = true">
                            <a href="{{ route($link['route'], ['locale' => $locale]) }}" class="text-sm font-medium inline-flex items-center gap-1 rounded-md py-6 -my-6 transition-colors {{ $isActive ? 'text-slate-900' : 'text-slate-500' }} hover:text-blue-600 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-blue-600" aria-haspopup="true" aria-controls="tour-mega-menu" x-bind:aria-expanded="tourMenuOpen.toString()" @if($isActive) aria-current="page" @endif>
                                <span>{{ $link['label'] }}</span>
                                <x-lucide-chevron-down class="h-3.5 w-3.5 text-slate-400 transition-transform duration-200" x-bind:class="{ 'rotate-180': tourMenuOpen }" aria-hidden="true" />
                            </a>
                        </div>
                    @else
                        <a href="{{ route($link['route'], ['locale' => $locale]) }}" class="rounded-md text-sm font-medium transition-colors {{ $isActive ? 'text-slate-900' : 'text-slate-500' }} hover:text-blue-600 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-blue-600" @if($isActive) aria-current="page" @endif>
                            {{ $link['label'] }}
                        </a>
                    @endif
                @endforeach
            </nav>

            <div class="hidden md:block">
                <x-ui::button tag="a" href="{{ route('contact.index', ['locale' => $locale]) }}">
                    {{ __('frontend.footer.contact') }}
                </x-ui::button>
            </div>

            <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden p-2 text-slate-600" aria-label="Toggle Menu" :aria-expanded="mobileMenuOpen">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    <path x-show="mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" x-cloak/>
                </svg>
            </button>
        </div>

        <x-shared.header.tour-mega-menu :$locale :$menuCategories :$menuFeaturedTours />
    </div>

    <x-shared::header.mobile-nav :$locale :$navLinks :$menuCategories :$contactUrl />

</header>

{{--<div class="fixed inset-x-0 top-3 z-50 mx-auto w-full max-w-7xl px-4 pointer-events-none sm:px-6 lg:top-4 lg:px-8">--}}
{{--    <a href="#main-content" class="pointer-events-auto sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-full focus:bg-slate-950 focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">--}}
{{--        Lewati ke konten utama--}}
{{--    </a>--}}
{{--    <header class="bg-white/85 backdrop-blur-md border border-slate-200/50 shadow-lg rounded-2xl pointer-events-auto transition-[background-color,border-color,box-shadow] duration-200" x-data="{ mobileMenuOpen: false, tourMenuOpen: false }" @mouseleave="tourMenuOpen = false" @keydown.escape.window="tourMenuOpen = false; mobileMenuOpen = false">--}}
{{--    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">--}}
{{--        <div class="flex justify-between items-center h-20">--}}
{{--            <a href="{{ route('home', ['locale' => $locale]) }}" class="flex items-center gap-2" aria-label="Baharsyah Jelajah Home">--}}
{{--                <img src="{{ asset('images/logo-baharsyah-jelajah.webp') }}" alt="Logo Baharsyah Jelajah" title="Logo Baharsyah Jelajah" width="176" height="44" class="h-10 w-auto object-contain lg:h-11">--}}
{{--            </a>--}}

{{--            <nav class="hidden md:flex gap-8 items-center" aria-label="Main Navigation">--}}
{{--                @foreach($navLinks as $link)--}}
{{--                    @php $isActive = request()->routeIs($link['match']); @endphp--}}
{{--                    @if($link['route'] === 'tour.index')--}}
{{--                        <div class="relative inline-flex items-center" @mouseenter="tourMenuOpen = true" @focusin="tourMenuOpen = true">--}}
{{--                            <a href="{{ route($link['route'], ['locale' => $locale]) }}" class="text-sm font-medium inline-flex items-center gap-1 rounded-md py-6 -my-6 transition-colors {{ $isActive ? 'text-slate-900' : 'text-slate-500' }} hover:text-blue-600 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-blue-600" aria-haspopup="true" aria-controls="tour-mega-menu" x-bind:aria-expanded="tourMenuOpen.toString()" @if($isActive) aria-current="page" @endif>--}}
{{--                                <span>{{ $link['label'] }}</span>--}}
{{--                                <x-lucide-chevron-down class="h-3.5 w-3.5 text-slate-400 transition-transform duration-200" ::class="{ 'rotate-180': tourMenuOpen }" aria-hidden="true" />--}}
{{--                            </a>--}}
{{--                        </div>--}}
{{--                    @else--}}
{{--                        <a href="{{ route($link['route'], ['locale' => $locale]) }}" class="rounded-md text-sm font-medium transition-colors {{ $isActive ? 'text-slate-900' : 'text-slate-500' }} hover:text-blue-600 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-blue-600" @if($isActive) aria-current="page" @endif>--}}
{{--                            {{ $link['label'] }}--}}
{{--                        </a>--}}
{{--                    @endif--}}
{{--                @endforeach--}}
{{--            </nav>--}}

{{--            <div class="hidden md:flex items-center gap-3">--}}
{{--                <div class="hidden lg:flex items-center gap-1.5 rounded-full border border-slate-200/70 bg-slate-50/70 p-1 backdrop-blur-md">--}}
{{--                    <div x-data="{ open: false }" class="relative">--}}
{{--                        <button type="button" @click="open = !open" @keydown.escape="open = false"--}}
{{--                                class="grid size-10 place-items-center rounded-full text-slate-600 transition-[background-color,color,box-shadow] duration-200 hover:bg-white hover:text-slate-950 hover:shadow-sm focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600"--}}
{{--                                aria-label="Pilih bahasa, saat ini {{ strtoupper($locale) }}" aria-haspopup="menu" aria-controls="language-menu" x-bind:aria-expanded="open.toString()">--}}
{{--                            <x-lucide-languages class="h-4.5 w-4.5" aria-hidden="true" />--}}
{{--                        </button>--}}
{{--                        <div id="language-menu" x-show="open" x-cloak @click.away="open = false"--}}
{{--                             x-transition:enter="transition ease-out duration-150"--}}
{{--                             x-transition:enter-start="opacity-0 scale-95"--}}
{{--                             x-transition:enter-end="opacity-100 scale-100"--}}
{{--                             x-transition:leave="transition ease-in duration-100"--}}
{{--                             x-transition:leave-start="opacity-100 scale-100"--}}
{{--                             x-transition:leave-end="opacity-0 scale-95"--}}
{{--                             class="absolute right-0 mt-2 w-52 origin-top-right overflow-hidden rounded-2xl border border-slate-100 bg-white/95 p-1.5 shadow-xl shadow-slate-900/10 backdrop-blur-lg" role="menu">--}}
{{--                            @foreach(['id' => 'Bahasa Indonesia', 'ms' => 'Bahasa Melayu', 'en' => 'English'] as $code => $label)--}}
{{--                                @php $isCurrent = $locale === $code; @endphp--}}
{{--                                <a href="{{ route(Route::currentRouteName() ?? 'home', array_merge(Route::current()?->parameters() ?? [], ['locale' => $code])) }}"--}}
{{--                                   class="flex items-center justify-between rounded-xl px-3.5 py-2 text-xs font-semibold transition-[background-color,color] duration-200 {{ $isCurrent ? 'bg-slate-50 text-blue-600 font-bold' : 'text-slate-600 hover:bg-slate-50/80 hover:text-slate-900' }}" role="menuitem">--}}
{{--                                    <span>{{ $label }}</span>--}}
{{--                                    @if($isCurrent)--}}
{{--                                        <x-lucide-check class="h-3.5 w-3.5 text-blue-600" aria-hidden="true" />--}}
{{--                                    @endif--}}
{{--                                </a>--}}
{{--                            @endforeach--}}
{{--                        </div>--}}
{{--                    </div>--}}

{{--                    <div x-data="{ open: false }" class="relative">--}}
{{--                        <button type="button" @click="open = !open" @keydown.escape="open = false"--}}
{{--                                class="grid size-10 place-items-center rounded-full text-slate-600 transition-[background-color,color,box-shadow] duration-200 hover:bg-white hover:text-slate-950 hover:shadow-sm focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600"--}}
{{--                                aria-label="Pilih mata uang, saat ini {{ \App\Helpers\LocaleHelper::currency() }}" aria-haspopup="menu" aria-controls="currency-menu" x-bind:aria-expanded="open.toString()">--}}
{{--                            <x-lucide-circle-dollar-sign class="h-4.5 w-4.5" aria-hidden="true" />--}}
{{--                        </button>--}}
{{--                        <div id="currency-menu" x-show="open" x-cloak @click.away="open = false"--}}
{{--                             x-transition:enter="transition ease-out duration-150"--}}
{{--                             x-transition:enter-start="opacity-0 scale-95"--}}
{{--                             x-transition:enter-end="opacity-100 scale-100"--}}
{{--                             x-transition:leave="transition ease-in duration-100"--}}
{{--                             x-transition:leave-start="opacity-100 scale-100"--}}
{{--                             x-transition:leave-end="opacity-0 scale-95"--}}
{{--                             class="absolute right-0 mt-2 w-48 origin-top-right overflow-hidden rounded-2xl border border-slate-100 bg-white/95 p-1.5 shadow-xl shadow-slate-900/10 backdrop-blur-lg" role="menu">--}}
{{--                            @foreach(['IDR' => 'Rupiah', 'MYR' => 'Ringgit', 'SGD' => 'Dollar'] as $code => $label)--}}
{{--                                @php $isCurrent = \App\Helpers\LocaleHelper::currency() === $code; @endphp--}}
{{--                                <a href="{{ route('set.currency', ['currency' => $code]) }}"--}}
{{--                                   class="flex items-center justify-between rounded-xl px-3.5 py-2 text-xs font-semibold transition-[background-color,color] duration-200 {{ $isCurrent ? 'bg-slate-50 text-blue-600 font-bold' : 'text-slate-600 hover:bg-slate-50/80 hover:text-slate-900' }}" role="menuitem">--}}
{{--                                    <div class="flex items-center gap-1.5">--}}
{{--                                        <span>{{ $label }}</span>--}}
{{--                                        <span class="text-[10px] text-slate-400 font-medium bg-slate-100 px-1.5 py-0.5 rounded">{{ $code }}</span>--}}
{{--                                    </div>--}}
{{--                                    @if($isCurrent)--}}
{{--                                        <x-lucide-check class="h-3.5 w-3.5 text-blue-600" aria-hidden="true" />--}}
{{--                                    @endif--}}
{{--                                </a>--}}
{{--                            @endforeach--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}

{{--                <a href="{{ $contactUrl }}" class="rounded-full bg-slate-900 px-6 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-slate-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-900">--}}
{{--                    Hubungi Kami--}}
{{--                </a>--}}
{{--            </div>--}}

{{--            <button type="button" @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden p-2 text-slate-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 rounded-lg" aria-label="Buka menu navigasi" aria-controls="mobile-navigation" x-bind:aria-expanded="mobileMenuOpen.toString()">--}}
{{--                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">--}}
{{--                    <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />--}}
{{--                    <path x-show="mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" x-cloak/>--}}
{{--                </svg>--}}
{{--            </button>--}}
{{--        </div>--}}
{{--    </div>--}}

{{--</header>--}}
{{--</div>--}}
