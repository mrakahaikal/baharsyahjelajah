@php
    $locale = app()->getLocale();
    $contactUrl = route('contact.index', ['locale' => $locale]);
    $navLinks = [
        ['route' => 'transport.index', 'match' => 'transport.*', 'label' => __('frontend.nav.transport'), 'icon' => 'car'],
        ['route' => 'tour.index', 'match' => 'tour.*', 'label' => __('frontend.nav.tour'), 'icon' => 'map'],
        ['route' => 'umroh.index', 'match' => 'umroh.*', 'label' => __('frontend.nav.umroh'), 'icon' => 'moon'],
        ['route' => 'visa.index', 'match' => 'visa.*', 'label' => __('frontend.nav.visa'), 'icon' => 'badge'],
        ['route' => 'blog.index', 'match' => 'blog.*', 'label' => __('frontend.nav.blog'), 'icon' => 'file'],
    ];
    $languages = [
        'id' => [
            'label' => __('frontend.header.switchers.languages.id'),
            'flag' => asset('images/flags/indonesia-flag.webp'),
        ],
        'ms' => [
            'label' => __('frontend.header.switchers.languages.ms'),
            'flag' => asset('images/flags/malaysia-flag.webp'),
        ],
        'en' => [
            'label' => __('frontend.header.switchers.languages.en'),
            'flag' => asset('images/flags/uk-flag.webp'),
        ],
    ];
    $currencies = collect(config('currencies.supported'))
        ->mapWithKeys(fn (array $metadata, string $code) => [
            $code => __("frontend.header.switchers.currencies.{$code}"),
        ])
        ->all();
    $currentCurrency = \App\Helpers\LocaleHelper::currency();
@endphp

<header class="bg-white sticky top-0 z-50 border-b border-gray-100" x-data="{ mobileMenuOpen: false, tourMenuOpen: false }" @mouseleave="tourMenuOpen = false" @keydown.escape.window="tourMenuOpen = false; mobileMenuOpen = false">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-20">
            <a href="{{ route('home', ['locale' => $locale]) }}" class="flex items-center gap-2" aria-label="Baharsyah Jelajah Home">
                <x-shared::application-logo />
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

            <div class="hidden items-center gap-3 md:flex">
                <div class="hidden items-center gap-1 rounded-lg border border-slate-200 bg-slate-50 p-1 lg:flex" x-data="{ switcherOpen: null }" @click.outside="switcherOpen = null" @keydown.escape.window="switcherOpen = null">
                    <div class="relative">
                        <button type="button" @click="switcherOpen = switcherOpen === 'language' ? null : 'language'" class="flex min-h-10 items-center gap-2 rounded-md px-2.5 text-xs font-bold uppercase text-slate-700 transition-colors hover:bg-white hover:text-slate-950 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600" aria-label="{{ __('frontend.header.switchers.language_current', ['language' => $languages[$locale]['label']]) }}" aria-haspopup="true" aria-controls="language-switcher" x-bind:aria-expanded="(switcherOpen === 'language').toString()">
                            <img src="{{ $languages[$locale]['flag'] }}" alt="" width="28" height="20" class="h-5 w-7 rounded-sm border border-slate-200 object-cover" aria-hidden="true">
                            <span>{{ strtoupper($locale) }}</span>
                            <x-lucide-chevron-down class="h-3.5 w-3.5 text-slate-400 transition-transform" x-bind:class="{ 'rotate-180': switcherOpen === 'language' }" aria-hidden="true" />
                        </button>

                        <div id="language-switcher" x-show="switcherOpen === 'language'" x-cloak x-transition.origin.top.right class="absolute right-0 top-full z-60 mt-2 w-56 rounded-lg border border-slate-200 bg-white p-1.5 shadow-xl shadow-slate-950/10" aria-label="{{ __('frontend.header.switchers.language') }}">
                            @foreach($languages as $code => $language)
                                @php $isCurrent = $locale === $code; @endphp
                                <a href="{{ $localeUrls[$code] ?? route(Route::currentRouteName() ?? 'home', array_merge(Route::current()?->parameters() ?? [], ['locale' => $code])) }}" class="flex min-h-11 items-center gap-3 rounded-md px-3 text-sm font-semibold transition-colors {{ $isCurrent ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-950' }}" @if($isCurrent) aria-current="page" @endif>
                                    <img src="{{ $language['flag'] }}" alt="" width="28" height="20" class="h-5 w-7 shrink-0 rounded-sm border border-slate-200 object-cover" aria-hidden="true">
                                    <span class="min-w-0 flex-1">{{ $language['label'] }}</span>
                                    <span class="text-[10px] font-bold uppercase text-slate-400">{{ $code }}</span>
                                    @if($isCurrent)
                                        <x-lucide-check class="h-4 w-4 shrink-0" aria-hidden="true" />
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    </div>

                    <div class="relative">
                        <button type="button" @click="switcherOpen = switcherOpen === 'currency' ? null : 'currency'" class="flex min-h-10 items-center gap-2 rounded-md px-2.5 text-xs font-bold text-slate-700 transition-colors hover:bg-white hover:text-slate-950 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600" aria-label="{{ __('frontend.header.switchers.currency_current', ['currency' => $currentCurrency]) }}" aria-haspopup="true" aria-controls="currency-switcher" x-bind:aria-expanded="(switcherOpen === 'currency').toString()">
                            <x-lucide-circle-dollar-sign class="h-4 w-4 text-slate-500" aria-hidden="true" />
                            <span>{{ $currentCurrency }}</span>
                            <x-lucide-chevron-down class="h-3.5 w-3.5 text-slate-400 transition-transform" x-bind:class="{ 'rotate-180': switcherOpen === 'currency' }" aria-hidden="true" />
                        </button>

                        <div id="currency-switcher" x-show="switcherOpen === 'currency'" x-cloak x-transition.origin.top.right class="absolute right-0 top-full z-60 mt-2 max-h-[min(32rem,calc(100vh-7rem))] w-64 overflow-y-auto rounded-lg border border-slate-200 bg-white p-1.5 shadow-xl shadow-slate-950/10" aria-label="{{ __('frontend.header.switchers.currency') }}">
                            @foreach($currencies as $code => $label)
                                @php $isCurrent = $currentCurrency === $code; @endphp
                                <a href="{{ route('set.currency', ['currency' => $code]) }}" class="flex min-h-11 items-center justify-between gap-3 rounded-md px-3 text-sm font-semibold transition-colors {{ $isCurrent ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-950' }}" @if($isCurrent) aria-current="true" @endif>
                                    <span>{{ $label }}</span>
                                    <span class="inline-flex items-center gap-2 text-xs font-bold">
                                        {{ $code }}
                                        @if($isCurrent)
                                            <x-lucide-check class="h-4 w-4" aria-hidden="true" />
                                        @endif
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>

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

    <x-shared::header.mobile-nav :$locale :$localeUrls :$navLinks :$menuCategories :$contactUrl :$languages :$currencies :$currentCurrency />

</header>
