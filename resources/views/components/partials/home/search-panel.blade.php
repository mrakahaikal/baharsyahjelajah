@props(['locale'])

<section id="search-panel" class="relative z-10 -mt-20" aria-labelledby="search-heading" data-aos="fade-up">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="rounded-3xl border border-slate-200/70 bg-white p-5 shadow-2xl shadow-slate-900/10 sm:p-8">
            <div class="grid gap-4 lg:grid-cols-[minmax(0,0.9fr)_minmax(0,1.1fr)] lg:items-end">
                <div class="min-w-0">
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-600">
                        {{ $locale === 'id' ? 'Cari Tour Pilihan' : ($locale === 'ms' ? 'Cari Lawatan Pilihan' : 'Find a Tour') }}
                    </p>
                    <h2 id="search-heading" class="mt-3 max-w-xl text-2xl font-extrabold tracking-tight text-slate-900 text-balance sm:text-3xl">
                        {{ $locale === 'id' ? 'Pilih rute yang cocok dengan gaya perjalanan Anda.' : ($locale === 'ms' ? 'Pilih laluan yang sesuai dengan gaya perjalanan anda.' : 'Choose a route that fits your travel style.') }}
                    </h2>
                </div>

                <p class="max-w-2xl text-sm leading-7 text-slate-500 lg:justify-self-end">
                    {{ $locale === 'id' ? 'Masukkan destinasi, tipe perjalanan, dan jumlah peserta untuk mulai menelusuri paket yang relevan.' : ($locale === 'ms' ? 'Masukkan destinasi, jenis perjalanan, dan jumlah peserta untuk mulai menelusuri pakej yang sesuai.' : 'Enter your destination, trip type, and group size to browse relevant packages.') }}
                </p>
            </div>

            <form method="GET" action="{{ route('tour.index', ['locale' => $locale]) }}" class="mt-7 grid grid-cols-1 items-end gap-4 lg:grid-cols-[minmax(0,1.5fr)_minmax(0,1fr)_minmax(0,0.85fr)_minmax(10rem,auto)]">
                <div class="min-w-0 rounded-2xl border border-slate-200/80 bg-white p-3.5 transition-[border-color,box-shadow] focus-within:border-blue-600 focus-within:ring-1 focus-within:ring-blue-600">
                    <label for="tour-destination" class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ __('frontend.search.fields.destination') }}</label>
                    <div class="flex min-w-0 items-center gap-2">
                        <x-lucide-map-pin class="h-4 w-4 shrink-0 text-slate-400" aria-hidden="true" />
                        <input id="tour-destination" name="destination" type="search" autocomplete="off" placeholder="{{ __('frontend.search.fields.destination_placeholder') }}" class="min-w-0 w-full bg-transparent text-sm font-semibold text-slate-900 outline-none placeholder:text-slate-400">
                    </div>
                </div>

                <div class="min-w-0 rounded-2xl border border-slate-200/80 bg-white p-3.5 transition-[border-color,box-shadow] focus-within:border-blue-600 focus-within:ring-1 focus-within:ring-blue-600">
                    <label for="tour-type" class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ __('frontend.tour.tour_type') }}</label>
                    <div class="relative flex min-w-0 items-center gap-2">
                        <x-lucide-compass class="h-4 w-4 shrink-0 text-slate-400" aria-hidden="true" />
                        <select id="tour-type" name="type" autocomplete="off" class="min-w-0 w-full appearance-none bg-transparent pr-8 text-sm font-semibold text-slate-900 outline-none">
                            <option value="">{{ $locale === 'id' ? 'Semua tipe' : ($locale === 'ms' ? 'Semua jenis' : 'All types') }}</option>
                            <option value="domestic">{{ __('frontend.tour.domestic') }}</option>
                            <option value="international">{{ __('frontend.tour.outbound') }}</option>
                        </select>
                        <x-lucide-chevron-down class="pointer-events-none absolute right-0 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" aria-hidden="true" />
                    </div>
                </div>

                <div class="min-w-0 rounded-2xl border border-slate-200/80 bg-white p-3.5 transition-[border-color,box-shadow] focus-within:border-blue-600 focus-within:ring-1 focus-within:ring-blue-600">
                    <label for="tour-pax" class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ __('frontend.search.fields.capacity') }}</label>
                    <div class="flex min-w-0 items-center gap-2">
                        <x-lucide-users class="h-4 w-4 shrink-0 text-slate-400" aria-hidden="true" />
                        <input id="tour-pax" name="pax" type="number" min="1" inputmode="numeric" autocomplete="off" placeholder="{{ __('frontend.search.fields.pax_placeholder') }}" class="min-w-0 w-full bg-transparent text-sm font-semibold text-slate-900 outline-none placeholder:text-slate-400">
                    </div>
                </div>

                <button type="submit" class="inline-flex h-14 w-full items-center justify-center gap-2 whitespace-nowrap rounded-2xl bg-slate-900 px-6 text-sm font-semibold text-white transition-colors hover:bg-slate-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-900 lg:min-w-40">
                    <x-lucide-search class="h-4 w-4 shrink-0" aria-hidden="true" />
                    {{ __('frontend.search.buttons.search_tour') }}
                </button>
            </form>
        </div>
    </div>
</section>
