<!-- Umrah Package Section — Dark Luxury (Umrah Theme) -->
<section class="theme-umrah relative overflow-hidden bg-[#0F0F0F] py-16">
    <!-- Ambient gold glow -->
    <div class="pointer-events-none absolute -top-32 left-1/2 -translate-x-1/2 h-96 w-[42rem] rounded-full bg-[#FFC000]/10 blur-3xl"></div>
    <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_top,rgba(255,192,0,0.06),transparent_60%)]"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-end justify-between mb-10 reveal-fade">
            <div>
                <div class="w-10 h-0.5 bg-primary mb-3"></div>
                <span class="text-[11px] font-semibold uppercase tracking-[0.2em] text-primary/80">Premium</span>
                <h2 class="font-display text-3xl sm:text-4xl font-bold text-white mt-1 mb-1.5">
                    {{ __('frontend.umrah_package.title') }}
                </h2>
                <p class="text-sm text-white/50">{{ __('frontend.umrah_package.subtitle') }}</p>
            </div>
            @if($umrahPackages->isNotEmpty())
                <a href="{{ route('umroh.index', ['locale' => app()->getLocale()]) }}"
                   class="shrink-0 text-sm text-primary font-medium hover:text-primary/80 transition-colors flex items-center gap-1">
                    {{ __('frontend.umrah_package.view_all') }}
                    <x-lucide-arrow-right class="w-3.5 h-3.5" />
                </a>
            @endif
        </div>

        @if($umrahPackages->isNotEmpty())
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($umrahPackages as $package)
                    <div data-delay="{{ ($loop->index % 3) + 1 }}"
                         class="reveal-on-scroll group bg-white/[0.04] rounded-2xl overflow-hidden border border-white/10 hover:border-primary/40 hover:bg-white/[0.06] hover:-translate-y-1 transition-all duration-300 flex flex-col backdrop-blur-sm">
                        <!-- Image -->
                        <div class="relative h-52 overflow-hidden bg-black/40">
                            <img src="{{ $package->thumbnail_url }}"
                                 alt="{{ $package->name }}"
                                 loading="lazy"
                                 class="w-full h-full object-cover opacity-95 group-hover:scale-105 group-hover:opacity-100 transition-all duration-500">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
                            <div class="absolute top-3 left-3 flex items-center gap-2">
                                <span class="bg-black/40 text-white/90 text-[11px] font-semibold px-2.5 py-1 rounded-full backdrop-blur-sm border border-white/15 capitalize">
                                    {{ $package->package_type }}
                                </span>
                                @if($package->duration_days)
                                    <span class="bg-primary text-black text-[11px] font-bold px-2.5 py-1 rounded-full shadow-sm">
                                        {{ __('frontend.umrah_package.labels.duration', ['days' => $package->duration_days]) }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="p-5 flex flex-col flex-grow">
                            <h3 class="text-[15px] font-semibold text-white mb-4 line-clamp-2 group-hover:text-primary transition-colors">
                                {{ $package->name }}
                            </h3>

                            <div class="space-y-3 mb-4">
                                @if($package->airline)
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-lg bg-white/10 flex items-center justify-center shrink-0">
                                            <x-lucide-plane class="w-3.5 h-3.5 text-primary" />
                                        </div>
                                        <div>
                                            <p class="text-[10px] text-white/40 leading-none mb-0.5">{{ __('frontend.umrah_package.labels.airline') }}</p>
                                            <p class="text-xs font-semibold text-white/90">{{ $package->airline }}</p>
                                        </div>
                                    </div>
                                @endif

                                @if($package->hotel_makkah)
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-lg bg-white/10 flex items-center justify-center shrink-0">
                                            <x-lucide-building-2 class="w-3.5 h-3.5 text-primary" />
                                        </div>
                                        <div>
                                            <p class="text-[10px] text-white/40 leading-none mb-0.5">{{ __('frontend.umrah_package.labels.hotel') }}</p>
                                            <p class="text-xs font-semibold text-white/90">{{ $package->hotel_makkah }}</p>
                                            @if($package->hotel_makkah_stars)
                                                <div class="flex gap-0.5 mt-0.5">
                                                    @for($i = 0; $i < $package->hotel_makkah_stars; $i++)
                                                        <x-lucide-star class="w-2.5 h-2.5 fill-primary text-primary" />
                                                    @endfor
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div class="mt-auto pt-4 border-t border-white/10 flex items-center justify-between">
                                <div>
                                    <p class="text-[11px] text-white/40 mb-0.5">{{ __('frontend.umrah_package.labels.start_from') }}</p>
                                    <p class="text-base font-bold text-primary">
                                        {{ \App\Helpers\LocaleHelper::formatPrice($package->price_idr) }}
                                    </p>
                                </div>
                                <a href="{{ route('umroh.show', ['locale' => app()->getLocale(), 'umrah' => $package->id]) }}"
                                   class="text-xs font-semibold text-primary bg-primary/15 hover:bg-primary hover:text-black px-3 py-1.5 rounded-full transition-colors">
                                    Detail
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-white/[0.04] rounded-2xl border border-white/10 p-12">
                <x-shared.empty-state
                    icon="lucide-moon-star"
                    :title="__('frontend.umrah_package.empty.title')"
                    :subtitle="__('frontend.umrah_package.empty.subtitle')"
                />
            </div>
        @endif
    </div>
</section>
