@php
    $locale = app()->getLocale();
@endphp

<x-layouts::app
    seo-page="country_show"
    :title="__('country.seo.show_title', ['name' => $country->name])"
    :meta-description="$description"
    :$schemaJson
    breadcrumb-name="country.show"
    :breadcrumb-parameters="[$locale, $country]"
    :$canonicalUrl
    :$alternateUrls>
    <section class="relative isolate overflow-hidden bg-slate-950 py-20 text-white sm:py-24" aria-labelledby="country-show-heading">
        <img src="{{ $country->cover_url }}" alt="{{ $country->name }}" width="1800" height="900" class="absolute inset-0 h-full w-full object-cover opacity-35">
        <div class="absolute inset-0 bg-linear-to-r from-slate-950 via-slate-950/90 to-slate-950/45"></div>

        <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <x-ui.breadcrumbs name="country.show" :parameters="[$locale, $country]" variant="dark" class="mb-10" />

            <div class="flex flex-wrap items-center gap-3">
                @if($country->flag_url)
                    <span class="inline-flex items-center gap-2.5 rounded-full border border-white/20 bg-white/10 px-4 py-1.5 text-xs font-bold text-white backdrop-blur-md">
                        <img src="{{ $country->flag_url }}" alt="" class="h-4 w-6 rounded-xs object-cover">
                        <span>{{ $country->iso_alpha_2 }} / {{ $country->iso_alpha_3 ?? '' }}</span>
                    </span>
                @else
                    <span class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-4 py-1.5 text-xs font-bold text-white backdrop-blur-md">
                        <x-lucide-globe class="h-4 w-4" aria-hidden="true" />
                        <span>{{ $country->iso_alpha_2 }}</span>
                    </span>
                @endif

                @if($country->is_featured)
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-400 px-3.5 py-1 text-xs font-extrabold text-slate-950 shadow-xs">
                        <x-lucide-sparkles class="h-3.5 w-3.5" aria-hidden="true" />
                        Destinasi Unggulan
                    </span>
                @endif
            </div>

            <h1 id="country-show-heading" class="mt-4 text-balance text-4xl font-black leading-tight sm:text-5xl lg:text-6xl">
                {{ $country->name }}
            </h1>

            @if(filled($country->description))
                <p class="mt-5 max-w-3xl text-pretty text-base leading-8 text-slate-200 sm:text-lg">
                    {{ str(strip_tags((string) $country->description)) }}
                </p>
            @endif

            @if(filled($country->capital_city) || filled($country->currency_code) || filled($country->language) || filled($country->best_time_to_visit) || filled($country->travel_requirements_summary))
                <div class="mt-10 grid gap-4 rounded-xl border border-white/15 bg-white/10 p-6 backdrop-blur-md sm:grid-cols-2 lg:grid-cols-4">
                    @if(filled($country->capital_city))
                        <div class="flex items-start gap-3">
                            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-blue-500/20 text-blue-300">
                                <x-lucide-building-2 class="h-5 w-5" aria-hidden="true" />
                            </span>
                            <div>
                                <p class="text-xs font-medium text-slate-300">Ibu Kota</p>
                                <p class="mt-0.5 text-sm font-bold text-white">{{ $country->capital_city }}</p>
                            </div>
                        </div>
                    @endif
                    @if(filled($country->currency_code))
                        <div class="flex items-start gap-3">
                            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-emerald-500/20 text-emerald-300">
                                <x-lucide-coins class="h-5 w-5" aria-hidden="true" />
                            </span>
                            <div>
                                <p class="text-xs font-medium text-slate-300">Mata Uang</p>
                                <p class="mt-0.5 text-sm font-bold text-white">{{ $country->currency_code }}</p>
                            </div>
                        </div>
                    @endif
                    @if(filled($country->language))
                        <div class="flex items-start gap-3">
                            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-purple-500/20 text-purple-300">
                                <x-lucide-languages class="h-5 w-5" aria-hidden="true" />
                            </span>
                            <div>
                                <p class="text-xs font-medium text-slate-300">Bahasa Resmi</p>
                                <p class="mt-0.5 text-sm font-bold text-white">{{ $country->language }}</p>
                            </div>
                        </div>
                    @endif
                    @if(filled($country->best_time_to_visit))
                        <div class="flex items-start gap-3">
                            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-amber-500/20 text-amber-300">
                                <x-lucide-sun-medium class="h-5 w-5" aria-hidden="true" />
                            </span>
                            <div>
                                <p class="text-xs font-medium text-slate-300">Waktu Terbaik</p>
                                <p class="mt-0.5 text-sm font-bold text-white">{{ $country->best_time_to_visit }}</p>
                            </div>
                        </div>
                    @endif
                    @if(filled($country->travel_requirements_summary))
                        <div class="sm:col-span-2 lg:col-span-4 border-t border-white/10 pt-4 flex items-start gap-3">
                            <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-sky-500/20 text-sky-300">
                                <x-lucide-file-text class="h-4 w-4" aria-hidden="true" />
                            </span>
                            <div class="min-w-0">
                                <p class="text-xs font-medium text-slate-300">Persyaratan Perjalanan & Visa</p>
                                <p class="mt-0.5 text-sm leading-6 text-slate-200">{{ $country->travel_requirements_summary }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </section>

    <!-- Tours Section -->
    @if($tours->isNotEmpty())
        <section class="bg-slate-50 py-16 sm:py-20" aria-labelledby="tours-heading">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="max-w-2xl">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-600">Paket Perjalanan</p>
                    <h2 id="tours-heading" class="mt-2 text-3xl font-extrabold text-slate-950 sm:text-4xl">
                        {{ __('country.show.tabs.tours', ['count' => $tours->total()]) }}
                    </h2>
                </div>

                <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($tours as $tour)
                        <x-ui.tour-card :$tour :$locale />
                    @endforeach
                </div>
                <div class="mt-10">{{ $tours->links() }}</div>
            </div>
        </section>
    @endif

    <!-- Umrah Packages Section -->
    @if($umrahPackages->isNotEmpty())
        <section class="bg-neutral-950 py-16 text-white sm:py-20" aria-labelledby="umrah-heading">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="max-w-2xl">
                    <p class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-[0.18em] text-amber-300">
                        <x-lucide-moon-star class="h-4 w-4" aria-hidden="true" />
                        Ibadah Umrah
                    </p>
                    <h2 id="umrah-heading" class="mt-2 text-3xl font-extrabold text-white sm:text-4xl">
                        {{ __('country.show.tabs.umrah', ['count' => $umrahPackages->total()]) }}
                    </h2>
                </div>

                <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($umrahPackages as $package)
                        <x-ui.umrah-package-card :$package :$locale :$whatsappNumber dark />
                    @endforeach
                </div>
                <div class="mt-10">{{ $umrahPackages->links() }}</div>
            </div>
        </section>
    @endif

    <!-- Visa Services Section -->
    @if($visaServices->isNotEmpty())
        <section class="bg-white py-16 sm:py-20" aria-labelledby="visa-heading">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="max-w-2xl">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-emerald-600">Dokumen Perjalanan</p>
                    <h2 id="visa-heading" class="mt-2 text-3xl font-extrabold text-slate-950 sm:text-4xl">
                        {{ __('country.show.tabs.visa', ['count' => $visaServices->total()]) }}
                    </h2>
                </div>

                <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($visaServices as $service)
                        <x-ui.visa-service-card :$service :$locale />
                    @endforeach
                </div>
                <div class="mt-10">{{ $visaServices->links() }}</div>
            </div>
        </section>
    @endif

    <!-- Destinations Section -->
    @if($destinations->isNotEmpty())
        <section class="bg-slate-50 py-16 sm:py-20" aria-labelledby="destinations-heading">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="max-w-2xl">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-600">Tempat Menarik</p>
                    <h2 id="destinations-heading" class="mt-2 text-3xl font-extrabold text-slate-950 sm:text-4xl">
                        {{ __('country.show.tabs.destinations', ['count' => $destinations->total()]) }}
                    </h2>
                </div>

                <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($destinations as $destination)
                        <x-ui.destination-card :$destination :$locale />
                    @endforeach
                </div>
                <div class="mt-10">{{ $destinations->links() }}</div>
            </div>
        </section>
    @endif

    <!-- Vehicle Section -->
    @if($vehicles->isNotEmpty())
        <section class="bg-white py-16 sm:py-20" aria-labelledby="vehicles-heading">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="max-w-2xl">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-600">Armada Transportasi</p>
                    <h2 id="vehicles-heading" class="mt-2 text-3xl font-extrabold text-slate-950 sm:text-4xl">
                        {{ __('country.show.tabs.vehicles', ['count' => $vehicles->total()]) }}
                    </h2>
                </div>

                <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($vehicles as $vehicle)
                        <x-ui.vehicle-card :$vehicle :$locale />
                    @endforeach
                </div>
                <div class="mt-10">{{ $vehicles->links() }}</div>
            </div>
        </section>
    @endif

    <!-- Articles Section -->
    @if($posts->isNotEmpty())
        <section class="bg-slate-50 py-16 sm:py-20" aria-labelledby="posts-heading">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="max-w-2xl">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-600">Panduan & Informasi</p>
                    <h2 id="posts-heading" class="mt-2 text-3xl font-extrabold text-slate-950 sm:text-4xl">
                        {{ __('country.show.tabs.articles', ['count' => $posts->total()]) }}
                    </h2>
                </div>

                <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($posts as $post)
                        <x-ui.post-card :$post :$locale />
                    @endforeach
                </div>
                <div class="mt-10">{{ $posts->links() }}</div>
            </div>
        </section>
    @endif
</x-layouts::app>
