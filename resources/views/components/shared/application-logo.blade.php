@props(['inverted' => false])

@php
    $isUmrah = request()->routeIs('umroh.*');
@endphp

<img
    src="{{ asset($isUmrah ? 'images/logo-baharsyah-jelajah-umrah.webp' : 'images/logo-baharsyah-jelajah.webp') }}"
    alt="{{ $isUmrah ? 'Baharsyah Jelajah Umrah' : 'Baharsyah Jelajah' }}"
    width="176"
    height="44"
    {{ $attributes->class([
        'h-10 w-auto object-contain lg:h-11',
        'brightness-0 invert' => $inverted,
    ]) }}
>
