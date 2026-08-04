@props(['locale', 'menuCountries', 'menuDestinations'])

<div id="country-mega-menu"
     x-show="countryMenuOpen"
     x-cloak
     @focusin="countryMenuOpen = true"
     @mouseenter="countryMenuOpen = true"
     @mouseleave="countryMenuOpen = false"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0 -translate-y-2"
     x-transition:enter-end="opacity-100 translate-y-0"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100 translate-y-0"
     x-transition:leave-end="opacity-0 -translate-y-2"
     class="absolute top-full mx-auto left-0 right-0 max-w-5xl pt-3 z-40"
     role="menu"
     aria-label="Country menu">
    <div class="bg-white/95 backdrop-blur-md border border-slate-200/50 shadow-2xl rounded-2xl py-7 px-8">
        <div class="grid grid-cols-12 gap-8">
            <!-- Left Column: Featured Countries -->
            <div class="col-span-8 border-r border-slate-100 pr-8">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <div class="grid h-7 w-7 place-items-center rounded-lg bg-blue-50 text-blue-600">
                            <x-lucide-globe class="h-4 w-4" aria-hidden="true" />
                        </div>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ __('frontend.header.country-mega-menu.featured-label') }}</h3>
                    </div>
                    <a href="{{ route('country.index', ['locale' => $locale]) }}" class="text-xs font-bold text-blue-600 hover:text-blue-700 inline-flex items-center gap-1 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 rounded-md" role="menuitem">
                        {{ __('frontend.header.country-mega-menu.trailing-link') }}
                        <x-lucide-arrow-right class="h-3.5 w-3.5" aria-hidden="true" />
                    </a>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    @foreach($menuCountries as $country)
                        <a href="{{ route('country.show', ['locale' => $locale, 'country' => $country]) }}"
                           class="group relative overflow-hidden rounded-xl border border-slate-200/80 bg-slate-900 p-4 transition-all duration-300 hover:shadow-lg hover:border-blue-400/50" role="menuitem">
                            <img src="{{ $country->cover_url }}" alt="{{ $country->name }}" width="400" height="200" class="absolute inset-0 h-full w-full object-cover opacity-40 transition-transform duration-500 group-hover:scale-105">
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-950/50 to-slate-950/20"></div>

                            <div class="relative flex flex-col justify-between h-22">
                                <div class="flex items-center justify-between">
                                    @if($country->flag_url)
                                        <img src="{{ $country->flag_url }}" alt="" class="h-4 w-6 rounded-xs object-cover border border-white/20 shadow-xs">
                                    @else
                                        <span class="text-xs font-black tracking-wider text-white/80 uppercase">{{ $country->iso_alpha_2 }}</span>
                                    @endif

                                    @if($country->is_featured)
                                        <span class="rounded-full bg-amber-400/90 px-2 py-0.5 text-[10px] font-extrabold text-slate-950 shadow-xs">Pilihan</span>
                                    @endif
                                </div>

                                <div>
                                    <h4 class="text-base font-extrabold text-white group-hover:text-blue-300 transition-colors">{{ $country->name }}</h4>
                                    <div class="mt-1 flex flex-wrap items-center gap-2 text-[11px] font-medium text-slate-300">
                                        @if(($country->tour_packages_count ?? 0) > 0)
                                            <span>{{ $country->tour_packages_count }} Tour</span>
                                        @endif
                                        @if(($country->umrah_packages_count ?? 0) > 0)
                                            <span>• {{ $country->umrah_packages_count }} Umrah</span>
                                        @endif
                                        @if(($country->visa_services_count ?? 0) > 0)
                                            <span>• {{ $country->visa_services_count }} Visa</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Right Column: Random Tourist Destinations -->
            <div class="col-span-4 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <div class="grid h-7 w-7 place-items-center rounded-lg bg-emerald-50 text-emerald-600">
                                <x-lucide-map-pin class="h-4 w-4" aria-hidden="true" />
                            </div>
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500">Destinasi Wisata Populer</h3>
                        </div>
                    </div>

                    <div class="space-y-3">
                        @foreach($menuDestinations as $destination)
                            <a href="{{ route('destination.show', ['locale' => $locale, 'destination' => $destination]) }}"
                               class="group flex items-center gap-3 rounded-xl border border-slate-100 p-2.5 hover:bg-slate-50 hover:border-slate-200 transition-all" role="menuitem">
                                @if($destination->cover_url)
                                    <img src="{{ $destination->cover_url }}" alt="{{ $destination->name }}" class="h-12 w-14 rounded-lg object-cover shrink-0">
                                @else
                                    <div class="grid h-12 w-14 shrink-0 place-items-center rounded-lg bg-slate-100 text-slate-400">
                                        <x-lucide-compass class="h-5 w-5" aria-hidden="true" />
                                    </div>
                                @endif
                                <div class="min-w-0 flex-1">
                                    <h4 class="text-xs font-extrabold text-slate-900 group-hover:text-blue-600 transition-colors truncate">{{ $destination->name }}</h4>
                                    @if(filled($destination->location))
                                        <p class="mt-0.5 text-[11px] text-slate-500 truncate flex items-center gap-1">
                                            <x-lucide-map-pin class="h-3 w-3 shrink-0 text-slate-400" aria-hidden="true" />
                                            <span>{{ $destination->location }}</span>
                                        </p>
                                    @endif
                                </div>
                                <x-lucide-chevron-right class="h-4 w-4 text-slate-300 group-hover:translate-x-1 group-hover:text-blue-600 transition-all shrink-0" aria-hidden="true" />
                            </a>
                        @endforeach
                    </div>
                </div>

                <a href="{{ route('destination.index', ['locale' => $locale]) }}" class="mt-4 text-center rounded-xl bg-slate-50 py-2.5 text-xs font-bold text-slate-700 hover:bg-blue-50 hover:text-blue-700 transition-colors block border border-slate-200/60">
                    Jelajah Semua Objek Wisata &rarr;
                </a>
            </div>
        </div>
    </div>
</div>
