<header class="bg-white border-b border-gray-100 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-14 sm:h-16">

            <!-- Logo -->
            <div class="shrink-0 flex items-center">
                <a href="{{ route('home', ['locale' => app()->getLocale()]) }}" class="block">
                    <img src="{{ asset('images/logo-baharsyah-jelajah.png') }}" alt="Baharsyah Jelajah" class="h-9 sm:h-10 w-auto object-contain">
                </a>
            </div>

            <!-- Desktop Navigation -->
            <nav class="hidden md:flex items-center space-x-1 lg:space-x-2">
                @php
                    $navLinks = [
                        ['route' => 'transport.index', 'match' => 'transport.*', 'label' => __('frontend.nav.transport')],
                        ['route' => 'tour.index',      'match' => 'tour.*',      'label' => __('frontend.nav.tour')],
                        ['route' => 'umroh.index',     'match' => 'umroh.*',     'label' => __('frontend.nav.umroh')],
                        ['route' => 'visa.index',      'match' => 'visa.*',      'label' => __('frontend.nav.visa')],
                        ['route' => 'shop.index',      'match' => 'shop.*',      'label' => __('frontend.nav.shop')],
                        ['route' => 'blog.index',      'match' => 'blog.*',      'label' => __('frontend.nav.blog')],
                    ];
                @endphp
                @foreach($navLinks as $link)
                    @php $isActive = request()->routeIs($link['match']); @endphp
                    <a href="{{ route($link['route'], ['locale' => app()->getLocale()]) }}"
                       class="px-3 py-2 text-sm font-medium rounded-lg transition-colors
                              {{ $isActive
                                  ? 'text-primary bg-primary/8 font-semibold'
                                  : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}">
                        {{ $link['label'] }}
                    </a>
                @endforeach
            </nav>

            <!-- Language & Currency -->
            <div class="hidden md:flex items-center gap-2">
                <!-- Language -->
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open"
                            class="flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-50 rounded-lg transition-colors border border-transparent hover:border-gray-200">
                        <x-lucide-languages class="w-4 h-4" />
                        <span class="uppercase">{{ app()->getLocale() }}</span>
                        <x-lucide-chevron-down class="w-3.5 h-3.5 text-gray-400" />
                    </button>

                    <div x-show="open"
                         x-cloak
                         @click.away="open = false"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="absolute right-0 mt-1.5 w-40 bg-white rounded-xl shadow-lg border border-gray-100 z-50 py-1.5 overflow-hidden">
                        @foreach(['id' => 'Bahasa Indonesia', 'ms' => 'Bahasa Melayu', 'en' => 'English'] as $code => $label)
                            <a href="{{ route(Route::currentRouteName() ?? 'home', array_merge(Route::current()?->parameters() ?? [], ['locale' => $code])) }}"
                               class="flex items-center justify-between px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-colors {{ app()->getLocale() === $code ? 'text-primary font-medium' : '' }}">
                                {{ $label }}
                                @if(app()->getLocale() === $code)
                                    <x-lucide-check class="w-3.5 h-3.5 text-primary" />
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>

                <!-- Currency -->
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open"
                            class="flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-50 rounded-lg transition-colors border border-transparent hover:border-gray-200">
                        <x-lucide-circle-dollar-sign class="w-4 h-4" />
                        <span>{{ \App\Helpers\LocaleHelper::currency() }}</span>
                        <x-lucide-chevron-down class="w-3.5 h-3.5 text-gray-400" />
                    </button>

                    <div x-show="open"
                         x-cloak
                         @click.away="open = false"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="absolute right-0 mt-1.5 w-40 bg-white rounded-xl shadow-lg border border-gray-100 z-50 py-1.5 overflow-hidden">
                        @foreach(['IDR' => 'Rupiah (IDR)', 'MYR' => 'Ringgit (MYR)', 'SGD' => 'Dollar (SGD)'] as $code => $label)
                            <a href="{{ route('set.currency', ['currency' => $code]) }}"
                               class="flex items-center justify-between px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-colors {{ \App\Helpers\LocaleHelper::currency() === $code ? 'text-primary font-medium' : '' }}">
                                {{ $label }}
                                @if(\App\Helpers\LocaleHelper::currency() === $code)
                                    <x-lucide-check class="w-3.5 h-3.5 text-primary" />
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Mobile menu -->
            <div class="md:hidden" x-data="{ open: false }">
                <button @click="open = !open"
                        class="p-2 rounded-lg text-gray-500 hover:text-gray-900 hover:bg-gray-100 transition-colors">
                    <x-lucide-menu x-show="!open" class="h-5 w-5" />
                    <x-lucide-x x-show="open" class="h-5 w-5" x-cloak />
                </button>

                <!-- Mobile Drawer -->
                <div x-show="open"
                     x-cloak
                     @click.away="open = false"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 -translate-y-2"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="absolute top-full left-0 w-full bg-white shadow-md border-b border-gray-100 z-50">
                    <div class="max-w-7xl mx-auto px-4 py-3 space-y-0.5">
                        @foreach($navLinks as $link)
                            @php $isActiveMobile = request()->routeIs($link['match']); @endphp
                            <a href="{{ route($link['route'], ['locale' => app()->getLocale()]) }}"
                               class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
                                      {{ $isActiveMobile
                                          ? 'bg-primary/8 text-primary font-semibold'
                                          : 'text-gray-700 hover:bg-gray-50 hover:text-primary' }}">
                                {{ $link['label'] }}
                            </a>
                        @endforeach

                        <div class="pt-2 pb-1 border-t border-gray-100 mt-2 flex items-center gap-3 px-3">
                            @foreach(['id' => 'ID', 'ms' => 'MY', 'en' => 'EN'] as $code => $label)
                                <a href="{{ route(Route::currentRouteName() ?? 'home', array_merge(Route::current()?->parameters() ?? [], ['locale' => $code])) }}"
                                   class="text-xs font-medium px-2.5 py-1 rounded-full transition-colors {{ app()->getLocale() === $code ? 'bg-primary text-white' : 'text-gray-500 hover:text-gray-900' }}">
                                    {{ $label }}
                                </a>
                            @endforeach
                            <span class="text-gray-200">|</span>
                            @foreach(['IDR', 'MYR', 'SGD'] as $code)
                                <a href="{{ route('set.currency', ['currency' => $code]) }}"
                                   class="text-xs font-medium px-2.5 py-1 rounded-full transition-colors {{ \App\Helpers\LocaleHelper::currency() === $code ? 'bg-primary text-white' : 'text-gray-500 hover:text-gray-900' }}">
                                    {{ $code }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</header>
