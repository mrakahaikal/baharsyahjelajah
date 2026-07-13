<x-layouts::app>
    @php
        $locale = app()->getLocale();
        $waNumber = app(\App\Settings\GeneralSettings::class)->whatsapp_number;
    @endphp

    <div>
        <!-- Hero Section -->
        <x-partials.home.hero-section :$locale :$banners />

        <!-- Search Panel Section -->
        <x-partials.home.search-panel :$locale />

        <!-- Services Section -->
        <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8" aria-labelledby="services-heading">
            <div class="text-center" data-aos="fade-up">
                <p class="text-sm font-semibold text-blue-600 uppercase tracking-wider">Rancang perjalanan</p>
                <h2 id="services-heading" class="mt-2 text-3xl font-extrabold text-slate-900 tracking-tight sm:text-4xl">Pilih tour dengan detail yang mudah dipahami.</h2>
                <p class="mt-4 max-w-2xl mx-auto text-sm leading-relaxed text-slate-500">Jelajahi itinerary, fasilitas, estimasi biaya, dan panduan destinasi sebelum berdiskusi dengan tim Baharsyah Jelajah.</p>
            </div>

            <div class="mt-12 grid grid-cols-1 gap-8 md:grid-cols-3">
                @foreach([
                    ['icon' => 'map', 'title' => 'Jelajahi Tour', 'desc' => 'Pilih tour tujuan, lalu bandingkan durasi, fasilitas, dan harga dari paket yang tersedia.', 'route' => 'tour.index'],
                    ['icon' => 'file', 'title' => 'Baca Panduan Destinasi', 'desc' => 'Temukan inspirasi rute, tips persiapan, dan catatan perjalanan sebelum menentukan pilihan.', 'route' => 'blog.index'],
                    ['icon' => 'message', 'title' => 'Diskusikan Itinerary', 'desc' => 'Ceritakan tanggal, jumlah peserta, dan gaya perjalanan agar tim dapat membantu menyesuaikan rute.', 'route' => 'contact.index'],
                ] as $srv)
                    <div class="group relative min-h-48 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-xs transition-[box-shadow,transform,border-color] duration-200 hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-md" data-aos="fade-up" data-aos-delay="{{ $loop->index * 80 }}">
                        <div class="inline-flex rounded-xl bg-blue-50 text-blue-600 p-3 mb-5 transition-colors group-hover:bg-blue-600 group-hover:text-white">
                            @if($srv['icon'] === 'map')
                                <x-lucide-map class="h-6 w-6" />
                            @elseif($srv['icon'] === 'file')
                                <x-lucide-file-text class="h-6 w-6" />
                            @else
                                <x-lucide-message-circle class="h-6 w-6" />
                            @endif
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 group-hover:text-blue-600 transition-colors">{{ $srv['title'] }}</h3>
                        <p class="mt-3 text-xs leading-relaxed text-slate-500">{{ $srv['desc'] }}</p>
                        <a href="{{ route($srv['route'], ['locale' => $locale]) }}" class="absolute inset-0 rounded-2xl focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600" aria-label="{{ $srv['title'] }}"></a>
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
                       class="inline-flex w-full items-center justify-center gap-1.5 rounded-full border border-slate-200 bg-white px-5 py-2.5 text-xs font-semibold text-slate-700 transition-colors hover:bg-slate-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 sm:w-fit">
                        {{ __('frontend.featured_tour.view_all') }}
                        <x-lucide-arrow-right class="h-3.5 w-3.5" aria-hidden="true"/>
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
                            ['title' => 'Respons personal', 'text' => 'Tim membantu membaca kebutuhan perjalanan sebelum memberi saran rute.'],
                            ['title' => 'Estimasi jelas', 'text' => 'Paket menampilkan durasi, fasilitas, dan harga awal yang mudah dipahami.'],
                            ['title' => 'Panduan berguna', 'text' => 'Artikel membantu Anda mengenali destinasi sebelum mulai berdiskusi.'],
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
                         alt="" width="1100" height="825" loading="lazy" class="absolute inset-0 h-full w-full object-cover opacity-25">
                    <div class="relative">
                        <p class="text-sm font-semibold uppercase tracking-wider text-blue-400">Cara merencanakan</p>
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
                                    aria-label="Testimoni sebelumnya"
                                    class="swiper-button-prev static! m-0! grid size-11! place-items-center rounded-full border border-slate-200 bg-white hover:bg-slate-50 transition-colors after:text-sm! after:font-bold! after:text-slate-700!"></button>
                            <button type="button"
                                    aria-label="Testimoni berikutnya"
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
        <section class="bg-white py-16" aria-labelledby="blog-heading">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between" data-aos="fade-up">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-wider text-blue-600">Insight perjalanan</p>
                        <h2 id="blog-heading" class="mt-2 text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">{{ __('frontend.blog.title') }}</h2>
                        <p class="mt-4 max-w-2xl text-sm leading-7 text-slate-500">{{ __('frontend.blog.subtitle') }}</p>
                    </div>

                    <a href="{{ route('blog.index', ['locale' => $locale]) }}"
                       class="inline-flex w-full items-center justify-center gap-1.5 rounded-full border border-slate-200 bg-white px-5 py-2.5 text-xs font-semibold text-slate-700 transition-colors hover:bg-slate-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 sm:w-fit">
                        {{ __('frontend.blog.view_all') }}
                        <x-lucide-arrow-right class="h-3.5 w-3.5" aria-hidden="true" />
                    </a>
                </div>

                @if($latestPosts->isNotEmpty())
                    <div class="mt-10 grid gap-6 lg:grid-cols-[1.1fr_0.9fr] lg:items-start">
                        <div class="lg:self-start" data-aos="fade-up">
                            <x-ui.post-card :post="$latestPosts->first()" :$locale image-height="h-72 sm:h-96" featured :stretch="false" />
                        </div>

                        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-1">
                            @foreach($latestPosts->skip(1) as $post)
                                <div data-aos="fade-up" data-aos-delay="{{ $loop->iteration * 80 }}">
                                    <x-ui.post-card :$post :$locale image-height="h-48" />
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="mt-10 rounded-2xl border border-slate-200/80 bg-slate-50 p-10 text-center">
                        <h3 class="font-bold text-slate-900">{{ __('frontend.blog.empty.title') }}</h3>
                        <p class="mt-2 text-sm text-slate-500">{{ __('frontend.blog.empty.subtitle') }}</p>
                    </div>
                @endif
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
                        <p class="text-sm font-semibold uppercase tracking-wider text-blue-100">Mulai diskusi</p>
                        <h2 id="final-cta-heading" class="mt-3 text-3xl font-extrabold tracking-tight">{{ __('frontend.cta.title') }}</h2>
                        <p class="mt-3 max-w-2xl text-sm leading-relaxed text-blue-100/90">{{ __('frontend.cta.subtitle') }}</p>
                    </div>
                    <div class="flex flex-col gap-3 sm:flex-row lg:col-span-4 lg:justify-end">
                        @if($waNumber)
                            <a href="https://wa.me/{{ $waNumber }}" target="_blank" rel="noopener"
                               class="inline-flex w-full items-center justify-center gap-1.5 whitespace-nowrap rounded-full bg-white px-5 py-3 text-sm font-semibold text-blue-600 transition-colors hover:bg-slate-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white sm:w-auto">
                                <x-lucide-message-circle class="h-4 w-4" aria-hidden="true"/>
                                {{ __('frontend.cta.button_whatsapp') }}
                            </a>
                        @endif
                        <a href="{{ route('contact.index', ['locale' => $locale]) }}"
                           class="inline-flex w-full items-center justify-center gap-1.5 whitespace-nowrap rounded-full bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition-colors hover:bg-slate-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-900 sm:w-auto">
                            {{ __('frontend.cta.button_consult') }}
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </div>
</x-layouts::app>
