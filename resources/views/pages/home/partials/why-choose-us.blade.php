<!-- Why Choose Us Section -->
<section class="py-14 bg-white border-y border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-8 mb-10">
            <div class="max-w-xl">
                <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-1.5">
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

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-0 divide-x divide-gray-100">
            <div class="px-6 lg:px-8 py-2 first:pl-0">
                <div class="text-3xl sm:text-4xl font-bold text-gray-900 mb-1">10K+</div>
                <div class="text-xs text-gray-500">{{ __('frontend.why_us.stats.pilgrims') }}</div>
            </div>
            <div class="px-6 lg:px-8 py-2">
                <div class="text-3xl sm:text-4xl font-bold text-gray-900 mb-1">50+</div>
                <div class="text-xs text-gray-500">{{ __('frontend.why_us.stats.destinations') }}</div>
            </div>
            <div class="px-6 lg:px-8 py-2">
                <div class="text-3xl sm:text-4xl font-bold text-gray-900 mb-1">98%</div>
                <div class="text-xs text-gray-500">{{ __('frontend.why_us.stats.satisfaction') }}</div>
            </div>
            <div class="px-6 lg:px-8 py-2">
                <div class="text-3xl sm:text-4xl font-bold text-primary mb-1">15+</div>
                <div class="text-xs text-gray-500">{{ __('frontend.why_us.stats.experience') }}</div>
            </div>
        </div>
    </div>
</section>
