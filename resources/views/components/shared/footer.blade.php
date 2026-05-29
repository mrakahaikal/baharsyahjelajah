<footer class="bg-gray-900 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-14 pb-8">

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10 mb-12">

            <!-- Brand -->
            <div class="space-y-5">
                <a href="{{ route('home', ['locale' => app()->getLocale()]) }}" class="block">
                    <img src="{{ asset('images/logo-baharsyah-jelajah.png') }}" alt="Baharsyah Jelajah"
                         class="h-9 w-auto brightness-0 invert object-contain">
                </a>
                <p class="text-gray-400 text-sm leading-relaxed">
                    {{ __('frontend.footer.description') }}
                </p>
                <div class="flex gap-3">
                    <a href="#" class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center hover:bg-white/20 transition-colors">
                        <span class="sr-only">Instagram</span>
                        <x-lucide-instagram class="w-4 h-4" />
                    </a>
                    <a href="#" class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center hover:bg-white/20 transition-colors">
                        <span class="sr-only">Facebook</span>
                        <x-lucide-facebook class="w-4 h-4" />
                    </a>
                    <a href="https://wa.me/6281234567890" target="_blank"
                       class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center hover:bg-white/20 transition-colors">
                        <span class="sr-only">WhatsApp</span>
                        <x-lucide-message-circle class="w-4 h-4" />
                    </a>
                </div>
            </div>

            <!-- Services -->
            <div>
                <h3 class="text-sm font-semibold text-white mb-4">{{ __('frontend.footer.services') }}</h3>
                <ul class="space-y-3">
                    <li><a href="{{ route('transport.index', ['locale' => app()->getLocale()]) }}" class="text-sm text-gray-400 hover:text-white transition-colors">{{ __('frontend.nav.transport') }}</a></li>
                    <li><a href="{{ route('tour.index', ['locale' => app()->getLocale()]) }}"      class="text-sm text-gray-400 hover:text-white transition-colors">{{ __('frontend.nav.tour') }}</a></li>
                    <li><a href="{{ route('umroh.index', ['locale' => app()->getLocale()]) }}"     class="text-sm text-gray-400 hover:text-white transition-colors">{{ __('frontend.nav.umroh') }}</a></li>
                    <li><a href="{{ route('visa.index', ['locale' => app()->getLocale()]) }}"      class="text-sm text-gray-400 hover:text-white transition-colors">{{ __('frontend.nav.visa') }}</a></li>
                    <li><a href="{{ route('shop.index', ['locale' => app()->getLocale()]) }}"      class="text-sm text-gray-400 hover:text-white transition-colors">{{ __('frontend.nav.shop') }}</a></li>
                </ul>
            </div>

            <!-- Support -->
            <div>
                <h3 class="text-sm font-semibold text-white mb-4">{{ __('frontend.footer.support') }}</h3>
                <ul class="space-y-3">
                    <li><a href="{{ route('testimonials.index', ['locale' => app()->getLocale()]) }}" class="text-sm text-gray-400 hover:text-white transition-colors">Testimoni</a></li>
                    <li><a href="{{ route('gallery.index', ['locale' => app()->getLocale()]) }}"      class="text-sm text-gray-400 hover:text-white transition-colors">Galeri</a></li>
                    <li><a href="{{ route('blog.index', ['locale' => app()->getLocale()]) }}"         class="text-sm text-gray-400 hover:text-white transition-colors">{{ __('frontend.nav.blog') }}</a></li>
                    <li><a href="#"                                                                    class="text-sm text-gray-400 hover:text-white transition-colors">Kontak Kami</a></li>
                </ul>
            </div>

            <!-- Contact -->
            <div>
                <h3 class="text-sm font-semibold text-white mb-4">{{ __('frontend.footer.contact') }}</h3>
                <ul class="space-y-3">
                    <li class="flex items-start gap-2.5 text-sm text-gray-400">
                        <x-lucide-map-pin class="w-4 h-4 shrink-0 mt-0.5" />
                        <span>Jakarta, Indonesia / Kuala Lumpur, Malaysia</span>
                    </li>
                    <li class="flex items-center gap-2.5 text-sm text-gray-400">
                        <x-lucide-mail class="w-4 h-4 shrink-0" />
                        <span>info@baharsyahjelajah.com</span>
                    </li>
                    <li class="flex items-center gap-2.5 text-sm text-gray-400">
                        <x-lucide-phone class="w-4 h-4 shrink-0" />
                        <span>+62 812-3456-7890</span>
                    </li>
                </ul>
            </div>

        </div>

        <div class="border-t border-white/10 pt-6 flex flex-col sm:flex-row justify-between items-center gap-3">
            <p class="text-xs text-gray-500">&copy; {{ date('Y') }} Baharsyah Jelajah. {{ __('frontend.footer.rights') }}</p>
            <div class="flex gap-5">
                <a href="#" class="text-xs text-gray-500 hover:text-gray-300 transition-colors">Privacy Policy</a>
                <a href="#" class="text-xs text-gray-500 hover:text-gray-300 transition-colors">Terms of Service</a>
            </div>
        </div>

    </div>
</footer>
