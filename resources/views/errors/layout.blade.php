@php
    $supportedLocales = ['id', 'ms', 'en'];
    $routeLocale = request()->route('locale');
    $segmentLocale = request()->segment(1);
    $locale = in_array($routeLocale, $supportedLocales, true)
        ? $routeLocale
        : (in_array($segmentLocale, $supportedLocales, true) ? $segmentLocale : 'id');

    $statusCode = (int) ($statusCode ?? 500);
    $isServerError = $statusCode >= 500;
    $statusFamily = $isServerError ? '5xx' : '4xx';

    $copy = [
        'id' => [
            'eyebrow' => 'Halaman tersesat',
            'support' => 'Butuh bantuan cepat?',
            'support_text' => 'Tim kami bisa membantu lewat kanal resmi jika Anda sedang mencari paket atau itinerary tertentu.',
            'home' => 'Kembali ke Beranda',
            'tour' => 'Lihat Paket Tour',
            'blog' => 'Baca Panduan',
            'contact' => 'Hubungi Tim',
            'reload' => 'Muat ulang halaman',
            '4xx' => [
                'label' => 'Rute tidak ditemukan',
                'title' => 'Arah perjalanan ini belum tersedia.',
                'description' => 'Tautan mungkin berubah, halaman sudah dipindahkan, atau alamat yang dibuka belum tepat. Anda bisa kembali ke katalog utama dan melanjutkan eksplorasi dari sana.',
            ],
            '5xx' => [
                'label' => 'Sistem sedang dirapikan',
                'title' => 'Ada kendala sementara di sisi sistem.',
                'description' => 'Kami sedang memulihkan halaman ini. Coba muat ulang dalam beberapa saat, atau hubungi tim jika Anda perlu bantuan memilih rute perjalanan.',
            ],
        ],
        'en' => [
            'eyebrow' => 'Page off route',
            'support' => 'Need quick help?',
            'support_text' => 'Our team can help through the official contact channel if you are looking for a specific trip or itinerary.',
            'home' => 'Back to Home',
            'tour' => 'Browse Tours',
            'blog' => 'Read Guides',
            'contact' => 'Contact Team',
            'reload' => 'Reload page',
            '4xx' => [
                'label' => 'Route not found',
                'title' => 'This travel route is not available yet.',
                'description' => 'The link may have changed, the page may have moved, or the address may be incorrect. You can return to the main catalog and continue from there.',
            ],
            '5xx' => [
                'label' => 'System is being restored',
                'title' => 'There is a temporary system issue.',
                'description' => 'We are restoring this page. Try reloading in a moment, or contact the team if you need help choosing a travel route.',
            ],
        ],
        'ms' => [
            'eyebrow' => 'Halaman tersasar',
            'support' => 'Perlukan bantuan segera?',
            'support_text' => 'Pasukan kami boleh membantu melalui saluran rasmi jika anda mencari pakej atau itinerary tertentu.',
            'home' => 'Kembali ke Utama',
            'tour' => 'Lihat Pakej Tour',
            'blog' => 'Baca Panduan',
            'contact' => 'Hubungi Pasukan',
            'reload' => 'Muat semula halaman',
            '4xx' => [
                'label' => 'Laluan tidak ditemui',
                'title' => 'Arah perjalanan ini belum tersedia.',
                'description' => 'Pautan mungkin berubah, halaman sudah dipindahkan, atau alamat yang dibuka belum tepat. Anda boleh kembali ke katalog utama dan meneruskan carian dari sana.',
            ],
            '5xx' => [
                'label' => 'Sistem sedang dipulihkan',
                'title' => 'Terdapat masalah sementara pada sistem.',
                'description' => 'Kami sedang memulihkan halaman ini. Cuba muat semula sebentar lagi, atau hubungi pasukan jika anda perlukan bantuan memilih laluan perjalanan.',
            ],
        ],
    ];

    $localized = $copy[$locale] ?? $copy['id'];
    $variant = $isServerError ? '5xx' : '4xx';
    $message = $localized[$variant];

    $homeUrl = route('home', ['locale' => $locale]);
    $tourUrl = route('tour.index', ['locale' => $locale]);
    $blogUrl = route('blog.index', ['locale' => $locale]);
    $contactUrl = route('contact.index', ['locale' => $locale]);
@endphp

