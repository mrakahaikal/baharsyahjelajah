@props(['locale', 'localeUrls' => [], 'navLinks', 'menuCategories', 'contactUrl'])

<div id="mobile-navigation" x-show="mobileMenuOpen" x-collapse x-cloak class="md:hidden bg-white/95 backdrop-blur-md border-t border-slate-200/50 shadow-lg rounded-b-2xl">
    <div class="px-4 pt-2 pb-6 space-y-4">
        <nav class="grid grid-cols-2 gap-2" aria-label="Mobile Navigation">
            @foreach($navLinks as $link)
                @php $isActive = request()->routeIs($link['match']); @endphp
                <a href="{{ route($link['route'], ['locale' => $locale]) }}"
                   class="flex items-center gap-2 rounded-xl px-3.5 py-3 text-sm font-semibold transition-[background-color,color] duration-200 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 {{ $isActive ? 'bg-blue-50 text-blue-700' : 'bg-slate-50 text-slate-700 hover:bg-slate-100' }}" @if($isActive) aria-current="page" @endif>
                    @if($link['icon'] === 'car')
                        <x-lucide-car class="h-4 w-4 shrink-0 text-blue-500" aria-hidden="true" />
                    @elseif($link['icon'] === 'map')
                        <x-lucide-map class="h-4 w-4 shrink-0 text-blue-500" aria-hidden="true" />
                    @elseif($link['icon'] === 'moon')
                        <x-lucide-moon class="h-4 w-4 shrink-0 text-blue-500" aria-hidden="true" />
                    @elseif($link['icon'] === 'badge')
                        <x-lucide-badge-check class="h-4 w-4 shrink-0 text-blue-500" aria-hidden="true" />
                    @elseif($link['icon'] === 'bag')
                        <x-lucide-shopping-bag class="h-4 w-4 shrink-0 text-blue-500" aria-hidden="true" />
                    @else
                        <x-lucide-file-text class="h-4 w-4 shrink-0 text-blue-500" aria-hidden="true" />
                    @endif
                    <span>{{ $link['label'] }}</span>
                </a>
            @endforeach
        </nav>

        <div class="border-t border-slate-100 pt-4 space-y-3">
            @if($menuCategories->isNotEmpty())
                <div class="flex flex-col gap-1.5">
                    <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Kategori Tour</span>
                    <div class="grid grid-cols-1 gap-2">
                        @foreach($menuCategories as $cat)
                            <a href="{{ route('tour.index', ['locale' => $locale, 'category' => $cat->slug]) }}"
                               class="flex items-center justify-between rounded-xl bg-slate-50 px-3.5 py-3 text-sm font-semibold text-slate-700 transition-colors hover:bg-slate-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                                <span>{{ $cat->name }}</span>
                                <span class="text-xs text-slate-400">{{ $cat->active_tours_count }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="flex flex-col gap-1.5">
                <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Pilih Bahasa</span>
                <div class="flex flex-wrap gap-2">
                    @foreach(['id' => 'Indonesian', 'ms' => 'Malay', 'en' => 'English'] as $code => $label)
                        @php $isCurrent = $locale === $code; @endphp
                        <a href="{{ $localeUrls[$code] ?? route(Route::currentRouteName() ?? 'home', array_merge(Route::current()?->parameters() ?? [], ['locale' => $code])) }}"
                           class="rounded-full px-4 py-2 text-xs font-semibold border transition-[background-color,border-color,color] duration-200 {{ $isCurrent ? 'bg-blue-50 border-blue-200 text-blue-700 font-bold' : 'bg-slate-50 border-slate-200/60 text-slate-600 hover:bg-slate-100' }}">
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
                           class="rounded-full px-4 py-2 text-xs font-semibold border transition-[background-color,border-color,color] duration-200 {{ $isCurrent ? 'bg-blue-50 border-blue-200 text-blue-700 font-bold' : 'bg-slate-50 border-slate-200/60 text-slate-600 hover:bg-slate-100' }}">
                            {{ $code }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="pt-2">
            <x-ui::button tag="a" :href="$contactUrl" class="block w-full text-center">
                {{ __('frontend.footer.contact') }}
            </x-ui::button>
        </div>
    </div>
</div>
