<!-- Umrah Package Section -->
<section class="py-14 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-end justify-between mb-8">
            <div>
                <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-1.5">
                    {{ __('frontend.umrah_package.title') }}
                </h2>
                <p class="text-sm text-gray-500">{{ __('frontend.umrah_package.subtitle') }}</p>
            </div>
            @if($umrahPackages->isNotEmpty())
                <a href="{{ route('umroh.index', ['locale' => app()->getLocale()]) }}"
                   class="shrink-0 text-sm text-primary font-medium hover:underline flex items-center gap-1">
                    {{ __('frontend.umrah_package.view_all') }}
                    <x-lucide-arrow-right class="w-3.5 h-3.5" />
                </a>
            @endif
        </div>

        @if($umrahPackages->isNotEmpty())
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($umrahPackages as $package)
                    <div class="group bg-white rounded-xl overflow-hidden border border-gray-100 hover:shadow-md transition-shadow duration-300 flex flex-col">
                        <!-- Image -->
                        <div class="relative h-52 overflow-hidden bg-gray-100">
                            <img src="{{ $package->thumbnail_url }}"
                                 alt="{{ $package->name }}"
                                 loading="lazy"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            <div class="absolute top-3 left-3 flex items-center gap-2">
                                <span class="bg-white/95 text-gray-700 text-[11px] font-semibold px-2.5 py-1 rounded-full shadow-sm capitalize">
                                    {{ $package->package_type }}
                                </span>
                                @if($package->duration_days)
                                    <span class="bg-primary text-white text-[11px] font-semibold px-2.5 py-1 rounded-full shadow-sm">
                                        {{ __('frontend.umrah_package.labels.duration', ['days' => $package->duration_days]) }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="p-5 flex flex-col flex-grow">
                            <h3 class="text-[15px] font-semibold text-gray-900 mb-4 line-clamp-2 group-hover:text-primary transition-colors">
                                {{ $package->name }}
                            </h3>

                            <div class="space-y-3 mb-4">
                                @if($package->airline)
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-lg bg-gray-50 flex items-center justify-center shrink-0">
                                            <x-lucide-plane class="w-3.5 h-3.5 text-gray-500" />
                                        </div>
                                        <div>
                                            <p class="text-[10px] text-gray-400 leading-none mb-0.5">{{ __('frontend.umrah_package.labels.airline') }}</p>
                                            <p class="text-xs font-semibold text-gray-700">{{ $package->airline }}</p>
                                        </div>
                                    </div>
                                @endif

                                @if($package->hotel_makkah)
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-lg bg-gray-50 flex items-center justify-center shrink-0">
                                            <x-lucide-building-2 class="w-3.5 h-3.5 text-gray-500" />
                                        </div>
                                        <div>
                                            <p class="text-[10px] text-gray-400 leading-none mb-0.5">{{ __('frontend.umrah_package.labels.hotel') }}</p>
                                            <p class="text-xs font-semibold text-gray-700">{{ $package->hotel_makkah }}</p>
                                            @if($package->hotel_makkah_stars)
                                                <div class="flex gap-0.5 mt-0.5">
                                                    @for($i = 0; $i < $package->hotel_makkah_stars; $i++)
                                                        <x-lucide-star class="w-2.5 h-2.5 fill-amber-400 text-amber-400" />
                                                    @endfor
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div class="mt-auto pt-4 border-t border-gray-50 flex items-center justify-between">
                                <div>
                                    <p class="text-[11px] text-gray-400 mb-0.5">{{ __('frontend.umrah_package.labels.start_from') }}</p>
                                    <p class="text-base font-bold text-primary">
                                        {{ \App\Helpers\LocaleHelper::formatPrice($package->price_idr) }}
                                    </p>
                                </div>
                                <a href="{{ route('umroh.show', ['locale' => app()->getLocale(), 'umrah' => $package->id]) }}"
                                   class="text-xs font-medium text-primary bg-primary/10 hover:bg-primary hover:text-white px-3 py-1.5 rounded-full transition-colors">
                                    Detail
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-gray-50 rounded-xl border border-gray-100 p-12">
                <x-shared.empty-state
                    icon="lucide-moon-star"
                    :title="__('frontend.umrah_package.empty.title')"
                    :subtitle="__('frontend.umrah_package.empty.subtitle')"
                />
            </div>
        @endif
    </div>
</section>
