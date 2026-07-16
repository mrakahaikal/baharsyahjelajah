<title>{{ $metadata->title }}</title>

@if($metadata->description)
    <meta name="description" content="{{ $metadata->description }}">
@endif
@if($robots)
    <meta name="robots" content="{{ $robots }}">
@endif

<link rel="canonical" href="{{ $metadata->canonicalUrl }}">
@foreach($alternateUrls as $alternateLocale => $alternateUrl)
    <link rel="alternate" hreflang="{{ $alternateLocale }}" href="{{ $alternateUrl }}">
@endforeach
@if($alternateUrls)
    <link rel="alternate" hreflang="x-default" href="{{ $alternateUrls['id'] ?? $metadata->canonicalUrl }}">
@endif

<meta property="og:type" content="{{ $metadata->ogType }}">
<meta property="og:site_name" content="{{ config('app.name') }}">
<meta property="og:locale" content="{{ str_replace('-', '_', $metadata->locale) }}">
<meta property="og:title" content="{{ $metadata->ogTitle }}">
@if($metadata->ogDescription)
    <meta property="og:description" content="{{ $metadata->ogDescription }}">
@endif
<meta property="og:url" content="{{ $metadata->canonicalUrl }}">
@if($metadata->ogImage)
    <meta property="og:image" content="{{ $metadata->ogImage }}">
@endif

<meta name="twitter:card" content="{{ $metadata->ogImage ? 'summary_large_image' : 'summary' }}">
<meta name="twitter:title" content="{{ $metadata->ogTitle }}">
@if($metadata->ogDescription)
    <meta name="twitter:description" content="{{ $metadata->ogDescription }}">
@endif
@if($metadata->ogImage)
    <meta name="twitter:image" content="{{ $metadata->ogImage }}">
@endif
