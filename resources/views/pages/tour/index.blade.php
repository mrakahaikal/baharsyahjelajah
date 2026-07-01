@php
    $locale = app()->getLocale();
    $activeCategory = request('category');
    $seoTitle = $locale === 'en'
        ? 'Halal Tour Packages | '.config('app.name')
        : ($locale === 'ms' ? 'Pakej Lawatan Halal | '.config('app.name') : 'Paket Tour Wisata Halal | '.config('app.name'));
    $seoDescription = $locale === 'en'
        ? 'Explore curated halal tour packages with clear itineraries, local guidance, and flexible consultation with Baharsyah Jelajah.'
        : ($locale === 'ms' ? 'Temui pakej lawatan halal dengan itinerari jelas, panduan tempatan, dan konsultasi fleksibel bersama Baharsyah Jelajah.' : 'Temukan paket tour wisata halal dengan itinerary jelas, pendampingan lokal, dan konsultasi fleksibel bersama Baharsyah Jelajah.');
@endphp

<x-layouts::app :title="$seoTitle" :meta-description="$seoDescription">
    <section class="border-b border-slate-100 bg-slate-50">
        <div class="mx-auto max-w-7xl px-4 pb-14 sm:px-6 lg:px-8">
            <nav class="mb-5 flex text-sm text-slate-500" aria-label="Breadcrumb">
                <ol class="flex items-center gap-2">
                    <li>
                        <a href="{{ route('home', ['locale' => $locale]) }}" class="rounded-md transition-colors hover:text-slate-900 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                            {{ $locale === 'id' ? 'Beranda' : ($locale === 'ms' ? 'Utama' : 'Home') }}
                        </a>
                    </li>
                    <li aria-hidden="true">/</li>
                    <li class="font-medium text-slate-900" aria-current="page">
                        {{ $locale === 'id' ? 'Paket Tour' : ($locale === 'ms' ? 'Pakej Lawatan' : 'Tour Packages') }}
                    </li>
                </ol>
            </nav>

            <div class="grid gap-10 lg:grid-cols-[1fr_22rem] lg:items-end">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-wider text-blue-600">
                        {{ $locale === 'id' ? 'Katalog Perjalanan' : ($locale === 'ms' ? 'Katalog Perjalanan' : 'Travel Catalog') }}
                    </p>
                    <h1 class="mt-3 max-w-4xl text-3xl font-extrabold tracking-tight text-slate-900 text-balance sm:text-4xl lg:text-5xl">
                        {{ $locale === 'id' ? 'Paket tour yang informatif, fleksibel, dan mudah dikonsultasikan.' : ($locale === 'ms' ? 'Pakej lawatan yang informatif, fleksibel, dan mudah dikonsultasikan.' : 'Informative, flexible tours that are easy to consult.') }}
                    </h1>
                    <p class="mt-5 max-w-3xl text-sm leading-7 text-slate-500 sm:text-base">
                        {{ $seoDescription }}
                    </p>
                </div>

                <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-xs">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">
                        {{ $locale === 'id' ? 'Paket aktif' : ($locale === 'ms' ? 'Pakej aktif' : 'Active packages') }}
                    </p>
                    <p class="mt-2 text-4xl font-extrabold tracking-tight text-slate-900">{{ $activeToursCount }}</p>
                    <p class="mt-2 text-sm leading-6 text-slate-500">
                        {{ $locale === 'id' ? 'Setiap paket bisa disesuaikan melalui konsultasi tim.' : ($locale === 'ms' ? 'Setiap pakej boleh disesuaikan melalui konsultasi pasukan.' : 'Every package can be tailored through team consultation.') }}
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section class="bg-white py-12" aria-labelledby="tour-catalog-heading">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-8 lg:grid-cols-12 lg:items-start">
                <aside class="lg:col-span-3">
                    <div class="sticky top-28 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs">
                        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400">
                            {{ $locale === 'id' ? 'Filter Kategori' : ($locale === 'ms' ? 'Tapis Kategori' : 'Category Filter') }}
                        </h2>

                        <div class="mt-4 flex gap-2 overflow-x-auto pb-1 lg:flex-col lg:overflow-visible lg:pb-0">
                            <a href="{{ route('tour.index', ['locale' => $locale]) }}"
                               class="flex min-w-max items-center justify-between gap-4 rounded-xl px-3.5 py-2.5 text-xs font-bold transition-colors {{ !$activeCategory ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                <span>{{ $locale === 'id' ? 'Semua Paket' : ($locale === 'ms' ? 'Semua Pakej' : 'All Packages') }}</span>
                                <span class="rounded-full px-2 py-0.5 text-[10px] {{ !$activeCategory ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-500' }}">
                                    {{ $activeToursCount }}
                                </span>
                            </a>

                            @foreach($categories as $cat)
                                @php $isCurrent = $activeCategory === $cat->slug || $activeCategory == $cat->id; @endphp
                                <a href="{{ route('tour.index', ['locale' => $locale, 'category' => $cat->slug]) }}"
                                   class="flex min-w-max items-center justify-between gap-4 rounded-xl px-3.5 py-2.5 text-xs font-bold transition-colors {{ $isCurrent ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                    <span>{{ $cat->name }}</span>
                                    <span class="rounded-full px-2 py-0.5 text-[10px] {{ $isCurrent ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-500' }}">
                                        {{ $cat->active_tours_count }}
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </aside>

                <main class="lg:col-span-9">
                    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-wider text-blue-600">
                                {{ $locale === 'id' ? 'Pilihan Tour' : ($locale === 'ms' ? 'Pilihan Lawatan' : 'Tour Options') }}
                            </p>
                            <h2 id="tour-catalog-heading" class="mt-2 text-2xl font-extrabold tracking-tight text-slate-900">
                                {{ $locale === 'id' ? 'Temukan rute yang paling cocok' : ($locale === 'ms' ? 'Temui laluan yang paling sesuai' : 'Find the route that fits best') }}
                            </h2>
                        </div>
                        <a href="{{ route('contact.index', ['locale' => $locale]) }}" class="inline-flex w-fit items-center justify-center gap-2 rounded-full bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-slate-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-900">
                            {{ __('frontend.tour.custom_trip_cta') }}
                            <x-lucide-message-circle class="h-4 w-4" aria-hidden="true" />
                        </a>
                    </div>

                    @if($tours->isNotEmpty())
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
                            @foreach($tours as $tour)
                                <x-ui.tour-card :$tour :$locale />
                            @endforeach
                        </div>

                        <div class="mt-12">
                            {{ $tours->links() }}
                        </div>
                    @else
                        <div class="rounded-2xl border border-slate-200/80 bg-slate-50 p-10 text-center">
                            <x-lucide-map class="mx-auto h-10 w-10 text-slate-400" aria-hidden="true" />
                            <h3 class="mt-4 font-bold text-slate-900">
                                {{ $locale === 'id' ? 'Belum ada paket di kategori ini' : ($locale === 'ms' ? 'Belum ada pakej dalam kategori ini' : 'No packages in this category yet') }}
                            </h3>
                            <p class="mx-auto mt-2 max-w-sm text-sm leading-7 text-slate-500">
                                {{ $locale === 'id' ? 'Tim kami tetap bisa membantu menyusun perjalanan custom sesuai kebutuhan Anda.' : ($locale === 'ms' ? 'Pasukan kami tetap boleh membantu menyusun perjalanan khas mengikut keperluan anda.' : 'Our team can still help shape a custom trip around your needs.') }}
                            </p>
                            <a href="{{ route('contact.index', ['locale' => $locale]) }}" class="mt-6 inline-flex items-center justify-center gap-2 rounded-full bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-slate-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-900">
                                {{ __('frontend.tour.custom_trip_cta') }}
                                <x-lucide-arrow-right class="h-4 w-4" aria-hidden="true" />
                            </a>
                        </div>
                    @endif
                </main>
            </div>
        </div>
    </section>
</x-layouts::app>
