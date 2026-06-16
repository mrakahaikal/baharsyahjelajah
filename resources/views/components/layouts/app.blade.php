<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'Baharsyah Jelajah') }}</title>

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-bg-main text-text-main font-sans antialiased {{ $themeClass ?? '' }}"
      x-data
      x-init="
        const obs = new IntersectionObserver(
          (entries) => entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('is-visible'); obs.unobserve(e.target); } }),
          { threshold: 0.12, rootMargin: '0px 0px -40px 0px' }
        );
        document.querySelectorAll('.reveal-on-scroll, .reveal-fade').forEach(el => obs.observe(el));
      ">
    <div class="min-h-screen flex flex-col">
        <x-shared.header />

        <main class="flex-grow">
            {{ $slot }}
        </main>

        <x-shared.footer />
    </div>

    <!-- Floating WhatsApp Button -->
    @php $waNumber = app(\App\Settings\GeneralSettings::class)->whatsapp_number; @endphp
    @if($waNumber)
        <a href="https://wa.me/{{ $waNumber }}" target="_blank" rel="noopener"
           class="fixed bottom-6 right-6 z-50 w-14 h-14 bg-[#25D366] hover:bg-[#1EBE5D] text-white rounded-full shadow-xl flex items-center justify-center transition-all duration-200 hover:scale-110 group">
            <x-lucide-message-circle class="w-6 h-6" />
            <span class="sr-only">Chat WhatsApp</span>
            <span class="absolute right-full mr-3 bg-gray-900 text-white text-xs font-medium px-2.5 py-1.5 rounded-lg whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none shadow-lg">
                Hubungi via WhatsApp
            </span>
        </a>
    @endif

    @livewireScripts
    @stack('scripts')
</body>
</html>
