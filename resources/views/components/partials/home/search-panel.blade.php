@props(['locale'])

<section id="search-panel" class="relative z-20 -mt-20 scroll-mt-24" aria-labelledby="search-heading">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-xl shadow-slate-950/10 sm:p-7">
            <div class="grid gap-6 lg:grid-cols-[minmax(0,0.75fr)_minmax(0,1.25fr)] lg:items-end">
                <div class="min-w-0">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-600">{{ __('home.search.eyebrow') }}</p>
                    <h2 id="search-heading" class="mt-2 text-balance text-2xl font-extrabold text-slate-950 sm:text-3xl">
                        {{ __('home.search.title') }}
                    </h2>
                    <p class="mt-2 text-sm leading-6 text-slate-500">{{ __('home.search.subtitle') }}</p>
                </div>

                <form method="GET" action="{{ route('tour.index', ['locale' => $locale]) }}" class="grid gap-3 sm:grid-cols-[minmax(0,1.25fr)_minmax(0,0.85fr)_auto]" role="search">
                    <label class="min-w-0 rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 transition-[border-color,box-shadow] focus-within:border-blue-600 focus-within:ring-2 focus-within:ring-blue-600/15">
                        <span class="block text-[11px] font-bold uppercase text-slate-500">{{ __('home.search.destination') }}</span>
                        <span class="mt-1 flex min-w-0 items-center gap-2">
                            <x-lucide-map-pin class="h-4 w-4 shrink-0 text-blue-600" aria-hidden="true" />
                            <input name="destination" type="search" autocomplete="off" placeholder="{{ __('home.search.destination_placeholder') }}" class="min-w-0 w-full bg-transparent text-sm font-semibold text-slate-950 outline-none placeholder:font-normal placeholder:text-slate-400">
                        </span>
                    </label>

                    <label class="min-w-0 rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 transition-[border-color,box-shadow] focus-within:border-blue-600 focus-within:ring-2 focus-within:ring-blue-600/15">
                        <span class="block text-[11px] font-bold uppercase text-slate-500">{{ __('home.search.type') }}</span>
                        <span class="relative mt-1 flex min-w-0 items-center gap-2">
                            <x-lucide-compass class="h-4 w-4 shrink-0 text-blue-600" aria-hidden="true" />
                            <select name="type" autocomplete="off" class="min-w-0 w-full appearance-none bg-transparent pr-6 text-sm font-semibold text-slate-950 outline-none">
                                <option value="">{{ __('home.search.all_types') }}</option>
                                <option value="domestic">{{ __('home.search.domestic') }}</option>
                                <option value="international">{{ __('home.search.international') }}</option>
                            </select>
                            <x-lucide-chevron-down class="pointer-events-none absolute right-0 h-4 w-4 text-slate-400" aria-hidden="true" />
                        </span>
                    </label>

                    <x-ui::button type="submit" size="lg" class="min-h-14 hover:bg-blue-600">
                        <x-slot:icon><x-lucide-search /></x-slot:icon>
                        {{ __('home.search.submit') }}
                    </x-ui::button>
                </form>
            </div>
        </div>
    </div>
</section>
