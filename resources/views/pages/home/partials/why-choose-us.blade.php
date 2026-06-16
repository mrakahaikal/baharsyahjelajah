<!-- Why Choose Us Section -->
<section class="py-14 bg-[#F5F3EF]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-8 mb-10 reveal-fade">
            <div class="max-w-xl">
                <div class="w-10 h-0.5 bg-[#89D4CF] mb-3"></div>
                <h2 class="font-display text-3xl sm:text-4xl font-bold text-gray-900 mb-1.5">
                    {!! __('frontend.why_us.title', ['brand' => '<span class="text-primary">Baharsyah</span>']) !!}
                </h2>
                <p class="text-sm text-gray-500">{{ __('frontend.why_us.subtitle') }}</p>
            </div>
            <a href="{{ route('testimonials.index', ['locale' => app()->getLocale()]) }}"
               class="shrink-0 text-sm text-primary font-medium hover:underline flex items-center gap-1">
                {{ __('frontend.why_us.cta') }}
                <x-lucide-arrow-right class="w-3.5 h-3.5" />
            </a>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
            <div class="reveal-on-scroll bg-white rounded-2xl px-6 py-6 shadow-sm" data-delay="1">
                <div class="text-3xl sm:text-4xl font-bold text-[#796FE1] mb-1">10K+</div>
                <div class="text-xs text-gray-500">{{ __('frontend.why_us.stats.pilgrims') }}</div>
            </div>
            <div class="reveal-on-scroll bg-white rounded-2xl px-6 py-6 shadow-sm" data-delay="2">
                <div class="text-3xl sm:text-4xl font-bold text-[#796FE1] mb-1">50+</div>
                <div class="text-xs text-gray-500">{{ __('frontend.why_us.stats.destinations') }}</div>
            </div>
            <div class="reveal-on-scroll bg-white rounded-2xl px-6 py-6 shadow-sm" data-delay="3">
                <div class="text-3xl sm:text-4xl font-bold text-[#796FE1] mb-1">98%</div>
                <div class="text-xs text-gray-500">{{ __('frontend.why_us.stats.satisfaction') }}</div>
            </div>
            <div class="reveal-on-scroll bg-white rounded-2xl px-6 py-6 shadow-sm" data-delay="4">
                <div class="text-3xl sm:text-4xl font-bold text-[#796FE1] mb-1">15+</div>
                <div class="text-xs text-gray-500">{{ __('frontend.why_us.stats.experience') }}</div>
            </div>
        </div>
    </div>
</section>
