@php
    $activeCategory = $categories->first(fn ($item) => $item->slug === $category || $item->getTranslation('slug', 'id') === $category);
    $activeType = collect($tourTypes)->first(fn ($item) => $item->value === $type);
    $activeDestination = $destinations->firstWhere('slug', $destinationSlug);
    $loadingTargets = 'destination,destinationSlug,category,type,resetFilters,clearFilter,gotoPage,previousPage,nextPage';
@endphp

<div class="-mt-8" x-data="{ filtersOpen: false }" x-on:keydown.escape.window="filtersOpen = false">
    <section class="relative isolate min-h-100 overflow-hidden bg-slate-950 text-white sm:min-h-112" aria-labelledby="tour-index-heading">
        <img src="{{ $heroImageUrl }}" alt="{{ $heroImageAlt }}" width="1800" height="900" fetchpriority="high" class="absolute inset-0 h-full w-full object-cover object-center">
        <div class="absolute inset-0 bg-slate-950/60" aria-hidden="true"></div>

        <div class="relative mx-auto flex min-h-100 max-w-7xl flex-col justify-center px-4 py-14 sm:min-h-112 sm:px-6 sm:py-16 lg:px-8">
            <p class="text-xs font-bold uppercase tracking-wider text-blue-200">{{ __('frontend.tour.index.hero.eyebrow') }}</p>
            <h1 id="tour-index-heading" class="mt-3 max-w-3xl text-3xl font-extrabold leading-tight text-balance sm:text-4xl lg:text-5xl">
                {{ __('frontend.tour.index.hero.title') }}
            </h1>
            <p class="mt-4 max-w-2xl text-sm leading-7 text-white/80 sm:text-base">
                {{ __('frontend.tour.index.hero.description') }}
            </p>

            <form wire:submit="search" class="mt-7 flex w-full max-w-2xl items-center gap-2 rounded-lg bg-white p-2 shadow-xl shadow-slate-950/20" role="search">
                <x-lucide-search class="ml-2 h-5 w-5 shrink-0 text-slate-400" aria-hidden="true" />
                <label for="tour-search" class="sr-only">{{ __('frontend.tour.index.search.label') }}</label>
                <input id="tour-search" type="search" wire:model.live.debounce.400ms="destination" autocomplete="off" placeholder="{{ __('frontend.tour.index.search.placeholder') }}" class="min-h-11 min-w-0 flex-1 bg-transparent px-1 text-sm font-semibold text-slate-900 outline-none placeholder:font-normal placeholder:text-slate-400">
                @if(filled($destination))
                    <button type="button" wire:click="clearFilter('destination')" class="grid size-11 shrink-0 place-items-center rounded-md text-slate-500 transition-colors hover:bg-slate-100 hover:text-slate-900 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600" title="{{ __('frontend.tour.index.search.clear') }}">
                        <x-lucide-x class="h-5 w-5" aria-hidden="true" />
                        <span class="sr-only">{{ __('frontend.tour.index.search.clear') }}</span>
                    </button>
                @endif
                <button type="submit" class="grid size-11 shrink-0 place-items-center rounded-md bg-blue-600 text-white transition-colors hover:bg-blue-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-200" title="{{ __('frontend.tour.index.search.submit') }}">
                    <x-lucide-arrow-right class="h-5 w-5" aria-hidden="true" />
                    <span class="sr-only">{{ __('frontend.tour.index.search.submit') }}</span>
                </button>
            </form>
        </div>
    </section>

    <section id="tour-results" class="scroll-mt-24 bg-white py-10 sm:py-12" aria-labelledby="tour-results-heading">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div wire:offline class="mb-5 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-900" role="status">
                {{ __('frontend.tour.index.offline') }}
            </div>

            <div class="flex items-center justify-between gap-4 lg:hidden">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-blue-600">{{ __('frontend.tour.index.results.eyebrow') }}</p>
                    <p class="mt-1 text-sm font-semibold text-slate-600" aria-live="polite">
                        {{ trans_choice('frontend.tour.index.results.count', $tours->total(), ['count' => $tours->total()]) }}
                    </p>
                </div>
                <button type="button" x-on:click="filtersOpen = true; $nextTick(() => $refs.filterClose.focus())" class="relative inline-flex min-h-11 items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 text-sm font-bold text-slate-800 shadow-sm">
                    <x-lucide-sliders-horizontal class="h-4 w-4" aria-hidden="true" />
                    {{ __('frontend.tour.index.filters.button') }}
                    @if($this->activeFilterCount > 0)
                        <span class="grid h-5 min-w-5 place-items-center rounded-full bg-blue-600 px-1 text-[10px] text-white">{{ $this->activeFilterCount }}</span>
                    @endif
                </button>
            </div>

            <div class="mt-6 lg:grid lg:grid-cols-[15rem_minmax(0,1fr)] lg:items-start lg:gap-10">
                <aside class="sticky top-24 hidden border-r border-slate-200 pr-8 lg:block" aria-labelledby="desktop-filter-heading">
                    <div class="flex items-center justify-between gap-3 border-b border-slate-200 pb-4">
                        <h2 id="desktop-filter-heading" class="text-base font-extrabold text-slate-900">{{ __('frontend.tour.index.filters.title') }}</h2>
                        @if($this->activeFilterCount > 0)
                            <button type="button" wire:click="resetFilters" class="grid size-9 shrink-0 place-items-center rounded-md text-slate-500 transition-colors hover:bg-slate-100 hover:text-slate-900" title="{{ __('frontend.tour.index.filters.reset') }}">
                                <x-lucide-rotate-ccw class="h-4 w-4" aria-hidden="true" />
                                <span class="sr-only">{{ __('frontend.tour.index.filters.reset') }}</span>
                            </button>
                        @endif
                    </div>

                    <div class="divide-y divide-slate-200">
                        <fieldset class="py-6">
                            <legend class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('frontend.tour.index.filters.type') }}</legend>
                            <div class="mt-3 flex flex-col gap-1">
                                <button type="button" wire:click="$set('type', '')" aria-pressed="{{ $type === '' ? 'true' : 'false' }}" class="flex min-h-10 items-center justify-between gap-3 rounded-md px-3 text-left text-sm font-semibold transition-colors {{ $type === '' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                                    {{ __('frontend.tour.index.filters.all_types') }}
                                    @if($type === '')<x-lucide-check class="h-4 w-4 shrink-0" aria-hidden="true" />@endif
                                </button>
                                @foreach($tourTypes as $tourType)
                                    <button type="button" wire:key="desktop-type-{{ $tourType->value }}" wire:click="$set('type', '{{ $tourType->value }}')" aria-pressed="{{ $type === $tourType->value ? 'true' : 'false' }}" class="flex min-h-10 items-center justify-between gap-3 rounded-md px-3 text-left text-sm font-semibold transition-colors {{ $type === $tourType->value ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                                        {{ __('frontend.tour.index.filters.types.'.$tourType->value) }}
                                        @if($type === $tourType->value)<x-lucide-check class="h-4 w-4 shrink-0" aria-hidden="true" />@endif
                                    </button>
                                @endforeach
                            </div>
                        </fieldset>

                        <fieldset class="py-6">
                            <legend class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('frontend.tour.index.filters.destination') }}</legend>
                            <div class="mt-3 flex flex-col gap-1">
                                <button type="button" wire:click="$set('destinationSlug', '')" aria-pressed="{{ $destinationSlug === '' ? 'true' : 'false' }}" class="flex min-h-10 items-center justify-between gap-3 rounded-md px-3 text-left text-sm font-semibold transition-colors {{ $destinationSlug === '' ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                                    {{ __('frontend.tour.index.filters.all_destinations') }}
                                    @if($destinationSlug === '')<x-lucide-check class="h-4 w-4 shrink-0" aria-hidden="true" />@endif
                                </button>
                                @foreach($destinations as $item)
                                    <button type="button" wire:key="desktop-destination-{{ $item->id }}" wire:click="$set('destinationSlug', '{{ $item->slug }}')" aria-pressed="{{ $item->slug === $destinationSlug ? 'true' : 'false' }}" class="flex min-h-10 items-center justify-between gap-3 rounded-md px-3 text-left text-sm font-semibold transition-colors {{ $item->slug === $destinationSlug ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                                        <span class="min-w-0 truncate">{{ $item->name }}</span>
                                        @if($item->slug === $destinationSlug)<x-lucide-check class="h-4 w-4 shrink-0" aria-hidden="true" />@endif
                                    </button>
                                @endforeach
                            </div>
                        </fieldset>

                        <fieldset class="py-6">
                            <legend class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('frontend.tour.index.filters.category') }}</legend>
                            <div class="mt-3 flex flex-col gap-1">
                                <button type="button" wire:click="$set('category', '')" aria-pressed="{{ $category === '' ? 'true' : 'false' }}" class="flex min-h-10 items-center justify-between gap-3 rounded-md px-3 text-left text-sm font-semibold transition-colors {{ $category === '' ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                                    {{ __('frontend.tour.index.filters.all') }}
                                    @if($category === '')<x-lucide-check class="h-4 w-4 shrink-0" aria-hidden="true" />@endif
                                </button>
                                @foreach($categories as $item)
                                    @php($isCategoryActive = $category === $item->slug || $category === $item->getTranslation('slug', 'id'))
                                    <button type="button" wire:key="desktop-category-{{ $item->id }}" wire:click="$set('category', '{{ $item->slug }}')" aria-pressed="{{ $isCategoryActive ? 'true' : 'false' }}" class="flex min-h-10 items-center justify-between gap-3 rounded-md px-3 text-left text-sm font-semibold transition-colors {{ $isCategoryActive ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                                        <span class="min-w-0 truncate">{{ $item->name }}</span>
                                        <span class="shrink-0 text-xs opacity-60">{{ $item->active_tours_count }}</span>
                                    </button>
                                @endforeach
                            </div>
                        </fieldset>
                    </div>
                </aside>

                <div class="min-w-0">

            @if($this->activeFilterCount > 0)
                <div class="mt-5 flex flex-wrap items-center gap-2" aria-label="{{ __('frontend.tour.index.filters.active') }}">
                    @if(filled($destination))
                        <button type="button" wire:click="clearFilter('destination')" class="inline-flex min-h-9 max-w-full items-center gap-2 rounded-full bg-blue-50 px-3 text-xs font-bold text-blue-700">
                            <span class="truncate">&ldquo;{{ $destination }}&rdquo;</span>
                            <x-lucide-x class="h-3.5 w-3.5 shrink-0" aria-hidden="true" />
                        </button>
                    @endif
                    @if($activeDestination)
                        <button type="button" wire:click="clearFilter('destinationSlug')" class="inline-flex min-h-9 max-w-full items-center gap-2 rounded-full bg-blue-50 px-3 text-xs font-bold text-blue-700">
                            <x-lucide-map-pin class="h-3.5 w-3.5 shrink-0" aria-hidden="true" />
                            <span class="truncate">{{ $activeDestination->name }}</span>
                            <x-lucide-x class="h-3.5 w-3.5 shrink-0" aria-hidden="true" />
                        </button>
                    @endif
                    @if($activeCategory)
                        <button type="button" wire:click="clearFilter('category')" class="inline-flex min-h-9 items-center gap-2 rounded-full bg-blue-50 px-3 text-xs font-bold text-blue-700">
                            {{ $activeCategory->name }}
                            <x-lucide-x class="h-3.5 w-3.5" aria-hidden="true" />
                        </button>
                    @endif
                    @if($activeType)
                        <button type="button" wire:click="clearFilter('type')" class="inline-flex min-h-9 items-center gap-2 rounded-full bg-blue-50 px-3 text-xs font-bold text-blue-700">
                            {{ __('frontend.tour.index.filters.types.'.$activeType->value) }}
                            <x-lucide-x class="h-3.5 w-3.5" aria-hidden="true" />
                        </button>
                    @endif
                </div>
            @endif

            <div class="mt-8 flex items-end justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-blue-600">{{ __('frontend.tour.index.results.eyebrow') }}</p>
                    <h2 id="tour-results-heading" class="mt-2 text-2xl font-extrabold text-slate-900 sm:text-3xl">{{ __('frontend.tour.index.results.title') }}</h2>
                </div>
                <p class="hidden text-sm font-semibold text-slate-500 sm:block" aria-live="polite">
                    {{ trans_choice('frontend.tour.index.results.count', $tours->total(), ['count' => $tours->total()]) }}
                </p>
            </div>

            <div class="relative mt-6">
                <div wire:loading.flex wire:target="{{ $loadingTargets }}" class="absolute inset-x-0 top-0 z-10 flex flex-col gap-5 bg-white" aria-live="polite" aria-busy="true">
                    <span class="sr-only">{{ __('frontend.tour.index.loading') }}</span>
                    @foreach(range(1, 3) as $placeholder)
                        <div class="grid animate-pulse overflow-hidden rounded-lg border border-slate-200 sm:grid-cols-[15rem_minmax(0,1fr)]">
                            <div class="aspect-video bg-slate-200 sm:aspect-auto sm:min-h-64"></div>
                            <div class="space-y-4 p-5 sm:p-6">
                                <div class="h-3 w-32 rounded bg-slate-200"></div>
                                <div class="h-6 w-3/4 rounded bg-slate-200"></div>
                                <div class="h-4 w-full rounded bg-slate-100"></div>
                                <div class="h-4 w-2/3 rounded bg-slate-100"></div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div wire:loading.class="pointer-events-none opacity-30" wire:target="{{ $loadingTargets }}" class="transition-opacity">
                    @if($tours->isNotEmpty())
                        <div class="flex flex-col gap-5">
                            @foreach($tours as $tour)
                                <x-ui.tour-catalog-card wire:key="tour-{{ $tour->id }}" :$tour :$locale />
                            @endforeach
                        </div>

                        @if($tours->hasPages())
                            <div class="mt-10">
                                {{ $tours->links(data: ['scrollTo' => '#tour-results']) }}
                            </div>
                        @endif
                    @else
                        <div class="border-y border-slate-200 bg-slate-50 px-5 py-14 text-center sm:px-10">
                            <x-lucide-search class="mx-auto h-10 w-10 text-slate-400" aria-hidden="true" />
                            <h3 class="mt-4 text-lg font-bold text-slate-900">{{ __('frontend.tour.index.empty.title') }}</h3>
                            <p class="mx-auto mt-2 max-w-md text-sm leading-7 text-slate-500">{{ __('frontend.tour.index.empty.description') }}</p>
                            <div class="mt-6 flex flex-col justify-center gap-3 sm:flex-row">
                                <button type="button" wire:click="resetFilters" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-full border border-slate-300 bg-white px-5 text-sm font-bold text-slate-800 transition-colors hover:bg-slate-100">
                                    <x-lucide-rotate-ccw class="h-4 w-4" aria-hidden="true" />
                                    {{ __('frontend.tour.index.filters.reset') }}
                                </button>
                                <a href="{{ route('contact.index', ['locale' => $locale]) }}" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-full bg-slate-900 px-5 text-sm font-bold text-white transition-colors hover:bg-blue-600">
                                    {{ __('frontend.tour.custom_trip_cta') }}
                                    <x-lucide-arrow-right class="h-4 w-4" aria-hidden="true" />
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
                </div>
            </div>
        </div>
    </section>

    <div x-cloak x-show="filtersOpen" class="fixed inset-0 z-60 lg:hidden" role="presentation">
        <button type="button" x-on:click="filtersOpen = false" x-transition.opacity class="absolute inset-0 bg-slate-950/50" aria-label="{{ __('frontend.tour.index.filters.close') }}"></button>
        <section x-show="filtersOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="translate-y-0" x-transition:leave-end="translate-y-full" x-trap.noscroll="filtersOpen" role="dialog" aria-modal="true" aria-labelledby="mobile-filter-heading" class="absolute inset-x-0 bottom-0 max-h-[85dvh] overflow-y-auto rounded-t-lg bg-white shadow-2xl">
            <div class="sticky top-0 z-10 flex items-center justify-between border-b border-slate-200 bg-white px-4 py-3">
                <h2 id="mobile-filter-heading" class="text-base font-extrabold text-slate-900">{{ __('frontend.tour.index.filters.title') }}</h2>
                <button x-ref="filterClose" type="button" x-on:click="filtersOpen = false" class="grid size-11 place-items-center rounded-md text-slate-500 hover:bg-slate-100" title="{{ __('frontend.tour.index.filters.close') }}">
                    <x-lucide-x class="h-5 w-5" aria-hidden="true" />
                    <span class="sr-only">{{ __('frontend.tour.index.filters.close') }}</span>
                </button>
            </div>

            <div class="space-y-7 px-4 py-6">
                <fieldset>
                    <legend class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('frontend.tour.index.filters.category') }}</legend>
                    <div class="mt-3 grid grid-cols-1 gap-2">
                        <button type="button" wire:click="$set('category', '')" aria-pressed="{{ $category === '' ? 'true' : 'false' }}" class="flex min-h-12 items-center justify-between rounded-lg border px-4 text-left text-sm font-bold {{ $category === '' ? 'border-blue-600 bg-blue-50 text-blue-700' : 'border-slate-200 text-slate-700' }}">
                            {{ __('frontend.tour.index.filters.all') }}
                            @if($category === '')<x-lucide-check class="h-4 w-4" aria-hidden="true" />@endif
                        </button>
                        @foreach($categories as $item)
                            @php($isCategoryActive = $category === $item->slug || $category === $item->getTranslation('slug', 'id'))
                            <button type="button" wire:key="mobile-category-{{ $item->id }}" wire:click="$set('category', '{{ $item->slug }}')" aria-pressed="{{ $isCategoryActive ? 'true' : 'false' }}" class="flex min-h-12 items-center justify-between rounded-lg border px-4 text-left text-sm font-bold {{ $isCategoryActive ? 'border-blue-600 bg-blue-50 text-blue-700' : 'border-slate-200 text-slate-700' }}">
                                <span>{{ $item->name }} <span class="ml-1 text-xs font-semibold opacity-60">{{ $item->active_tours_count }}</span></span>
                                @if($isCategoryActive)<x-lucide-check class="h-4 w-4" aria-hidden="true" />@endif
                            </button>
                        @endforeach
                    </div>
                </fieldset>

                <fieldset>
                    <legend class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('frontend.tour.index.filters.destination') }}</legend>
                    <div class="mt-3 grid grid-cols-1 gap-2">
                        <button type="button" wire:click="$set('destinationSlug', '')" aria-pressed="{{ $destinationSlug === '' ? 'true' : 'false' }}" class="flex min-h-12 items-center justify-between rounded-lg border px-4 text-left text-sm font-bold {{ $destinationSlug === '' ? 'border-blue-600 bg-blue-50 text-blue-700' : 'border-slate-200 text-slate-700' }}">
                            {{ __('frontend.tour.index.filters.all_destinations') }}
                            @if($destinationSlug === '')<x-lucide-check class="h-4 w-4" aria-hidden="true" />@endif
                        </button>
                        @foreach($destinations as $item)
                            <button type="button" wire:key="mobile-destination-{{ $item->id }}" wire:click="$set('destinationSlug', '{{ $item->slug }}')" aria-pressed="{{ $item->slug === $destinationSlug ? 'true' : 'false' }}" class="flex min-h-12 items-center justify-between gap-3 rounded-lg border px-4 text-left text-sm font-bold {{ $item->slug === $destinationSlug ? 'border-blue-600 bg-blue-50 text-blue-700' : 'border-slate-200 text-slate-700' }}">
                                <span class="min-w-0 truncate">{{ $item->name }}</span>
                                @if($item->slug === $destinationSlug)<x-lucide-check class="h-4 w-4 shrink-0" aria-hidden="true" />@endif
                            </button>
                        @endforeach
                    </div>
                </fieldset>

                <fieldset>
                    <legend class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('frontend.tour.index.filters.type') }}</legend>
                    <div class="mt-3 grid grid-cols-1 gap-2">
                        <button type="button" wire:click="$set('type', '')" aria-pressed="{{ $type === '' ? 'true' : 'false' }}" class="flex min-h-12 items-center justify-between rounded-lg border px-4 text-left text-sm font-bold {{ $type === '' ? 'border-blue-600 bg-blue-50 text-blue-700' : 'border-slate-200 text-slate-700' }}">
                            {{ __('frontend.tour.index.filters.all_types') }}
                            @if($type === '')<x-lucide-check class="h-4 w-4" aria-hidden="true" />@endif
                        </button>
                        @foreach($tourTypes as $tourType)
                            <button type="button" wire:key="mobile-type-{{ $tourType->value }}" wire:click="$set('type', '{{ $tourType->value }}')" aria-pressed="{{ $type === $tourType->value ? 'true' : 'false' }}" class="flex min-h-12 items-center justify-between rounded-lg border px-4 text-left text-sm font-bold {{ $type === $tourType->value ? 'border-blue-600 bg-blue-50 text-blue-700' : 'border-slate-200 text-slate-700' }}">
                                {{ __('frontend.tour.index.filters.types.'.$tourType->value) }}
                                @if($type === $tourType->value)<x-lucide-check class="h-4 w-4" aria-hidden="true" />@endif
                            </button>
                        @endforeach
                    </div>
                </fieldset>
            </div>

            <div class="sticky bottom-0 grid grid-cols-[auto_minmax(0,1fr)] gap-3 border-t border-slate-200 bg-white p-4">
                <button type="button" wire:click="resetFilters" class="min-h-12 rounded-lg px-4 text-sm font-bold text-slate-600 hover:bg-slate-100">{{ __('frontend.tour.index.filters.reset') }}</button>
                <button type="button" x-on:click="filtersOpen = false" class="min-h-12 rounded-lg bg-slate-900 px-5 text-sm font-bold text-white">
                    {{ trans_choice('frontend.tour.index.filters.show_results', $tours->total(), ['count' => $tours->total()]) }}
                </button>
            </div>
        </section>
    </div>
</div>
