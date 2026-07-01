@php
    $locale = app()->getLocale();
    $waNumber = app(\App\Settings\GeneralSettings::class)->whatsapp_number;

    $footerSettings = app(\App\Settings\FooterSettings::class);
    $subscribeTitle = $footerSettings->subscribe_title[$locale] ?? $footerSettings->subscribe_title['id'] ?? '';
    $subscribeSubtitle = $footerSettings->subscribe_subtitle[$locale] ?? $footerSettings->subscribe_subtitle['id'] ?? '';
    $copyrightText = $footerSettings->copyright_text[$locale] ?? $footerSettings->copyright_text['id'] ?? '';
@endphp

{{--<footer class="bg-slate-950 text-white">--}}
{{--    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8 lg:py-16">--}}
{{--        <div class="mb-12 rounded-3xl border border-white/10 bg-white/[0.04] p-6 sm:p-8">--}}
{{--            <div class="grid gap-6 lg:grid-cols-12 lg:items-center">--}}
{{--                <div class="lg:col-span-7">--}}
{{--                    <p class="text-xs font-bold uppercase tracking-[0.22em] text-blue-300">Baharsyah Jelajah</p>--}}
{{--                    <h2 class="mt-3 text-2xl font-bold leading-tight sm:text-3xl">Satu tim untuk tour halal, umroh, visa, dan transportasi.</h2>--}}
{{--                    <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-300">{{ __('frontend.footer.description') }}</p>--}}
{{--                </div>--}}
{{--                <div class="flex flex-col gap-3 sm:flex-row lg:col-span-5 lg:justify-end">--}}
{{--                    <a href="{{ route('tour.index', ['locale' => $locale]) }}" class="inline-flex items-center justify-center gap-2 rounded-full bg-white px-5 py-3 text-sm font-bold text-slate-950 transition hover:bg-blue-50">--}}
{{--                        Lihat Paket--}}
{{--                        <x-lucide-arrow-right class="h-4 w-4" />--}}
{{--                    </a>--}}
{{--                    @if($waNumber)--}}
{{--                        <a href="https://wa.me/{{ $waNumber }}" target="_blank" rel="noopener" class="inline-flex items-center justify-center gap-2 rounded-full bg-orange-500 px-5 py-3 text-sm font-bold text-white transition hover:bg-orange-600">--}}
{{--                            <x-lucide-message-circle class="h-4 w-4" />--}}
{{--                            WhatsApp--}}
{{--                        </a>--}}
{{--                    @endif--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}

{{--        <div class="grid grid-cols-1 gap-10 md:grid-cols-2 lg:grid-cols-4">--}}
{{--            <div class="space-y-5">--}}
{{--                <a href="{{ route('home', ['locale' => $locale]) }}" class="block">--}}
{{--                    <img src="{{ asset('images/logo-baharsyah-jelajah.png') }}" alt="Baharsyah Jelajah" class="h-10 w-auto brightness-0 invert">--}}
{{--                </a>--}}
{{--                <p class="text-sm leading-7 text-slate-400">Platform perjalanan untuk itinerary halal, layanan ibadah, dan mobilitas yang dirancang supaya konsultasi lebih cepat dan keputusan lebih jelas.</p>--}}
{{--                <div class="flex gap-3">--}}
{{--                    <a href="#" class="grid h-10 w-10 place-items-center rounded-full bg-white/10 transition hover:bg-white/20">--}}
{{--                        <span class="sr-only">Instagram</span>--}}
{{--                        <x-lucide-instagram class="h-4 w-4" />--}}
{{--                    </a>--}}
{{--                    <a href="#" class="grid h-10 w-10 place-items-center rounded-full bg-white/10 transition hover:bg-white/20">--}}
{{--                        <span class="sr-only">Facebook</span>--}}
{{--                        <x-lucide-facebook class="h-4 w-4" />--}}
{{--                    </a>--}}
{{--                    @if($waNumber)--}}
{{--                        <a href="https://wa.me/{{ $waNumber }}" target="_blank" rel="noopener" class="grid h-10 w-10 place-items-center rounded-full bg-white/10 transition hover:bg-white/20">--}}
{{--                            <span class="sr-only">WhatsApp</span>--}}
{{--                            <x-lucide-message-circle class="h-4 w-4" />--}}
{{--                        </a>--}}
{{--                    @endif--}}
{{--                </div>--}}
{{--            </div>--}}

{{--            <div>--}}
{{--                <h3 class="text-sm font-bold text-white">{{ __('frontend.footer.services') }}</h3>--}}
{{--                <ul class="mt-5 space-y-3">--}}
{{--                    <li><a href="{{ route('transport.index', ['locale' => $locale]) }}" class="text-sm text-slate-400 transition hover:text-white">{{ __('frontend.nav.transport') }}</a></li>--}}
{{--                    <li><a href="{{ route('tour.index', ['locale' => $locale]) }}" class="text-sm text-slate-400 transition hover:text-white">{{ __('frontend.nav.tour') }}</a></li>--}}
{{--                    <li><a href="{{ route('umroh.index', ['locale' => $locale]) }}" class="text-sm text-slate-400 transition hover:text-white">{{ __('frontend.nav.umroh') }}</a></li>--}}
{{--                    <li><a href="{{ route('visa.index', ['locale' => $locale]) }}" class="text-sm text-slate-400 transition hover:text-white">{{ __('frontend.nav.visa') }}</a></li>--}}
{{--                    <li><a href="{{ route('shop.index', ['locale' => $locale]) }}" class="text-sm text-slate-400 transition hover:text-white">{{ __('frontend.nav.shop') }}</a></li>--}}
{{--                </ul>--}}
{{--            </div>--}}

{{--            <div>--}}
{{--                <h3 class="text-sm font-bold text-white">{{ __('frontend.footer.support') }}</h3>--}}
{{--                <ul class="mt-5 space-y-3">--}}
{{--                    <li><a href="{{ route('testimonials.index', ['locale' => $locale]) }}" class="text-sm text-slate-400 transition hover:text-white">Testimoni</a></li>--}}
{{--                    <li><a href="{{ route('gallery.index', ['locale' => $locale]) }}" class="text-sm text-slate-400 transition hover:text-white">Galeri</a></li>--}}
{{--                    <li><a href="{{ route('blog.index', ['locale' => $locale]) }}" class="text-sm text-slate-400 transition hover:text-white">{{ __('frontend.nav.blog') }}</a></li>--}}
{{--                    <li><a href="{{ route('home', ['locale' => $locale]) }}#faq" class="text-sm text-slate-400 transition hover:text-white">FAQ</a></li>--}}
{{--                </ul>--}}
{{--            </div>--}}

{{--            <div>--}}
{{--                <h3 class="text-sm font-bold text-white">{{ __('frontend.footer.contact') }}</h3>--}}
{{--                <ul class="mt-5 space-y-4">--}}
{{--                    <li class="flex items-start gap-3 text-sm leading-6 text-slate-400">--}}
{{--                        <x-lucide-map-pin class="mt-0.5 h-4 w-4 shrink-0 text-blue-300" />--}}
{{--                        <span>Jakarta, Indonesia / Kuala Lumpur, Malaysia</span>--}}
{{--                    </li>--}}
{{--                    <li class="flex items-center gap-3 text-sm text-slate-400">--}}
{{--                        <x-lucide-mail class="h-4 w-4 shrink-0 text-blue-300" />--}}
{{--                        <span>info@baharsyahjelajah.com</span>--}}
{{--                    </li>--}}
{{--                    <li class="flex items-center gap-3 text-sm text-slate-400">--}}
{{--                        <x-lucide-phone class="h-4 w-4 shrink-0 text-blue-300" />--}}
{{--                        <span>{{ $waNumber ? '+'.$waNumber : '+62 812-3456-7890' }}</span>--}}
{{--                    </li>--}}
{{--                </ul>--}}
{{--            </div>--}}
{{--        </div>--}}

{{--        <div class="mt-12 flex flex-col gap-3 border-t border-white/10 pt-6 text-xs text-slate-500 sm:flex-row sm:items-center sm:justify-between">--}}
{{--            <p>&copy; {{ date('Y') }} Baharsyah Jelajah. {{ __('frontend.footer.rights') }}</p>--}}
{{--            <div class="flex gap-5">--}}
{{--                <a href="#" class="transition hover:text-slate-300">Privacy Policy</a>--}}
{{--                <a href="#" class="transition hover:text-slate-300">Terms of Service</a>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}
{{--</footer>--}}

<footer class="bg-[#0b1324] text-white pt-16 pb-8 border-t border-slate-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="bg-white/5 rounded-2xl p-6 md:p-8 mb-16 flex flex-col md:flex-row items-center justify-between gap-6 border border-white/10">
            <div>
                <h3 class="text-xl font-bold flex items-center gap-2 mb-2">
                    <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                    {{ $subscribeTitle }}
                </h3>
                <p class="text-sm text-gray-400">{{ $subscribeSubtitle }}</p>
            </div>
            <form class="w-full md:w-auto flex gap-2" @submit.prevent>
                <input type="email" placeholder="Your email address" class="bg-white/10 border border-white/20 text-white px-4 py-3 rounded-lg focus:outline-none focus:border-blue-400 w-full md:w-64 placeholder-gray-400">
                <button type="submit" class="bg-white text-slate-900 px-6 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors shrink-0">Subscribe</button>
            </form>
        </div>

        <div class="mb-12">
            <h4 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-4">Where's your next adventure?</h4>
            <div class="flex flex-wrap gap-4 text-sm text-gray-300">
                <a href="#" class="hover:text-white">Asia</a>
                <a href="#" class="hover:text-white">Europe</a>
                <a href="#" class="hover:text-white">North America</a>
                <a href="#" class="hover:text-white">South America</a>
                <a href="#" class="hover:text-white">Africa</a>
                <a href="#" class="hover:text-white">Antarctica</a>
                <a href="#" class="hover:text-white">Australia</a>
                <a href="#" class="hover:text-white">Middle East</a>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 mb-16">
            <div>
                <h4 class="font-bold mb-4">About Baharsyah Jelajah</h4>
                <ul class="space-y-3 text-sm text-gray-400">
                    <li><a href="#" class="hover:text-white">About Us</a></li>
                    <li><a href="#" class="hover:text-white">Why Travel with Us</a></li>
                    <li><a href="#" class="hover:text-white">Our Values</a></li>
                    <li><a href="#" class="hover:text-white">Sustainability</a></li>
                    <li><a href="#" class="hover:text-white">Careers</a></li>
                </ul>
            </div>
            <div>
                <h4 class="font-bold mb-4">Help Guide</h4>
                <ul class="space-y-3 text-sm text-gray-400">
                    <li><a href="#" class="hover:text-white">FAQs</a></li>
                    <li><a href="#" class="hover:text-white">Trip Information</a></li>
                    <li><a href="#" class="hover:text-white">Visa & Passports</a></li>
                    <li><a href="#" class="hover:text-white">Safety Info</a></li>
                    <li><a href="#" class="hover:text-white">Contact Us</a></li>
                </ul>
            </div>
            <div>
                <h4 class="font-bold mb-4">Travel Agents</h4>
                <ul class="space-y-3 text-sm text-gray-400">
                    <li><a href="#" class="hover:text-white">Agent Login</a></li>
                    <li><a href="#" class="hover:text-white">Agent Registration</a></li>
                    <li><a href="#" class="hover:text-white">B2B Portal</a></li>
                    <li><a href="#" class="hover:text-white">Events</a></li>
                </ul>
            </div>
            <div>
                <h4 class="font-bold mb-4">Community</h4>
                <ul class="space-y-3 text-sm text-gray-400">
                    <li><a href="#" class="hover:text-white">Blog</a></li>
                    <li><a href="#" class="hover:text-white">Reviews</a></li>
                    <li><a href="#" class="hover:text-white">Travel Documentaries</a></li>
                    <li><a href="#" class="hover:text-white">Photo Contests</a></li>
                </ul>
            </div>
        </div>

        <div class="border-t border-white/10 pt-8 text-center md:text-left flex flex-col md:flex-row justify-between items-center gap-4">
            <p class="text-gray-500 text-sm">&copy; {{ date('Y') }} {{ $copyrightText }}</p>
            <div class="flex gap-6 text-sm text-gray-400">
                <a href="#" class="hover:text-white">Terms</a>
                <a href="#" class="hover:text-white">Privacy</a>
                <a href="#" class="hover:text-white">Cookies</a>
            </div>
        </div>

        <div class="mt-8 text-center opacity-10 select-none overflow-hidden">
            <span class="text-[12vw] font-black tracking-tighter leading-none whitespace-nowrap">BAHARSYAH JELAJAH</span>
        </div>
    </div>
</footer>