<!DOCTYPE html>
<html lang="{{ $locale }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, follow">
    <title>{{ $statusCode }} | Baharsyah Jelajah</title>

    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-slate-950 font-sans text-white antialiased">
    <main class="relative isolate min-h-screen overflow-hidden">
        <div class="absolute inset-0 -z-20 bg-[radial-gradient(circle_at_18%_18%,rgba(37,99,235,0.38),transparent_28%),radial-gradient(circle_at_85%_18%,rgba(34,197,94,0.28),transparent_24%),linear-gradient(135deg,#020617_0%,#0f172a_48%,#082f49_100%)]"></div>
        <div class="absolute inset-0 -z-10 bg-[linear-gradient(rgba(255,255,255,0.055)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.055)_1px,transparent_1px)] bg-[size:54px_54px] opacity-40"></div>
        <div class="absolute left-0 top-0 -z-10 h-48 w-full bg-gradient-to-b from-white/10 to-transparent"></div>

        <section class="mx-auto flex min-h-screen w-full max-w-7xl flex-col px-4 py-8 sm:px-6 lg:px-8">
            <header class="flex items-center justify-between gap-4">
                <a href="{{ $homeUrl }}" class="inline-flex rounded-md focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-blue-300" aria-label="Baharsyah Jelajah Home">
                    <img src="{{ asset('images/logo-baharsyah-jelajah.webp') }}" alt="Baharsyah Jelajah" width="176" height="44" class="h-11 w-auto brightness-0 invert">
                </a>

                <span class="rounded-full border border-white/15 bg-white/10 px-4 py-2 text-xs font-bold uppercase tracking-wider text-blue-100">
                    {{ $statusCode }} {{ $statusFamily }}
                </span>
            </header>

            <div class="grid flex-1 items-center gap-10 py-14 lg:grid-cols-[minmax(0,1.05fr)_minmax(22rem,0.95fr)] lg:py-20">
                <div class="max-w-3xl">
                    <p class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-4 py-2 text-sm font-semibold text-blue-100 backdrop-blur">
                        <span class="h-2 w-2 rounded-full {{ $isServerError ? 'bg-amber-300' : 'bg-emerald-300' }}" aria-hidden="true"></span>
                        {{ $localized['eyebrow'] }}
                    </p>

                    <p class="mt-8 text-8xl font-black leading-none tracking-normal text-white sm:text-9xl">
                        {{ $statusCode }}
                    </p>
                    <p class="mt-5 text-sm font-bold uppercase tracking-widest text-blue-200">
                        {{ $message['label'] }}
                    </p>
                    <h1 class="mt-4 max-w-3xl text-4xl font-extrabold tracking-normal text-balance text-white sm:text-5xl lg:text-6xl">
                        {{ $message['title'] }}
                    </h1>
                    <p class="mt-6 max-w-2xl text-base leading-8 text-slate-300 sm:text-lg">
                        {{ $message['description'] }}
                    </p>

                    <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                        <a href="{{ $homeUrl }}" class="inline-flex items-center justify-center gap-2 rounded-full bg-white px-6 py-3 text-sm font-bold text-slate-950 transition-[background-color,transform] duration-200 hover:-translate-y-0.5 hover:bg-blue-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-300">
                            <x-lucide-home class="h-4 w-4" aria-hidden="true" />
                            {{ $localized['home'] }}
                        </a>

                        @if($isServerError)
                            <a href="{{ url()->current() }}" class="inline-flex items-center justify-center gap-2 rounded-full border border-white/20 bg-white/10 px-6 py-3 text-sm font-bold text-white transition-[background-color,transform] duration-200 hover:-translate-y-0.5 hover:bg-white/15 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-300">
                                <x-lucide-refresh-cw class="h-4 w-4" aria-hidden="true" />
                                {{ $localized['reload'] }}
                            </a>
                        @else
                            <a href="{{ $tourUrl }}" class="inline-flex items-center justify-center gap-2 rounded-full border border-white/20 bg-white/10 px-6 py-3 text-sm font-bold text-white transition-[background-color,transform] duration-200 hover:-translate-y-0.5 hover:bg-white/15 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-300">
                                <x-lucide-map class="h-4 w-4" aria-hidden="true" />
                                {{ $localized['tour'] }}
                            </a>
                        @endif
                    </div>
                </div>

                <aside class="rounded-2xl border border-white/15 bg-white/10 p-5 shadow-2xl shadow-slate-950/30 backdrop-blur-xl sm:p-6">
                    <div class="rounded-xl border border-white/10 bg-slate-950/40 p-5">
                        <div class="flex items-start gap-4">
                            <span class="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-blue-400/15 text-blue-100">
                                <x-lucide-compass class="h-5 w-5" aria-hidden="true" />
                            </span>
                            <div>
                                <h2 class="text-lg font-extrabold text-white">{{ $localized['support'] }}</h2>
                                <p class="mt-2 text-sm leading-7 text-slate-300">{{ $localized['support_text'] }}</p>
                            </div>
                        </div>
                    </div>

                    <nav class="mt-4 grid gap-3" aria-label="Error recovery navigation">
                        <a href="{{ $tourUrl }}" class="group flex items-center justify-between gap-4 rounded-xl border border-white/10 bg-white/8 px-4 py-3 text-sm font-bold text-white transition-colors hover:bg-white/15 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-300">
                            <span class="inline-flex items-center gap-3">
                                <x-lucide-map class="h-4 w-4 text-blue-200" aria-hidden="true" />
                                {{ $localized['tour'] }}
                            </span>
                            <x-lucide-arrow-right class="h-4 w-4 text-slate-400 transition-transform group-hover:translate-x-1" aria-hidden="true" />
                        </a>

                        <a href="{{ $blogUrl }}" class="group flex items-center justify-between gap-4 rounded-xl border border-white/10 bg-white/8 px-4 py-3 text-sm font-bold text-white transition-colors hover:bg-white/15 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-300">
                            <span class="inline-flex items-center gap-3">
                                <x-lucide-file-text class="h-4 w-4 text-emerald-200" aria-hidden="true" />
                                {{ $localized['blog'] }}
                            </span>
                            <x-lucide-arrow-right class="h-4 w-4 text-slate-400 transition-transform group-hover:translate-x-1" aria-hidden="true" />
                        </a>

                        <a href="{{ $contactUrl }}" class="group flex items-center justify-between gap-4 rounded-xl border border-white/10 bg-white/8 px-4 py-3 text-sm font-bold text-white transition-colors hover:bg-white/15 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-300">
                            <span class="inline-flex items-center gap-3">
                                <x-lucide-message-circle class="h-4 w-4 text-amber-200" aria-hidden="true" />
                                {{ $localized['contact'] }}
                            </span>
                            <x-lucide-arrow-right class="h-4 w-4 text-slate-400 transition-transform group-hover:translate-x-1" aria-hidden="true" />
                        </a>
                    </nav>
                </aside>
            </div>
        </section>
    </main>
</body>
</html>
