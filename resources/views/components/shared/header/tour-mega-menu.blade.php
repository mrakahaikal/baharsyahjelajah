@props(['locale','menuCategories', 'menuFeaturedTours'])

<div id="tour-mega-menu" x-show="tourMenuOpen"
     x-cloak
     @focusin="tourMenuOpen = true"
     @mouseenter="tourMenuOpen = true"
     @mouseleave="tourMenuOpen = false"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0 -translate-y-2"
     x-transition:enter-end="opacity-100 translate-y-0"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100 translate-y-0"
     x-transition:leave-end="opacity-0 -translate-y-2"
     class="absolute top-full mx-auto left-0 right-0 max-w-4xl pt-3 z-40" role="menu" aria-label="Tour menu">
    <div class="bg-white/95 backdrop-blur-md border border-slate-200/50 shadow-2xl rounded-2xl py-8 px-8">
        <div class="grid grid-cols-12 gap-8">
            <div class="col-span-4 border-r border-slate-100 pr-8">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-4">{{ __('frontend.header.tour-mega-menu.category-label') }}</h3>
                <div class="space-y-1">
                    @foreach($menuCategories as $cat)
                        <a href="{{ route('tour.index', ['locale' => $locale, 'category' => $cat->slug]) }}"
                           class="group flex items-center justify-between rounded-2xl p-3 hover:bg-slate-50 transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600" role="menuitem">
                            <div>
                                <h4 class="text-sm font-bold text-slate-900 group-hover:text-blue-600 transition-colors">{{ $cat->name }}</h4>
                                <p class="mt-0.5 text-xs text-slate-500">{{ $cat->active_tours_count }} {{ $locale === 'en' ? 'active tours' : ($locale === 'ms' ? 'lawatan aktif' : 'tour aktif') }}</p>
                            </div>
                            <x-lucide-chevron-right class="h-4 w-4 text-slate-400 transition-transform duration-200 group-hover:translate-x-1" aria-hidden="true" />
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="col-span-8">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('frontend.header.tour-mega-menu.recommendation-label') }}</h3>
                    <a href="{{ route('tour.index', ['locale' => $locale]) }}" class="text-xs font-bold text-blue-600 hover:text-blue-700 inline-flex items-center gap-1 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 rounded-md" role="menuitem">
                        {{ __('frontend.header.tour-mega-menu.trailing-link') }}
                        <x-lucide-arrow-right class="h-3.5 w-3.5" aria-hidden="true" />
                    </a>
                </div>
                <div class="grid grid-cols-2 gap-6">
                    @foreach($menuFeaturedTours as $menuTour)
                        <x-ui.tour-image-card :tour="$menuTour" :locale="$locale" />
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
