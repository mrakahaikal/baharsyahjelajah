<!-- Testimonials Section -->
<section class="py-14 bg-gray-50/60">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-end justify-between mb-8">
            <div>
                <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-1.5">
                    {{ __('frontend.testimonials.title') }}
                </h2>
                <p class="text-sm text-gray-500">{{ __('frontend.testimonials.subtitle') }}</p>
            </div>
            @if($testimonials->isNotEmpty())
                <a href="{{ route('testimonials.index', ['locale' => app()->getLocale()]) }}"
                   class="shrink-0 text-sm text-primary font-medium hover:underline flex items-center gap-1">
                    {{ __('frontend.testimonials.view_all') }}
                    <x-lucide-arrow-right class="w-3.5 h-3.5" />
                </a>
            @endif
        </div>

        @if($testimonials->isNotEmpty())
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($testimonials as $testimonial)
                    <div class="bg-white rounded-xl p-6 border border-gray-100 flex flex-col hover:shadow-md transition-shadow duration-300">
                        <!-- Stars -->
                        <div class="flex gap-0.5 mb-4">
                            @for($i = 0; $i < ($testimonial->rating ?? 5); $i++)
                                <x-lucide-star class="w-3.5 h-3.5 fill-amber-400 text-amber-400" />
                            @endfor
                        </div>

                        <blockquote class="flex-grow">
                            <p class="text-sm text-gray-600 leading-relaxed mb-5">
                                "{{ $testimonial->content }}"
                            </p>
                        </blockquote>

                        <div class="flex items-center gap-3 pt-4 border-t border-gray-50">
                            <div class="w-9 h-9 rounded-full overflow-hidden bg-gray-100 shrink-0">
                                <img src="{{ $testimonial->avatar ? asset('storage/' . $testimonial->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($testimonial->name) . '&color=796FE1&background=EDE9FF&bold=true&size=64' }}"
                                     alt="{{ $testimonial->name }}"
                                     loading="lazy"
                                     class="w-full h-full object-cover">
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-900">{{ $testimonial->name }}</p>
                                @if($testimonial->location)
                                    <p class="text-xs text-gray-400">{{ $testimonial->location }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-white rounded-xl border border-gray-100 p-12">
                <x-shared.empty-state
                    icon="lucide-message-square"
                    :title="__('frontend.testimonials.empty.title')"
                    :subtitle="__('frontend.testimonials.empty.subtitle')"
                />
            </div>
        @endif
    </div>
</section>
