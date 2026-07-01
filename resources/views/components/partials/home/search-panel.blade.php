@props(['locale'])

<section id="search-panel" class="relative z-10 -mt-24" aria-labelledby="search-heading" data-aos="fade-up">
    <h2 id="search-heading" class="sr-only">Cari layanan perjalanan</h2>
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div
            x-data="{
                tab: 'tour',
                tabs: ['tour', 'transport', 'umroh'],
                activateTab(nextTab) {
                    this.tab = nextTab;
                    this.$nextTick(() => document.getElementById(`search-tab-${nextTab}`)?.focus());
                },
                moveTab(step) {
                    const currentIndex = this.tabs.indexOf(this.tab);
                    const nextTab = this.tabs[(currentIndex + step + this.tabs.length) % this.tabs.length];
                    this.activateTab(nextTab);
                },
            }"
            class="rounded-3xl bg-white p-5 shadow-2xl shadow-slate-900/10 border border-slate-200/60 sm:p-8">
            <div class="flex gap-4 overflow-x-auto border-b border-slate-100 pb-px sm:gap-6" role="tablist" aria-label="Jenis layanan" @keydown.arrow-right.prevent="moveTab(1)" @keydown.arrow-left.prevent="moveTab(-1)" @keydown.home.prevent="activateTab(tabs[0])" @keydown.end.prevent="activateTab(tabs[tabs.length - 1])">
                @foreach([
                    'tour' => ['label' => __('frontend.search.tabs.tour'), 'icon' => 'map'],
                    'transport' => ['label' => __('frontend.search.tabs.transport'), 'icon' => 'car'],
                    'umroh' => ['label' => __('frontend.search.tabs.umroh'), 'icon' => 'moon'],
                ] as $key => $tab)
                    <button type="button" id="search-tab-{{ $key }}" @click="tab = '{{ $key }}'" role="tab" :aria-selected="tab === '{{ $key }}'" aria-controls="search-panel-{{ $key }}" :tabindex="tab === '{{ $key }}' ? 0 : -1"
                            class="inline-flex shrink-0 items-center gap-2 border-b-2 pb-4 text-sm font-bold transition-[border-color,color] duration-200 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600"
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
                <form x-show="tab === 'tour'" id="search-panel-tour" role="tabpanel" aria-labelledby="search-tab-tour" method="GET" action="{{ route('tour.index', ['locale' => $locale]) }}" class="grid grid-cols-1 gap-4 lg:grid-cols-12">
                    <div class="lg:col-span-4 border border-slate-200/80 rounded-2xl bg-white p-3.5 focus-within:border-blue-600 focus-within:ring-1 focus-within:ring-blue-600 transition-[border-color,box-shadow]">
                        <label for="tour-destination" class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">{{ __('frontend.search.fields.destination') }}</label>
                        <div class="flex items-center gap-2">
                            <x-lucide-map-pin class="h-4 w-4 text-slate-400 shrink-0" />
                            <input id="tour-destination" name="destination" type="search" autocomplete="off" placeholder="{{ __('frontend.search.fields.destination_placeholder') }}" class="w-full text-sm font-semibold text-slate-900 placeholder:text-slate-400 outline-none bg-transparent">
                        </div>
                    </div>
                    <div class="lg:col-span-3 border border-slate-200/80 rounded-2xl bg-white p-3.5 focus-within:border-blue-600 focus-within:ring-1 focus-within:ring-blue-600 transition-[border-color,box-shadow]">
                        <label for="tour-region" class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">{{ __('frontend.search.fields.region') }}</label>
                        <div class="flex items-center gap-2 relative">
                            <x-lucide-globe class="h-4 w-4 text-slate-400 shrink-0" />
                            <select id="tour-region" name="region" autocomplete="country-name" class="w-full text-sm font-semibold text-slate-900 bg-transparent outline-none appearance-none pr-8">
                                <option value="">{{ __('frontend.search.fields.all_regions') }}</option>
                                <option value="indonesia">Indonesia</option>
                                <option value="malaysia">Malaysia</option>
                                <option value="asia">Asia</option>
                                <option value="timur-tengah">Timur Tengah</option>
                            </select>
                            <x-lucide-chevron-down class="pointer-events-none absolute right-0 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                        </div>
                    </div>
                    <div class="lg:col-span-3 border border-slate-200/80 rounded-2xl bg-white p-3.5 focus-within:border-blue-600 focus-within:ring-1 focus-within:ring-blue-600 transition-[border-color,box-shadow]">
                        <label for="tour-pax" class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">{{ __('frontend.search.fields.capacity') }}</label>
                        <div class="flex items-center gap-2">
                            <x-lucide-users class="h-4 w-4 text-slate-400 shrink-0" />
                            <input id="tour-pax" name="pax" type="number" min="1" inputmode="numeric" autocomplete="off" placeholder="{{ __('frontend.search.fields.pax_placeholder') }}" class="w-full text-sm font-semibold text-slate-900 placeholder:text-slate-400 outline-none bg-transparent">
                        </div>
                    </div>
                    <div class="flex items-end lg:col-span-2">
                        <button type="submit" class="inline-flex h-14 w-full items-center justify-center gap-2 rounded-2xl bg-slate-900 text-sm font-semibold text-white transition-colors hover:bg-slate-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-900">
                            <x-lucide-search class="h-4 w-4" />
                            {{ __('frontend.search.buttons.search_tour') }}
                        </button>
                    </div>
                </form>

                <form x-show="tab === 'transport'" x-cloak id="search-panel-transport" role="tabpanel" aria-labelledby="search-tab-transport" method="GET" action="{{ route('transport.index', ['locale' => $locale]) }}" class="grid grid-cols-1 gap-4 lg:grid-cols-12">
                    <div class="lg:col-span-4 border border-slate-200/80 rounded-2xl bg-white p-3.5 focus-within:border-blue-600 focus-within:ring-1 focus-within:ring-blue-600 transition-[border-color,box-shadow]">
                        <label for="transport-pax" class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">{{ __('frontend.search.fields.capacity') }}</label>
                        <div class="flex items-center gap-2">
                            <x-lucide-users class="h-4 w-4 text-slate-400 shrink-0" />
                            <input id="transport-pax" name="pax" type="number" min="1" inputmode="numeric" autocomplete="off" placeholder="{{ __('frontend.search.fields.pax_placeholder') }}" class="w-full text-sm font-semibold text-slate-900 placeholder:text-slate-400 outline-none bg-transparent">
                        </div>
                    </div>
                    <div class="lg:col-span-4 border border-slate-200/80 rounded-2xl bg-white p-3.5 focus-within:border-blue-600 focus-within:ring-1 focus-within:ring-blue-600 transition-[border-color,box-shadow]">
                        <label for="vehicle-type" class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">{{ __('frontend.search.fields.vehicle_type') }}</label>
                        <div class="flex items-center gap-2 relative">
                            <x-lucide-car class="h-4 w-4 text-slate-400 shrink-0" />
                            <select id="vehicle-type" name="vehicle_type" autocomplete="off" class="w-full text-sm font-semibold text-slate-900 bg-transparent outline-none appearance-none pr-8">
                                <option value="">{{ __('frontend.search.fields.all_vehicles') }}</option>
                                <option value="city-car">City Car</option>
                                <option value="mpv-family">MPV / Family</option>
                                <option value="premium">Premium</option>
                                <option value="van-bus">Van / Bus</option>
                            </select>
                            <x-lucide-chevron-down class="pointer-events-none absolute right-0 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                        </div>
                    </div>
                    <div class="lg:col-span-2 border border-slate-200/80 rounded-2xl bg-white p-3.5 focus-within:border-blue-600 focus-within:ring-1 focus-within:ring-blue-600 transition-[border-color,box-shadow]">
                        <label for="transport-date" class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Tanggal</label>
                        <div class="flex items-center gap-2">
                            <x-lucide-calendar class="h-4 w-4 text-slate-400 shrink-0" />
                            <input id="transport-date" name="date" type="date" autocomplete="off" class="w-full text-sm font-semibold text-slate-900 outline-none bg-transparent">
                        </div>
                    </div>
                    <div class="flex items-end lg:col-span-2">
                        <button type="submit" class="inline-flex h-14 w-full items-center justify-center gap-2 rounded-2xl bg-slate-900 text-sm font-semibold text-white transition-colors hover:bg-slate-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-900">
                            <x-lucide-search class="h-4 w-4" />
                            {{ __('frontend.search.buttons.search_transport') }}
                        </button>
                    </div>
                </form>

                <form x-show="tab === 'umroh'" x-cloak id="search-panel-umroh" role="tabpanel" aria-labelledby="search-tab-umroh" method="GET" action="{{ route('umroh.index', ['locale' => $locale]) }}" class="grid grid-cols-1 gap-4 lg:grid-cols-12">
                    <div class="lg:col-span-4 border border-slate-200/80 rounded-2xl bg-white p-3.5 focus-within:border-blue-600 focus-within:ring-1 focus-within:ring-blue-600 transition-[border-color,box-shadow]">
                        <label for="departure-month" class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">{{ __('frontend.search.fields.departure_month') }}</label>
                        <div class="flex items-center gap-2 relative">
                            <x-lucide-calendar-days class="h-4 w-4 text-slate-400 shrink-0" />
                            <select id="departure-month" name="departure_month" autocomplete="off" class="w-full text-sm font-semibold text-slate-900 bg-transparent outline-none appearance-none pr-8">
                                <option value="">{{ __('frontend.search.fields.select_month') }}</option>
                                <option value="ramadhan-2026">Ramadhan 2026</option>
                                <option value="syawal-2026">Syawal 2026</option>
                                <option value="dzulhijjah-2026">Dzulhijjah 2026</option>
                            </select>
                            <x-lucide-chevron-down class="pointer-events-none absolute right-0 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                        </div>
                    </div>
                    <div class="lg:col-span-4 border border-slate-200/80 rounded-2xl bg-white p-3.5 focus-within:border-blue-600 focus-within:ring-1 focus-within:ring-blue-600 transition-[border-color,box-shadow]">
                        <label for="umroh-package" class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">{{ __('frontend.search.fields.package_type') }}</label>
                        <div class="flex items-center gap-2 relative">
                            <x-lucide-package class="h-4 w-4 text-slate-400 shrink-0" />
                            <select id="umroh-package" name="package_type" autocomplete="off" class="w-full text-sm font-semibold text-slate-900 bg-transparent outline-none appearance-none pr-8">
                                <option value="">{{ __('frontend.search.fields.all_packages') }}</option>
                                <option value="regular">Regular</option>
                                <option value="plus">Plus</option>
                                <option value="vip">VIP</option>
                                <option value="ramadan">Ramadan</option>
                            </select>
                            <x-lucide-chevron-down class="pointer-events-none absolute right-0 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                        </div>
                    </div>
                    <div class="lg:col-span-2 border border-slate-200/80 rounded-2xl bg-white p-3.5 focus-within:border-blue-600 focus-within:ring-1 focus-within:ring-blue-600 transition-[border-color,box-shadow]">
                        <label for="umroh-pax" class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Jamaah</label>
                        <div class="flex items-center gap-2">
                            <x-lucide-users class="h-4 w-4 text-slate-400 shrink-0" />
                            <input id="umroh-pax" name="pax" type="number" min="1" inputmode="numeric" autocomplete="off" placeholder="2 Jamaah" class="w-full text-sm font-semibold text-slate-900 placeholder:text-slate-400 outline-none bg-transparent">
                        </div>
                    </div>
                    <div class="flex items-end lg:col-span-2">
                        <button type="submit" class="inline-flex h-14 w-full items-center justify-center gap-2 rounded-2xl bg-slate-900 text-sm font-semibold text-white transition-colors hover:bg-slate-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-900">
                            <x-lucide-search class="h-4 w-4" />
                            {{ __('frontend.search.buttons.search_umroh') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
