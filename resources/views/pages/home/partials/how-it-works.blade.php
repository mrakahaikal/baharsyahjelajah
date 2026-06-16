<!-- How It Works Section -->
<section class="py-14 bg-[#F5F3EF]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10 reveal-fade">
            <div class="w-10 h-0.5 bg-[#89D4CF] mb-3 mx-auto"></div>
            <h2 class="font-display text-3xl sm:text-4xl font-bold text-gray-900 mb-1.5">
                {{ __('frontend.how_it_works.title') }}
            </h2>
            <p class="text-sm text-gray-500 max-w-xl mx-auto">
                {{ __('frontend.how_it_works.subtitle') }}
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 relative">
            <!-- Connector line (desktop only) -->
            <div class="hidden lg:block absolute top-8 left-1/3 right-1/3 h-px bg-gradient-to-r from-[#796FE1]/30 via-[#89D4CF]/50 to-[#796FE1]/30 z-0"></div>

            <!-- Step 1 -->
            <div class="reveal-on-scroll flex flex-col items-center text-center relative z-10" data-delay="1">
                <div class="w-16 h-16 bg-[#89D4CF]/15 border-2 border-[#89D4CF]/30 rounded-2xl flex items-center justify-center mb-5 shadow-sm relative">
                    <x-lucide-search class="w-7 h-7 text-primary" />
                    <span class="absolute -top-2.5 -right-2.5 w-6 h-6 bg-[#796FE1] text-white rounded-full flex items-center justify-center text-xs font-bold shadow-sm">1</span>
                </div>
                <h3 class="text-base font-semibold text-gray-900 mb-2">{{ __('frontend.how_it_works.steps.1.title') }}</h3>
                <p class="text-sm text-gray-500 leading-relaxed max-w-xs mx-auto">
                    {{ __('frontend.how_it_works.steps.1.description') }}
                </p>
            </div>

            <!-- Step 2 -->
            <div class="reveal-on-scroll flex flex-col items-center text-center relative z-10" data-delay="2">
                <div class="w-16 h-16 bg-[#89D4CF]/15 border-2 border-[#89D4CF]/30 rounded-2xl flex items-center justify-center mb-5 shadow-sm relative">
                    <x-lucide-message-circle class="w-7 h-7 text-primary" />
                    <span class="absolute -top-2.5 -right-2.5 w-6 h-6 bg-[#796FE1] text-white rounded-full flex items-center justify-center text-xs font-bold shadow-sm">2</span>
                </div>
                <h3 class="text-base font-semibold text-gray-900 mb-2">{{ __('frontend.how_it_works.steps.2.title') }}</h3>
                <p class="text-sm text-gray-500 leading-relaxed max-w-xs mx-auto">
                    {{ __('frontend.how_it_works.steps.2.description') }}
                </p>
            </div>

            <!-- Step 3 -->
            <div class="reveal-on-scroll flex flex-col items-center text-center relative z-10" data-delay="3">
                <div class="w-16 h-16 bg-[#89D4CF]/15 border-2 border-[#89D4CF]/30 rounded-2xl flex items-center justify-center mb-5 shadow-sm relative">
                    <x-lucide-plane-takeoff class="w-7 h-7 text-primary" />
                    <span class="absolute -top-2.5 -right-2.5 w-6 h-6 bg-[#796FE1] text-white rounded-full flex items-center justify-center text-xs font-bold shadow-sm">3</span>
                </div>
                <h3 class="text-base font-semibold text-gray-900 mb-2">{{ __('frontend.how_it_works.steps.3.title') }}</h3>
                <p class="text-sm text-gray-500 leading-relaxed max-w-xs mx-auto">
                    {{ __('frontend.how_it_works.steps.3.description') }}
                </p>
            </div>
        </div>
    </div>
</section>
