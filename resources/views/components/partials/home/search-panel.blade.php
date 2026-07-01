@props(['locale'])

<section id="search-panel" class="relative z-10 -mt-24" aria-labelledby="search-heading" data-aos="fade-up">
    <h2 id="search-heading" class="sr-only">Cari layanan perjalanan</h2>
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div x-data="{ tab: 'tour' }" class="rounded-3xl bg-white p-6 shadow-2xl shadow-slate-900/10 border border-slate-200/60 sm:p-8">
            <div class="flex gap-6 border-b border-slate-100 pb-px" role="tablist" aria-label="Jenis layanan">
                @foreach([
                    'tour' => ['label' => __('frontend.search.tabs.tour'), 'icon' => 'map'],
                    'transport' => ['label' => __('frontend.search.tabs.transport'), 'icon' => 'car'],
                    'umroh' => ['label' => __('frontend.search.tabs.umroh'), 'icon' => 'moon'],
                ] as $key => $tab)
                    <button type="button" @click="tab = '{{ $key }}'" role="tab" :aria-selected="tab === '{{ $key }}'"
                            class="inline-flex items-center gap-2 border-b-2 pb-4 text-sm font-bold transition-all duration-200 outline-none"
                            :class="tab === '{{ $key }}' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-900'">
                        @if($tab['icon'] === 'map')
                            <x-lucide-map class="h-4 w-4 shrink-0" />
                        @elseif($tab['icon'] === 'car')
                            <x-lucide-car class="h-4 w-4 shrink-0" />
                        @else
                            <x-lucide-moon class="h-4 w-4 shrink-0" />
                        @endif
                        <span>{{ $tab['label'] }}</span>
                    </button>
                @endforeach
            </div>

            <div class="mt-6">
                <div x-show="tab === 'tour'" class="grid grid-cols-1 gap-4 lg:grid-cols-12">
                    <div class="lg:col-span-4 border border-slate-200/80 rounded-2xl bg-white p-3.5 focus-within:border-blue-600 focus-within:ring-1 focus-within:ring-blue-600 transition-all">
                        <label for="tour-destination" class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">{{ __('frontend.search.fields.destination') }}</label>
                        <div class="flex items-center gap-2">
                            <x-lucide-map-pin class="h-4 w-4 text-slate-400 shrink-0" />
                            <input id="tour-destination" type="text" placeholder="{{ __('frontend.search.fields.destination_placeholder') }}" class="w-full text-sm font-semibold text-slate-900 placeholder:text-slate-400 outline-none bg-transparent">
                        </div>
                    </div>
                    <div class="lg:col-span-3 border border-slate-200/80 rounded-2xl bg-white p-3.5 focus-within:border-blue-600 focus-within:ring-1 focus-within:ring-blue-600 transition-all">
                        <label for="tour-region" class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">{{ __('frontend.search.fields.region') }}</label>
                        <div class="flex items-center gap-2 relative">
                            <x-lucide-globe class="h-4 w-4 text-slate-400 shrink-0" />
                            <select id="tour-region" class="w-full text-sm font-semibold text-slate-900 bg-transparent outline-none appearance-none pr-8">
                                <option>{{ __('frontend.search.fields.all_regions') }}</option>
                                <option>Indonesia</option>
                                <option>Malaysia</option>
                                <option>Asia</option>
                                <option>Timur Tengah</option>
                            </select>
                            <x-lucide-chevron-down class="pointer-events-none absolute right-0 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                        </div>
                    </div>
                    <div class="lg:col-span-3 border border-slate-200/80 rounded-2xl bg-white p-3.5 focus-within:border-blue-600 focus-within:ring-1 focus-within:ring-blue-600 transition-all">
                        <label for="tour-pax" class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">{{ __('frontend.search.fields.capacity') }}</label>
                        <div class="flex items-center gap-2">
                            <x-lucide-users class="h-4 w-4 text-slate-400 shrink-0" />
                            <input id="tour-pax" type="number" min="1" placeholder="{{ __('frontend.search.fields.pax_placeholder') }}" class="w-full text-sm font-semibold text-slate-900 placeholder:text-slate-400 outline-none bg-transparent">
                        </div>
                    </div>
                    <div class="flex items-end lg:col-span-2">
                        <a href="{{ route('tour.index', ['locale' => $locale]) }}" class="inline-flex w-full h-14.5 items-center justify-center gap-2 rounded-2xl bg-slate-900 text-sm font-semibold text-white transition hover:bg-slate-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-900">
                            <x-lucide-search class="h-4 w-4" />
                            {{ __('frontend.search.buttons.search_tour') }}
                        </a>
                    </div>
                </div>

                <div x-show="tab === 'transport'" x-cloak class="grid grid-cols-1 gap-4 lg:grid-cols-12">
                    <div class="lg:col-span-4 border border-slate-200/80 rounded-2xl bg-white p-3.5 focus-within:border-blue-600 focus-within:ring-1 focus-within:ring-blue-600 transition-all">
                        <label for="transport-pax" class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">{{ __('frontend.search.fields.capacity') }}</label>
                        <div class="flex items-center gap-2">
                            <x-lucide-users class="h-4 w-4 text-slate-400 shrink-0" />
                            <input id="transport-pax" type="number" min="1" placeholder="{{ __('frontend.search.fields.pax_placeholder') }}" class="w-full text-sm font-semibold text-slate-900 placeholder:text-slate-400 outline-none bg-transparent">
                        </div>
                    </div>
                    <div class="lg:col-span-4 border border-slate-200/80 rounded-2xl bg-white p-3.5 focus-within:border-blue-600 focus-within:ring-1 focus-within:ring-blue-600 transition-all">
                        <label for="vehicle-type" class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">{{ __('frontend.search.fields.vehicle_type') }}</label>
                        <div class="flex items-center gap-2 relative">
                            <x-lucide-car class="h-4 w-4 text-slate-400 shrink-0" />
                            <select id="vehicle-type" class="w-full text-sm font-semibold text-slate-900 bg-transparent outline-none appearance-none pr-8">
                                <option>{{ __('frontend.search.fields.all_vehicles') }}</option>
                                <option>City Car</option>
                                <option>MPV / Family</option>
                                <option>Premium</option>
                                <option>Van / Bus</option>
                            </select>
                            <x-lucide-chevron-down class="pointer-events-none absolute right-0 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                        </div>
                    </div>
                    <div class="lg:col-span-2 border border-slate-200/80 rounded-2xl bg-white p-3.5 focus-within:border-blue-600 focus-within:ring-1 focus-within:ring-blue-600 transition-all">
                        <label for="transport-date" class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Tanggal</label>
                        <div class="flex items-center gap-2">
                            <x-lucide-calendar class="h-4 w-4 text-slate-400 shrink-0" />
                            <input id="transport-date" type="date" class="w-full text-sm font-semibold text-slate-900 outline-none bg-transparent">
                        </div>
                    </div>
                    <div class="flex items-end lg:col-span-2">
                        <a href="{{ route('transport.index', ['locale' => $locale]) }}" class="inline-flex w-full h-14.5 items-center justify-center gap-2 rounded-2xl bg-slate-900 text-sm font-semibold text-white transition hover:bg-slate-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-900">
                            <x-lucide-search class="h-4 w-4" />
                            {{ __('frontend.search.buttons.search_transport') }}
                        </a>
                    </div>
                </div>

                <div x-show="tab === 'umroh'" x-cloak class="grid grid-cols-1 gap-4 lg:grid-cols-12">
                    <div class="lg:col-span-4 border border-slate-200/80 rounded-2xl bg-white p-3.5 focus-within:border-blue-600 focus-within:ring-1 focus-within:ring-blue-600 transition-all">
                        <label for="departure-month" class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">{{ __('frontend.search.fields.departure_month') }}</label>
                        <div class="flex items-center gap-2 relative">
                            <x-lucide-calendar-days class="h-4 w-4 text-slate-400 shrink-0" />
                            <select id="departure-month" class="w-full text-sm font-semibold text-slate-900 bg-transparent outline-none appearance-none pr-8">
                                <option>{{ __('frontend.search.fields.select_month') }}</option>
                                <option>Ramadhan 2026</option>
                                <option>Syawal 2026</option>
                                <option>Dzulhijjah 2026</option>
                            </select>
                            <x-lucide-chevron-down class="pointer-events-none absolute right-0 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                        </div>
                    </div>
                    <div class="lg:col-span-4 border border-slate-200/80 rounded-2xl bg-white p-3.5 focus-within:border-blue-600 focus-within:ring-1 focus-within:ring-blue-600 transition-all">
                        <label for="umroh-package" class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">{{ __('frontend.search.fields.package_type') }}</label>
                        <div class="flex items-center gap-2 relative">
                            <x-lucide-package class="h-4 w-4 text-slate-400 shrink-0" />
                            <select id="umroh-package" class="w-full text-sm font-semibold text-slate-900 bg-transparent outline-none appearance-none pr-8">
                                <option>{{ __('frontend.search.fields.all_packages') }}</option>
                                <option>Regular</option>
                                <option>Plus</option>
                                <option>VIP</option>
                                <option>Ramadan</option>
                            </select>
                            <x-lucide-chevron-down class="pointer-events-none absolute right-0 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                        </div>
                    </div>
                    <div class="lg:col-span-2 border border-slate-200/80 rounded-2xl bg-white p-3.5 focus-within:border-blue-600 focus-within:ring-1 focus-within:ring-blue-600 transition-all">
                        <label for="umroh-pax" class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Jamaah</label>
                        <div class="flex items-center gap-2">
                            <x-lucide-users class="h-4 w-4 text-slate-400 shrink-0" />
                            <input id="umroh-pax" type="number" min="1" placeholder="2 Jamaah" class="w-full text-sm font-semibold text-slate-900 placeholder:text-slate-400 outline-none bg-transparent">
                        </div>
                    </div>
                    <div class="flex items-end lg:col-span-2">
                        <a href="{{ route('umroh.index', ['locale' => $locale]) }}" class="inline-flex w-full h-14.5 items-center justify-center gap-2 rounded-2xl bg-slate-900 text-sm font-semibold text-white transition hover:bg-slate-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-900">
                            <x-lucide-search class="h-4 w-4" />
                            {{ __('frontend.search.buttons.search_umroh') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
