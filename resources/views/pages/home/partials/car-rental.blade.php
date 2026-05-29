<!-- Car Rental Section -->
<section class="py-14 bg-gray-50/60">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-end justify-between mb-8">
            <div>
                <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-1.5">
                    {{ __('frontend.car_rental.title') }}
                </h2>
                <p class="text-sm text-gray-500">{{ __('frontend.car_rental.subtitle') }}</p>
            </div>
            @if($featuredVehicles->isNotEmpty())
                <a href="{{ route('transport.index', ['locale' => app()->getLocale()]) }}"
                   class="shrink-0 text-sm text-primary font-medium hover:underline flex items-center gap-1">
                    {{ __('frontend.car_rental.view_all') }}
                    <x-lucide-arrow-right class="w-3.5 h-3.5" />
                </a>
            @endif
        </div>

        @if($featuredVehicles->isNotEmpty())
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                @foreach($featuredVehicles as $vehicle)
                    <div class="group bg-white rounded-xl overflow-hidden border border-gray-100 hover:shadow-md transition-shadow duration-300 flex flex-col">
                        <!-- Image -->
                        <div class="relative h-44 bg-gray-50 overflow-hidden flex items-center justify-center">
                            <img src="{{ $vehicle->thumbnail_url }}"
                                 alt="{{ $vehicle->name }}"
                                 loading="lazy"
                                 class="h-full w-full object-contain p-3 group-hover:scale-105 transition-transform duration-500">
                        </div>

                        <!-- Content -->
                        <div class="p-5 flex flex-col flex-grow">
                            <h3 class="text-[15px] font-semibold text-gray-900 mb-3 group-hover:text-primary transition-colors">
                                {{ $vehicle->name }}
                            </h3>

                            <div class="flex flex-wrap gap-2 mb-4">
                                <span class="flex items-center gap-1 text-gray-500 text-xs bg-gray-50 px-2.5 py-1 rounded-full">
                                    <x-lucide-users class="w-3 h-3" />
                                    {{ __('frontend.car_rental.labels.pax', ['count' => $vehicle->capacity_pax]) }}
                                </span>
                                <span class="flex items-center gap-1 text-gray-500 text-xs bg-gray-50 px-2.5 py-1 rounded-full">
                                    <x-lucide-gauge class="w-3 h-3" />
                                    {{ $vehicle->transmission }}
                                </span>
                            </div>

                            <div class="mt-auto space-y-1.5 pt-4 border-t border-gray-50">
                                @if($vehicle->price_per_day_idr > 0)
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs text-gray-400">{{ __('frontend.car_rental.labels.daily') }}</span>
                                        <span class="text-sm font-bold text-primary">
                                            {{ \App\Helpers\LocaleHelper::formatPrice($vehicle->price_per_day_idr) }}<span class="text-[10px] font-normal text-gray-400">/hari</span>
                                        </span>
                                    </div>
                                @endif
                                @if($vehicle->price_per_trip_idr > 0)
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs text-gray-400">{{ __('frontend.car_rental.labels.all_in') }}</span>
                                        <span class="text-sm font-bold text-gray-700">
                                            {{ \App\Helpers\LocaleHelper::formatPrice($vehicle->price_per_trip_idr) }}<span class="text-[10px] font-normal text-gray-400">/trip</span>
                                        </span>
                                    </div>
                                @endif

                                <a href="{{ route('transport.show', ['locale' => app()->getLocale(), 'vehicle' => $vehicle->id]) }}"
                                   class="mt-3 w-full h-9 rounded-lg bg-gray-50 text-gray-600 group-hover:bg-primary group-hover:text-white flex items-center justify-center gap-1.5 text-xs font-semibold transition-colors duration-300">
                                    {{ __('frontend.car_rental.labels.daily') !== 'Daily' ? 'Lihat Detail' : 'View Detail' }}
                                    <x-lucide-chevron-right class="w-3.5 h-3.5" />
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-white rounded-xl border border-gray-100 p-12">
                <x-shared.empty-state
                    icon="lucide-car"
                    :title="__('frontend.car_rental.empty.title')"
                    :subtitle="__('frontend.car_rental.empty.subtitle')"
                />
            </div>
        @endif
    </div>
</section>
