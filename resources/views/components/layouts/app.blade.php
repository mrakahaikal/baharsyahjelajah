@props([
    'title' => null,
    'metaDescription' => null,
    'schemaJson' => null,
    'themeClass' => '',
    'overlapHeader' => false,
])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'Baharsyah Jelajah') }}</title>
    @if($metaDescription)
        <meta name="description" content="{{ $metaDescription }}">
    @endif
    @if($schemaJson)
        <script type="application/ld+json">{!! $schemaJson !!}</script>
    @endif

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Dancing+Script:wght@600;700&display=swap" rel="stylesheet">

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-white text-slate-700 font-sans antialiased {{ $themeClass }}">
    <div class="min-h-screen flex flex-col">
        <x-shared::header />

        <main id="main-content" class="flex-grow {{ $overlapHeader ? '' : '[&>*:first-child]:pt-28 lg:[&>*:first-child]:pt-32' }}">
            {{ $slot }}
        </main>

        <x-shared.footer />
    </div>

    <!-- Floating WhatsApp Button -->
    @php $waNumber = app(\App\Settings\GeneralSettings::class)->whatsapp_number; @endphp
    @if($waNumber)
        <a href="https://wa.me/{{ $waNumber }}" target="_blank" rel="noopener"
           class="group fixed bottom-4 right-4 z-50 flex h-14 w-14 items-center justify-center rounded-full bg-[#25D366] text-white shadow-xl shadow-emerald-900/20 transition-[background-color,transform] duration-200 hover:scale-105 hover:bg-[#1EBE5D] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 sm:bottom-6 sm:right-6">
            <x-lucide-message-circle class="h-6 w-6" aria-hidden="true" />
            <span class="sr-only">Chat WhatsApp</span>
            <span class="pointer-events-none absolute right-full mr-3 hidden whitespace-nowrap rounded-full bg-slate-950 px-3 py-2 text-xs font-semibold text-white opacity-0 shadow-lg transition-opacity group-hover:opacity-100 sm:block">
                Hubungi via WhatsApp
            </span>
        </a>
    @endif

    @livewireScripts
</body>
</html>
