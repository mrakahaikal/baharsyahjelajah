@props([
    'title' => null,
    'metaDescription' => null,
    'schemaJson' => null,
    'canonicalUrl' => null,
    'alternateUrls' => [],
    'robots' => null,
    'ogType' => null,
    'ogImage' => null,
    'showFloatingWhatsapp' => true,
    'themeClass' => '',
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
    @if($robots)
        <meta name="robots" content="{{ $robots }}">
    @endif
    <link rel="canonical" href="{{ $canonicalUrl ?? url()->current() }}">
    @foreach($alternateUrls as $alternateLocale => $alternateUrl)
        <link rel="alternate" hreflang="{{ $alternateLocale }}" href="{{ $alternateUrl }}">
    @endforeach
    @if($alternateUrls)
        <link rel="alternate" hreflang="x-default" href="{{ $alternateUrls['id'] ?? $canonicalUrl }}">
    @endif
    @if($schemaJson)
        <script type="application/ld+json">{!! $schemaJson !!}</script>
    @endif
    @if($ogType)
        <meta property="og:type" content="{{ $ogType }}">
        <meta property="og:locale" content="{{ str_replace('-', '_', app()->getLocale()) }}">
        <meta property="og:title" content="{{ $title ?? config('app.name') }}">
        @if($metaDescription)
            <meta property="og:description" content="{{ $metaDescription }}">
        @endif
        <meta property="og:url" content="{{ $canonicalUrl ?? url()->current() }}">
        @if($ogImage)
            <meta property="og:image" content="{{ $ogImage }}">
        @endif
        <meta name="twitter:card" content="{{ $ogImage ? 'summary_large_image' : 'summary' }}">
        <meta name="twitter:title" content="{{ $title ?? config('app.name') }}">
        @if($metaDescription)
            <meta name="twitter:description" content="{{ $metaDescription }}">
        @endif
        @if($ogImage)
            <meta name="twitter:image" content="{{ $ogImage }}">
        @endif
    @endif

    @if(request()->routeIs('umroh.*'))
        <link rel="icon" href="{{ asset('images/favicon-umrah.png') }}">
    @else
        <link rel="icon" href="{{ asset('images/favicon.png') }}">
    @endif

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://images.unsplash.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-white text-slate-700 font-sans antialiased {{ $themeClass }}">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-60 focus:rounded-md focus:bg-slate-950 focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
        {{ __('frontend.header.skip-to-content') }}
    </a>

    <div class="min-h-screen flex flex-col">
        <x-shared-header :locale-urls="$alternateUrls" />

        <main id="main-content" class="grow">
            {{ $slot }}
        </main>

        <x-shared.footer />
    </div>

    <!-- Floating WhatsApp Button -->
    @php $waNumber = app(\App\Settings\GeneralSettings::class)->whatsapp_number; @endphp
    @if($showFloatingWhatsapp && $waNumber)
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
