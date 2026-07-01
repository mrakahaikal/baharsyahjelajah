@props(['locale', 'banners'])
@php
    $bannerSlides = $banners->isNotEmpty() ? $banners : collect([
            (object) [
                'title' => 'Tour halal yang informatif dan mudah dikonsultasikan',
                'subtitle' => 'Jelajahi rute, itinerary, fasilitas, dan insight perjalanan sebelum menentukan pilihan.',
                'image_path' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1800&q=85',
            ],
            (object) [
                'title' => 'Panduan perjalanan untuk memilih rute dengan lebih yakin',
                'subtitle' => 'Artikel destinasi, tips persiapan, dan rekomendasi itinerary membantu Anda merencanakan perjalanan yang masuk akal.',
                'image_path' => 'https://images.unsplash.com/photo-1524492412937-b28074a5d7da?auto=format&fit=crop&w=1800&q=85',
            ],
        ]);

    $imageUrl = fn (?string $path, string $fallback): string => $path
                ? (str_starts_with($path, 'http') ? $path : \Illuminate\Support\Facades\Storage::url($path))
                : $fallback;
@endphp

<section class="relative overflow-hidden text-white" aria-labelledby="home-hero-heading">
    <div class="absolute inset-0 -z-10">
        <div class="swiper js-hero-swiper absolute inset-0">
            <div class="swiper-wrapper">
                @foreach($bannerSlides as $slide)
                    <div class="swiper-slide">
                        <img src="{{ $imageUrl($slide->image_path ?? null, 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1800&q=85') }}" alt="" width="1800" height="1100" @if($loop->first) fetchpriority="high" @else loading="lazy" @endif class="h-full w-full object-cover">
                    </div>
                @endforeach
            </div>
            <div class="swiper-pagination bottom-8! left-auto! right-6! w-auto! text-white"></div>
        </div>
    </div>
    
    <div class="absolute inset-0 bg-linear-to-r from-brand-primary/95 via-brand-primary/80 to-brand-primary/20"></div>
    <div class="absolute inset-x-0 bottom-0 h-32 bg-linear-to-t from-white to-transparent"></div>

    <div class="relative mx-auto max-w-7xl px-4 pb-36 pt-32 sm:px-6 lg:px-8 lg:pb-44 lg:pt-36">
        <div class="max-w-3xl" data-aos="fade-up">
            <p class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-2 text-xs font-bold uppercase tracking-[0.2em] text-blue-100 ring-1 ring-white/20 backdrop-blur-md">
                <x-lucide-shield-check class="h-4 w-4 text-blue-400" />
                {{ $locale === 'id' ? 'Tour Halal & Panduan Perjalanan' : ($locale === 'ms' ? 'Lawatan Halal & Panduan Perjalanan' : 'Halal Tours & Travel Guides') }}
            </p>
            <h1 id="home-hero-heading" class="mt-6 text-4xl font-extrabold leading-tight tracking-tight sm:text-5xl lg:text-6xl">
                {{ $locale === 'id' ? 'Rencanakan' : ($locale === 'ms' ? 'Rancang' : 'Plan') }} <span class="text-blue-400">{{ $locale === 'id' ? 'tour yang lebih jelas' : ($locale === 'ms' ? 'lawatan yang lebih jelas' : 'a clearer trip') }}</span>{{ $locale === 'en' ? '.' : '.' }}
            </h1>
            <p class="mt-5 max-w-2xl text-base leading-8 text-slate-300">
                {{ $locale === 'id' ? 'Bandingkan paket tour, cek detail itinerary, baca panduan destinasi, lalu konsultasikan rute yang paling sesuai dengan gaya perjalanan dan jumlah peserta Anda.' : ($locale === 'ms' ? 'Bandingkan pakej lawatan, semak detail itinerari, baca panduan destinasi, lalu bincangkan laluan yang sesuai dengan gaya perjalanan dan jumlah peserta anda.' : 'Compare tour packages, review itinerary details, read destination guides, then consult the route that fits your travel style and group size.') }}
            </p>
            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                <a href="#search-panel" class="inline-flex items-center justify-center gap-2 rounded-full bg-blue-600 px-7 py-3.5 text-sm font-semibold text-white shadow-lg shadow-blue-600/20 transition-[background-color,transform] duration-300 hover:scale-[1.02] hover:bg-blue-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-200">
                    {{ $locale === 'id' ? 'Cari Tour' : ($locale === 'ms' ? 'Cari Lawatan' : 'Find Tours') }}
                    <x-lucide-arrow-right class="h-4 w-4" aria-hidden="true" />
                </a>
                <a href="{{ route('contact.index', ['locale' => $locale]) }}" class="inline-flex items-center justify-center gap-2 rounded-full bg-white/10 px-7 py-3.5 text-sm font-semibold text-white ring-1 ring-white/20 backdrop-blur-md transition-[background-color,transform] duration-300 hover:scale-[1.02] hover:bg-white/20 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
                    {{ $locale === 'id' ? 'Diskusikan Itinerary' : ($locale === 'ms' ? 'Bincangkan Itinerari' : 'Discuss an Itinerary') }}
                    <x-lucide-message-circle class="h-4 w-4" aria-hidden="true" />
                </a>
            </div>
        </div>

        <div class="mt-12 grid max-w-3xl grid-cols-1 gap-3 sm:grid-cols-3 sm:gap-4" data-aos="fade-up" data-aos-delay="120">
            @foreach([
                ['value' => '35+', 'label' => $locale === 'id' ? 'Destinasi Dikurasi' : ($locale === 'ms' ? 'Destinasi Dikurasi' : 'Curated Destinations')],
                ['value' => '3', 'label' => $locale === 'id' ? 'Bahasa Konten' : ($locale === 'ms' ? 'Bahasa Kandungan' : 'Content Languages')],
                ['value' => '1:1', 'label' => $locale === 'id' ? 'Konsultasi Itinerary' : ($locale === 'ms' ? 'Konsultasi Itinerari' : 'Itinerary Consultation')],
            ] as $stat)
                <div class="rounded-2xl bg-white/5 p-5 ring-1 ring-white/10 backdrop-blur-sm transition-[background-color,box-shadow,transform] duration-300 hover:scale-[1.02] hover:bg-white/10 hover:ring-white/20">
                    <div class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white">{{ $stat['value'] }}</div>
                    <p class="mt-1 text-[10px] sm:text-xs font-semibold uppercase tracking-wider text-slate-400">{{ $stat['label'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
