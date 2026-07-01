<x-layouts::app :overlap-header="true">
    @php
        $locale = app()->getLocale();
        $waNumber = app(\App\Settings\GeneralSettings::class)->whatsapp_number;
    @endphp

    <div class="bg-white">
        <!-- Hero Section -->
        <x-partials.home.hero-section :$locale :$banners />

        <!-- Search Panel Section -->
        <x-partials.home.search-panel :$locale />

        <!-- Services Section -->
        <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8" aria-labelledby="services-heading">
            <div class="text-center" data-aos="fade-up">
                <p class="text-sm font-semibold text-blue-600 uppercase tracking-wider">Layanan utama</p>
                <h2 id="services-heading" class="mt-2 text-3xl font-extrabold text-slate-900 tracking-tight sm:text-4xl">Layanan Perjalanan Terbaik Kami</h2>
                <p class="mt-4 max-w-2xl mx-auto text-sm leading-relaxed text-slate-500">Mulai dari perjalanan ibadah, wisata halal dunia, pengurusan dokumen perjalanan, hingga transportasi nyaman.</p>
            </div>
            
            <div class="mt-12 grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-4">
                @foreach([
                    ['icon' => 'map', 'title' => 'Tour & Travel', 'desc' => 'Paket wisata domestik dan internasional dengan jaminan halal tour.', 'route' => 'tour.index'],
                    ['icon' => 'moon', 'title' => 'Paket Umroh', 'desc' => 'Layanan ibadah umroh eksklusif pendampingan mutawwif berpengalaman.', 'route' => 'umroh.index'],
                    ['icon' => 'car', 'title' => 'Sewa Kendaraan', 'desc' => 'Armada lengkap dengan driver ramah untuk menunjang kenyamanan mobilitas Anda.', 'route' => 'transport.index'],
                    ['icon' => 'badge-check', 'title' => 'Layanan Visa', 'desc' => 'Bantuan pengurusan dokumen perjalanan dan visa resmi dengan proses cepat.', 'route' => 'visa.index'],
                ] as $srv)
                    <div class="group relative rounded-2xl border border-slate-200/80 bg-white p-6 shadow-xs hover:shadow-md transition-all duration-200" data-aos="fade-up" data-aos-delay="{{ $loop->index * 80 }}">
                        <div class="inline-flex rounded-xl bg-blue-50 text-blue-600 p-3 mb-5 transition-colors group-hover:bg-blue-600 group-hover:text-white">
                            @if($srv['icon'] === 'map')
                                <x-lucide-map class="h-6 w-6" />
                            @elseif($srv['icon'] === 'moon')
                                <x-lucide-moon class="h-6 w-6" />
                            @elseif($srv['icon'] === 'car')
                                <x-lucide-car class="h-6 w-6" />
                            @else
                                <x-lucide-badge-check class="h-6 w-6" />
                            @endif
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 group-hover:text-blue-600 transition-colors">{{ $srv['title'] }}</h3>
                        <p class="mt-3 text-xs leading-relaxed text-slate-500">{{ $srv['desc'] }}</p>
                        <a href="{{ route($srv['route'], ['locale' => $locale]) }}" class="absolute inset-0" aria-label="{{ $srv['title'] }}"></a>
                    </div>
                @endforeach
            </div>
        </section>

        <!-- Featured Tours Section -->
        <section class="bg-slate-50 py-16" aria-labelledby="featured-tours-heading">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex flex-wrap items-end justify-between gap-4" data-aos="fade-up">
                    <div>
                        <p class="text-sm font-semibold text-blue-600 uppercase tracking-wider">Destinasi pilihan</p>
                        <h2 id="featured-tours-heading" class="mt-2 text-3xl font-extrabold text-slate-900 tracking-tight sm:text-4xl">{{ __('frontend.featured_tour.title') }}</h2>
                    </div>
                    <a href="{{ route('tour.index', ['locale' => $locale]) }}"
                       class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-white px-5 py-2.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                        {{ __('frontend.featured_tour.view_all') }}
                        <x-lucide-arrow-right class="h-3.5 w-3.5"/>
                    </a>
                </div>

                <div class="mt-8 grid grid-cols-1 gap-8 md:grid-cols-3">
                    @forelse($featuredTours as $tour)
                        <div data-aos="fade-up" data-aos-delay="{{ $loop->index * 80 }}">
                            <x-ui.tour-card :$tour :$locale />
                        </div>
                    @empty
                        <div class="md:col-span-3 rounded-2xl bg-white p-10 text-center border border-slate-200/80">
                            <h3 class="font-bold text-slate-900">{{ __('frontend.featured_tour.empty.title') }}</h3>
                            <p class="mt-2 text-sm text-slate-500">{{ __('frontend.featured_tour.empty.subtitle') }}</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>

        <!-- Transport Section -->
        <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8" aria-labelledby="transport-heading">
            <div class="grid gap-10 lg:grid-cols-[0.9fr_1.1fr] lg:items-center">
                <div data-aos="fade-right">
                    <p class="text-sm font-semibold text-blue-600 uppercase tracking-wider">Transport fleksibel</p>
                    <h2 id="transport-heading" class="mt-2 text-3xl font-extrabold text-slate-900 tracking-tight">{{ __('frontend.car_rental.title') }}</h2>
                    <p class="mt-4 text-sm leading-relaxed text-slate-500">{{ __('frontend.car_rental.subtitle') }}</p>
                    <div class="mt-6 grid grid-cols-2 gap-3">
                        @foreach(['AC nyaman', 'Driver opsional', 'Harga harian/trip', 'Rombongan siap'] as $item)
                            <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-xs">{{ $item }}</div>
                        @endforeach
                    </div>
                    <a href="{{ route('transport.index', ['locale' => $locale]) }}"
                       class="mt-8 inline-flex items-center gap-1.5 rounded-full bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">
                        {{ __('frontend.car_rental.view_all') }}
                        <x-lucide-arrow-right class="h-4 w-4"/>
                    </a>
                </div>

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    @forelse($featuredVehicles->take(4) as $vehicle)
                        <article class="group overflow-hidden rounded-2xl bg-white border border-slate-200/80 shadow-xs hover:shadow-md transition-all duration-200"
                                 data-aos="fade-up" data-aos-delay="{{ $loop->index * 80 }}">
                            <div class="h-44 overflow-hidden bg-slate-100">
                                <img src="{{ $vehicle->thumbnail_url }}" alt="{{ $vehicle->name }}"
                                     class="h-full w-full object-cover group-hover:scale-105 transition-transform duration-500">
                            </div>
                            <div class="p-5">
                                <h3 class="font-bold text-slate-900 group-hover:text-blue-600 transition-colors">{{ $vehicle->full_name ?: $vehicle->name }}</h3>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    @foreach(array_slice($vehicle->feature_badges, 0, 3) as $badge)
                                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-600">{{ $badge }}</span>
                                    @endforeach
                                </div>
                                <p class="mt-4 text-base font-extrabold text-blue-600">{{ $vehicle->formatted_price_per_day ?? $vehicle->formatted_price_per_trip ?? 'Hubungi kami' }}</p>
                            </div>
                        </article>
                    @empty
                        <div class="sm:col-span-2 rounded-2xl bg-white p-10 text-center border border-slate-200/80">
                            <h3 class="font-bold text-slate-900">{{ __('frontend.car_rental.empty.title') }}</h3>
                            <p class="mt-2 text-sm text-slate-500">{{ __('frontend.car_rental.empty.subtitle') }}</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>

        <!-- Umrah Section -->
        <section class="bg-slate-900 py-16 text-white" aria-labelledby="umrah-heading">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid gap-10 lg:grid-cols-[0.85fr_1.15fr] lg:items-end">
                    <div data-aos="fade-right">
                        <p class="inline-flex items-center gap-1.5 rounded-full bg-blue-500/10 px-3.5 py-1.5 text-xs font-bold uppercase tracking-wider text-blue-400 ring-1 ring-blue-500/20">
                            <x-lucide-moon-star class="h-3.5 w-3.5 shrink-0"/>
                            Nuansa Ka'bah premium
                        </p>
                        <h2 id="umrah-heading" class="mt-5 text-3xl font-extrabold leading-tight text-white tracking-tight sm:text-4xl">{{ __('frontend.umrah_package.title') }}</h2>
                        <p class="mt-4 max-w-xl text-sm leading-relaxed text-slate-400">{{ __('frontend.umrah_package.subtitle') }}</p>
                        <div class="mt-8 grid grid-cols-2 gap-3">
                            @foreach([
                                ['value' => 'Visa', 'label' => 'Pengurusan dibantu'],
                                ['value' => 'Hotel', 'label' => 'Makkah & Madinah'],
                                ['value' => 'Handling', 'label' => 'Keberangkatan rapi'],
                                ['value' => 'Guide', 'label' => 'Pendamping ibadah'],
                            ] as $item)
                                <div class="rounded-2xl border border-slate-800 bg-slate-950/40 p-4">
                                    <div class="font-extrabold text-blue-400 text-lg">{{ $item['value'] }}</div>
                                    <p class="mt-1 text-xs text-slate-400">{{ $item['label'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    
                    <div class="bg-slate-950/50 border border-slate-800 rounded-2xl p-6 sm:p-8 relative overflow-hidden" data-aos="fade-left">
                        <div class="relative">
                            <h3 class="text-xl font-bold mb-2">Konsultasi Umroh Kustom</h3>
                            <p class="text-xs text-slate-400 leading-relaxed mb-6">Butuh akomodasi khusus, paket keluarga kecil, atau tanggal keberangkatan yang fleksibel? Diskusikan langsung via WhatsApp.</p>
                            
                            <div class="space-y-4">
                                <a href="https://wa.me/{{ $waNumber }}" target="_blank" rel="noopener" class="flex items-center justify-between rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-4 text-sm transition-colors shadow-lg shadow-blue-500/10">
                                    <span class="flex items-center gap-2">
                                        <x-lucide-message-circle class="h-4.5 w-4.5" />
                                        Hubungi Chat Admin
                                    </span>
                                    <x-lucide-arrow-right class="h-4 w-4" />
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Why Choose Us -->
        <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8" aria-labelledby="why-heading">
            <div class="grid gap-10 lg:grid-cols-2 lg:items-center">
                <div data-aos="fade-right">
                    <p class="text-sm font-semibold text-blue-600 uppercase tracking-wider">Kenapa kami</p>
                    <h2 id="why-heading" class="mt-2 text-3xl font-extrabold text-slate-900 tracking-tight">{{ __('frontend.why_us.title', ['brand' => 'Baharsyah Jelajah']) }}</h2>
                    <p class="mt-4 text-sm leading-relaxed text-slate-500">{{ __('frontend.why_us.subtitle') }}</p>
                    <div class="mt-8 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        @foreach([
                            ['title' => 'Kurasi itinerary', 'text' => 'Rute, fasilitas, dan durasi disusun supaya realistis.'],
                            ['title' => 'Konsultasi cepat', 'text' => 'Setiap CTA membawa calon pelanggan langsung ke percakapan.'],
                            ['title' => 'Harga terbaca', 'text' => 'Paket menampilkan durasi, fasilitas, dan harga mulai.'],
                            ['title' => 'Lintas layanan', 'text' => 'Tour, umroh, transport, visa, dan shop saling terhubung.'],
                        ] as $item)
                            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs">
                                <h3 class="font-bold text-slate-900">{{ $item['title'] }}</h3>
                                <p class="mt-2 text-xs leading-relaxed text-slate-500">{{ $item['text'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="relative overflow-hidden rounded-2xl bg-slate-900 p-8 text-white shadow-xl"
                     data-aos="fade-left">
                    <img src="https://images.unsplash.com/photo-1524492412937-b28074a5d7da?auto=format&fit=crop&w=1100&q=85"
                         alt="" class="absolute inset-0 h-full w-full object-cover opacity-25">
                    <div class="relative">
                        <p class="text-sm font-semibold uppercase tracking-wider text-blue-400">3 langkah mudah</p>
                        <div class="mt-8 space-y-6">
                            @foreach(__('frontend.how_it_works.steps') as $step)
                                <div class="flex gap-4">
                                    <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-slate-800 text-sm font-bold text-white border border-slate-700">{{ $loop->iteration }}</span>
                                    <div>
                                        <h3 class="font-bold">{{ $step['title'] }}</h3>
                                        <p class="mt-1 text-sm leading-relaxed text-slate-300">{{ $step['description'] }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Testimonials -->
        @if($testimonials->isNotEmpty())
            <section class="bg-slate-50 py-16" aria-labelledby="testimonials-heading">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex flex-wrap items-end justify-between gap-4" data-aos="fade-up">
                        <div>
                            <p class="text-sm font-semibold text-blue-600 uppercase tracking-wider">Cerita pelanggan</p>
                            <h2 id="testimonials-heading" class="mt-2 text-3xl font-extrabold text-slate-900 tracking-tight">{{ __('frontend.testimonials.title') }}</h2>
                        </div>
                        <div class="hidden gap-2 sm:flex">
                            <button type="button"
                                    class="swiper-button-prev static! m-0! grid size-11! place-items-center rounded-full border border-slate-200 bg-white hover:bg-slate-50 transition-colors after:text-sm! after:font-bold! after:text-slate-700!"></button>
                            <button type="button"
                                    class="swiper-button-next static! m-0! grid size-11! place-items-center rounded-full border border-slate-200 bg-white hover:bg-slate-50 transition-colors after:text-sm! after:font-bold! after:text-slate-700!"></button>
                        </div>
                    </div>
                    <div class="swiper js-testimonial-swiper mt-8 pb-10" data-aos="fade-up">
                        <div class="swiper-wrapper">
                            @foreach($testimonials as $testimonial)
                                <div class="swiper-slide h-auto">
                                    <figure class="h-full rounded-2xl bg-white p-6 border border-slate-200/80 shadow-xs flex flex-col justify-between">
                                        <div>
                                            <div class="text-sm tracking-wider text-amber-400">{{ $testimonial->stars }}</div>
                                            <blockquote class="mt-4 text-sm italic leading-relaxed text-slate-500">
                                                "{{ $testimonial->content }}"
                                            </blockquote>
                                        </div>
                                        <div class="mt-5">
                                            <figcaption class="font-bold text-slate-900">{{ $testimonial->reviewer_name }}</figcaption>
                                            @if($testimonial->reviewer_country)
                                                <p class="mt-1 text-xs text-slate-400">{{ $testimonial->reviewer_country }}</p>
                                            @endif
                                        </div>
                                    </figure>
                                </div>
                            @endforeach
                        </div>
                        <div class="swiper-pagination text-blue-600"></div>
                    </div>
                </div>
            </section>
        @endif

        <!-- Blog -->
        <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8" aria-labelledby="blog-heading">
            <div class="flex flex-wrap items-end justify-between gap-4" data-aos="fade-up">
                <div>
                    <p class="text-sm font-semibold text-blue-600 uppercase tracking-wider">Insight perjalanan</p>
                    <h2 id="blog-heading" class="mt-2 text-3xl font-extrabold text-slate-900 tracking-tight">{{ __('frontend.blog.title') }}</h2>
                </div>
                <a href="{{ route('blog.index', ['locale' => $locale]) }}"
                   class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-white px-5 py-2.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                    {{ __('frontend.blog.view_all') }}
                    <x-lucide-arrow-right class="h-3.5 w-3.5"/>
                </a>
            </div>
            <div class="mt-8 grid grid-cols-1 gap-6 md:grid-cols-3">
                @forelse($latestPosts as $post)
                    <article class="group overflow-hidden rounded-2xl bg-white border border-slate-200/80 shadow-xs hover:shadow-md transition-all duration-200"
                             data-aos="fade-up" data-aos-delay="{{ $loop->index * 80 }}">
                        <div class="h-48 overflow-hidden bg-slate-100">
                            <img src="{{ $post->cover_image_url }}" alt="{{ $post->title }}"
                                 class="h-full w-full object-cover group-hover:scale-105 transition-transform duration-500">
                        </div>
                        <div class="p-5">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-blue-600">{{ $post->category?->name ?? __('frontend.nav.blog') }}</p>
                            <h3 class="mt-1 text-lg font-bold leading-snug text-slate-900 group-hover:text-blue-600 transition-colors line-clamp-1">{{ $post->title }}</h3>
                            <p class="mt-3 text-xs leading-relaxed text-slate-500 line-clamp-2">{{ \Illuminate\Support\Str::limit($post->excerpt ?: strip_tags($post->content ?? ''), 110) }}</p>
                            <a href="{{ route('blog.show', ['locale' => $locale, 'post' => $post->id]) }}"
                               class="mt-5 inline-flex items-center gap-1 text-xs font-bold uppercase tracking-wider text-blue-600">
                                {{ __('frontend.blog.read_more') }}
                                <x-lucide-arrow-right class="h-3.5 w-3.5 transition group-hover:translate-x-1"/>
                            </a>
                        </div>
                    </article>
                @empty
                    <div class="md:col-span-3 rounded-2xl bg-white p-10 text-center border border-slate-200/80">
                        <h3 class="font-bold text-slate-900">{{ __('frontend.blog.empty.title') }}</h3>
                        <p class="mt-2 text-sm text-slate-500">{{ __('frontend.blog.empty.subtitle') }}</p>
                    </div>
                @endforelse
            </div>
        </section>

        <!-- FAQ -->
        @if($faqs->isNotEmpty())
            <section id="faq" class="bg-slate-50 py-16" aria-labelledby="faq-heading">
                <div class="mx-auto grid max-w-7xl gap-10 px-4 sm:px-6 lg:grid-cols-[0.8fr_1.2fr] lg:px-8">
                    <div data-aos="fade-right">
                        <p class="text-sm font-semibold text-blue-600 uppercase tracking-wider">FAQ</p>
                        <h2 id="faq-heading" class="mt-2 text-3xl font-extrabold text-slate-900 tracking-tight">{{ __('frontend.faq.title') }}</h2>
                        <p class="mt-4 text-sm leading-relaxed text-slate-500">{{ __('frontend.faq.subtitle') }}</p>
                    </div>
                    <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs divide-y divide-slate-100"
                         data-aos="fade-left">
                        @foreach($faqs as $faq)
                            <details class="group py-5 first:pt-0 last:pb-0">
                                <summary class="flex cursor-pointer list-none items-center justify-between gap-4 text-sm font-bold text-slate-900 hover:text-blue-600 transition-colors">
                                    {{ $faq->question }}
                                    <x-lucide-chevron-down class="h-4.5 w-4.5 shrink-0 text-slate-400 transition group-open:rotate-180"/>
                                </summary>
                                <p class="mt-3 text-xs leading-relaxed text-slate-500">{{ $faq->answer }}</p>
                            </details>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        <!-- CTA -->
        <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8" aria-labelledby="final-cta-heading">
            <div class="overflow-hidden rounded-2xl bg-blue-600 p-8 text-white shadow-xl sm:p-10"
                 data-aos="fade-up">
                <div class="grid gap-8 lg:grid-cols-12 lg:items-center">
                    <div class="lg:col-span-8">
                        <p class="text-sm font-semibold uppercase tracking-wider text-blue-100">Konsultasi gratis</p>
                        <h2 id="final-cta-heading" class="mt-3 text-3xl font-extrabold tracking-tight">{{ __('frontend.cta.title') }}</h2>
                        <p class="mt-3 max-w-2xl text-sm leading-relaxed text-blue-100/90">{{ __('frontend.cta.subtitle') }}</p>
                    </div>
                    <div class="flex flex-col gap-3 sm:flex-row lg:col-span-4 lg:justify-end">
                        @if($waNumber)
                            <a href="https://wa.me/{{ $waNumber }}" target="_blank" rel="noopener"
                               class="inline-flex items-center justify-center gap-1.5 rounded-full bg-white px-5 py-3 text-sm font-semibold text-blue-600 hover:bg-slate-50 transition-colors">
                                <x-lucide-message-circle class="h-4 w-4"/>
                                {{ __('frontend.cta.button_whatsapp') }}
                            </a>
                        @endif
                        <a href="{{ route('tour.index', ['locale' => $locale]) }}"
                           class="inline-flex items-center justify-center gap-1.5 rounded-full bg-slate-900 px-5 py-3 text-sm font-semibold text-white hover:bg-slate-800 transition-colors">
                            {{ __('frontend.cta.button_consult') }}
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </div>
</x-layouts::app>
