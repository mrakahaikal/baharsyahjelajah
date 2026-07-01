<!-- Featured Tour Section -->
<section class="py-14 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-end justify-between mb-8">
            <div>
                <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-1.5">
                    {{ __('frontend.featured_tour.title') }}
                </h2>
                <p class="text-sm text-gray-500">{{ __('frontend.featured_tour.subtitle') }}</p>
            </div>
            @if($featuredTours->isNotEmpty())
                <a href="{{ route('tour.index', ['locale' => app()->getLocale()]) }}"
                   class="shrink-0 text-sm text-primary font-medium hover:underline flex items-center gap-1">
                    {{ __('frontend.featured_tour.view_all') }}
                    <x-lucide-arrow-right class="w-3.5 h-3.5" />
                </a>
            @endif
        </div>

        @if($featuredTours->isNotEmpty())
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($featuredTours as $tour)
                    <a href="{{ route('tour.show', ['locale' => app()->getLocale(), 'tour' => $tour->slug]) }}"
                       class="group bg-white rounded-xl overflow-hidden border border-gray-100 hover:shadow-md transition-shadow duration-300 flex flex-col">
                        <!-- Image -->
                        <div class="relative h-52 overflow-hidden bg-gray-100">
                            <img src="{{ $tour->thumbnail_url }}"
                                 alt="{{ $tour->name }}"
                                 loading="lazy"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            @if($tour->category)
                                <span class="absolute top-3 left-3 bg-white/95 text-gray-700 text-[11px] font-semibold px-2.5 py-1 rounded-full shadow-sm">
                                    {{ $tour->category->name }}
                                </span>
                            @endif
                        </div>

                        <!-- Content -->
                        <div class="p-5 flex flex-col flex-grow">
                            <h3 class="text-[15px] font-semibold text-gray-900 mb-1.5 line-clamp-2 group-hover:text-primary transition-colors">
                                {{ $tour->name }}
                            </h3>
                            <div class="flex items-center gap-1.5 text-gray-400 text-xs mb-4">
                                <x-lucide-clock class="w-3.5 h-3.5" />
                                {{ __('frontend.featured_tour.labels.duration', ['days' => $tour->duration_days]) }}
                            </div>

                            <div class="mt-auto flex items-center justify-between pt-4 border-t border-gray-50">
                                <div>
                                    <p class="text-[11px] text-gray-400 mb-0.5">{{ __('frontend.featured_tour.labels.start_from') }}</p>
                                    <p class="text-base font-bold text-primary">
                                        {{ $tour->formatted_price }}
                                    </p>
                                </div>
                                <span class="text-xs font-medium text-primary bg-primary/10 px-3 py-1.5 rounded-full group-hover:bg-primary group-hover:text-white transition-colors">
                                    Detail
                                </span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <div class="bg-gray-50 rounded-xl border border-gray-100 p-12">
                <x-shared.empty-state
                    icon="lucide-map"
                    :title="__('frontend.featured_tour.empty.title', ['default' => 'Belum ada paket unggulan'])"
                    :subtitle="__('frontend.featured_tour.empty.subtitle', ['default' => 'Saat ini kami sedang menyiapkan paket perjalanan terbaik untuk Anda. Silakan cek kembali nanti.'])"
                />
            </div>
        @endif
    </div>
</section>
