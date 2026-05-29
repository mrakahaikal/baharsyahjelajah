<!-- CTA Section -->
<section class="py-14 bg-gray-50/60">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="relative rounded-2xl overflow-hidden">
            <!-- Background image -->
            <img src="https://images.unsplash.com/photo-1591604466107-ec97de577aff?auto=format&fit=crop&q=80&w=1920"
                 alt=""
                 class="absolute inset-0 w-full h-full object-cover"
                 aria-hidden="true">

            <!-- Primary gradient overlay -->
            <div class="absolute inset-0 bg-gradient-to-r from-primary/95 via-primary/85 to-primary/70"></div>

            <!-- Content -->
            <div class="relative z-10 px-8 py-14 sm:px-14 sm:py-16 text-center max-w-2xl mx-auto">
                <h2 class="text-2xl sm:text-4xl font-bold text-white mb-3 leading-tight">
                    {{ __('frontend.cta.title') }}
                </h2>
                <p class="text-white/80 text-base sm:text-lg mb-8 leading-relaxed">
                    {{ __('frontend.cta.subtitle') }}
                </p>

                @php $waNumber = app(\App\Settings\GeneralSettings::class)->whatsapp_number; @endphp
                <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                    <a href="https://wa.me/{{ $waNumber }}" target="_blank" rel="noopener"
                       class="w-full sm:w-auto inline-flex items-center justify-center gap-2.5 bg-white text-primary hover:bg-gray-50 font-semibold py-3 px-8 rounded-xl transition-colors text-sm shadow-sm">
                        <x-lucide-message-circle class="w-5 h-5" />
                        {{ __('frontend.cta.button_whatsapp') }}
                    </a>
                    <a href="#"
                       class="w-full sm:w-auto inline-flex items-center justify-center gap-2.5 bg-white/15 hover:bg-white/25 text-white font-semibold py-3 px-8 rounded-xl transition-colors text-sm border border-white/25">
                        <x-lucide-info class="w-5 h-5" />
                        {{ __('frontend.cta.button_consult') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
