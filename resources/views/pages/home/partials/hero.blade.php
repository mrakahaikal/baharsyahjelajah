<!-- Hero Section -->
<section class="relative">
    <!-- Image Container -->
    <div class="relative h-[420px] sm:h-[500px] lg:h-[660px] overflow-hidden">
        <img src="https://images.unsplash.com/photo-1523906834658-6e24ef2386f9?auto=format&fit=crop&q=80&w=1920"
             alt="Baharsyah Jelajah"
             class="absolute inset-0 w-full h-full object-cover">

        <!-- Gradient: dark on left + bottom -->
        <div class="absolute inset-0 bg-gradient-to-r from-black/65 via-black/40 to-black/10"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent"></div>

        <!-- Text — bottom-left inside image, aligned with max-w-7xl -->
        <div class="absolute bottom-0 left-0 right-0 z-10">
            <div class="max-w-7xl mx-auto px-6 sm:px-10 lg:px-16 pb-12 sm:pb-14 lg:pb-16">
                <div class="lg:max-w-[50%]">
                    <span class="inline-flex items-center gap-1.5 bg-white/15 backdrop-blur-sm border border-white/20 text-white text-xs font-semibold px-3 py-1.5 rounded-full mb-4">
                        <x-lucide-map-pin class="w-3 h-3" />
                        Destinasi Pilihan
                    </span>
                    <h1 class="text-3xl sm:text-4xl lg:text-5xl xl:text-6xl font-bold text-white leading-tight mb-3">
                        {!! nl2br(e(__('frontend.hero.slides.0.title'))) !!}
                    </h1>
                    <p class="text-white/75 text-sm sm:text-base leading-relaxed">
                        {{ __('frontend.hero.slides.0.subtitle') }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Search Card — desktop only, aligned with max-w-7xl right edge -->
        <div class="hidden lg:flex absolute inset-0 z-20 items-center">
            <div class="w-full max-w-7xl mx-auto px-6 sm:px-10 lg:px-16 flex justify-end">
                <div class="bg-white rounded-2xl shadow-2xl p-7 w-[360px] xl:w-[380px]">
                    <p class="text-xs font-semibold text-primary uppercase tracking-widest mb-1">Mulai Perjalanan</p>
                    <h2 class="text-base font-bold text-gray-900 mb-5">Rencanakan Perjalanan Anda</h2>
                    @include('pages.home.partials.search-form')
                </div>
            </div>
        </div>
    </div>

    <!-- Search Card — mobile only, overlap below image -->
    <div class="lg:hidden relative z-10 -mt-6 px-4 sm:px-6 pb-0">
        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 px-5 py-5">
            <p class="text-xs font-semibold text-primary uppercase tracking-widest mb-1">Mulai Perjalanan</p>
            <h2 class="text-sm font-bold text-gray-900 mb-4">Rencanakan Perjalanan Anda</h2>
            @include('pages.home.partials.search-form')
        </div>
    </div>
</section>
