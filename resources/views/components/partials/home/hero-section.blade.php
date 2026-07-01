@props(['locale', 'banners'])
@php
    $bannerSlides = $banners->isNotEmpty() ? $banners : collect([
            (object) [
                'title' => 'Tour halal, umroh, dan transport dalam satu layanan',
                'subtitle' => 'Konsultasi rute, jadwal, dan kebutuhan perjalanan bersama tim Baharsyah Jelajah.',
                'image_path' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1800&q=85',
            ],
            (object) [
                'title' => 'Perjalanan ibadah yang lebih tenang',
                'subtitle' => 'Paket umroh dengan pilihan hotel, maskapai, dan pendampingan yang jelas.',
                'image_path' => 'https://images.unsplash.com/photo-1564767609342-620cb19b2357?auto=format&fit=crop&w=1800&q=85',
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
                Halal Travel & Tour Terpercaya
            </p>
            <h1 id="home-hero-heading" class="mt-6 text-4xl font-extrabold leading-tight tracking-tight sm:text-5xl lg:text-6xl">
                Rencanakan <span class="text-blue-400">tour, umroh, & transport</span> tanpa ribet.
            </h1>
            <p class="mt-5 max-w-2xl text-base leading-8 text-slate-300">
                Bandingkan paket, cek fasilitas lengkap, dan konsultasikan kebutuhan perjalanan bersama tim ahli kami yang siap memahami rute wisata, layanan ibadah, dan mobilitas keluarga Anda.
            </p>
            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                <a href="#search-panel" class="inline-flex items-center justify-center gap-2 rounded-full bg-blue-600 px-7 py-3.5 text-sm font-semibold text-white shadow-lg shadow-blue-600/20 transition-[background-color,transform] duration-300 hover:scale-[1.02] hover:bg-blue-700">
                    Mulai Cari Paket
                    <x-lucide-arrow-right class="h-4 w-4" />
                </a>
                <a href="{{ route('umroh.index', ['locale' => $locale]) }}" class="inline-flex items-center justify-center gap-2 rounded-full bg-white/10 px-7 py-3.5 text-sm font-semibold text-white ring-1 ring-white/20 backdrop-blur-md transition-[background-color,transform] duration-300 hover:scale-[1.02] hover:bg-white/20">
                    Lihat Paket Umroh
                    <x-lucide-moon class="h-4 w-4" />
                </a>
            </div>
        </div>

        <div class="mt-12 grid max-w-3xl grid-cols-1 gap-3 sm:grid-cols-3 sm:gap-4" data-aos="fade-up" data-aos-delay="120">
            @foreach([
                ['value' => '35+', 'label' => 'Destinasi'],
                ['value' => '1.200+', 'label' => 'Jamaah & Traveler'],
                ['value' => '24/7', 'label' => 'Bantuan Perjalanan'],
            ] as $stat)
                <div class="rounded-2xl bg-white/5 p-5 ring-1 ring-white/10 backdrop-blur-sm transition-[background-color,box-shadow,transform] duration-300 hover:scale-[1.02] hover:bg-white/10 hover:ring-white/20">
                    <div class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white">{{ $stat['value'] }}</div>
                    <p class="mt-1 text-[10px] sm:text-xs font-semibold uppercase tracking-wider text-slate-400">{{ $stat['label'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
