<title>{{ $metadata->title }}</title>

@if($metadata->description)
    <meta name="description" content="{{ $metadata->description }}">
@endif
<meta name="robots" content="{{ $robots ?? 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1' }}">
<meta name="author" content="{{ $metadata->siteName ?? config('app.name') }}">
<meta name="theme-color" content="#2563eb">

<link rel="canonical" href="{{ $metadata->canonicalUrl }}">
@foreach($alternateUrls as $alternateLocale => $alternateUrl)
    <link rel="alternate" hreflang="{{ $alternateLocale }}" href="{{ $alternateUrl }}">
@endforeach
@if($alternateUrls)
    <link rel="alternate" hreflang="x-default" href="{{ $alternateUrls['id'] ?? $metadata->canonicalUrl }}">
@endif

<!-- Open Graph / Facebook / WhatsApp / LinkedIn -->
<meta property="og:type" content="{{ $metadata->ogType }}">
<meta property="og:site_name" content="{{ $metadata->siteName ?? config('app.name') }}">
<meta property="og:locale" content="{{ $metadata->ogLocale }}">
@foreach($metadata->ogLocaleAlternates as $altLocale)
    <meta property="og:locale:alternate" content="{{ $altLocale }}">
@endforeach
<meta property="og:title" content="{{ $metadata->ogTitle }}">
@if($metadata->ogDescription)
    <meta property="og:description" content="{{ $metadata->ogDescription }}">
@endif
<meta property="og:url" content="{{ $metadata->canonicalUrl }}">
@if($metadata->ogImage)
    <meta property="og:image" content="{{ $metadata->ogImage }}">
    @if(str_starts_with($metadata->ogImage, 'https://'))
        <meta property="og:image:secure_url" content="{{ $metadata->ogImage }}">
    @endif
    <meta property="og:image:width" content="{{ $metadata->ogImageWidth }}">
    <meta property="og:image:height" content="{{ $metadata->ogImageHeight }}">
    <meta property="og:image:type" content="{{ $metadata->ogImageType }}">
    <meta property="og:image:alt" content="{{ $metadata->ogTitle }}">
@endif

<!-- Twitter -->
<meta name="twitter:card" content="{{ $metadata->ogImage ? 'summary_large_image' : 'summary' }}">
<meta name="twitter:title" content="{{ $metadata->ogTitle }}">
@if($metadata->ogDescription)
    <meta name="twitter:description" content="{{ $metadata->ogDescription }}">
@endif
@if($metadata->ogImage)
    <meta name="twitter:image" content="{{ $metadata->ogImage }}">
    <meta name="twitter:image:alt" content="{{ $metadata->ogTitle }}">
@endif
