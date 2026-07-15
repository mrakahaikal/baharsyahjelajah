@props(['destination', 'locale' => app()->getLocale()])

@php
    $url = route('destination.show', ['locale' => $locale, 'destination' => $destination]);
    $hasTours = (bool) ($destination->has_direct_tour_packages || $destination->has_itinerary_tour_packages);
@endphp

<article {{ $attributes->class('group flex h-full flex-col overflow-hidden rounded-lg border border-slate-200 bg-white transition duration-300 hover:-translate-y-1 hover:border-blue-200 hover:shadow-xl hover:shadow-slate-950/8') }}>
    <a href="{{ $url }}" class="relative block aspect-[4/3] overflow-hidden bg-slate-100 focus-visible:outline-2 focus-visible:outline-offset-[-3px] focus-visible:outline-blue-600" aria-label="{{ $destination->name }}">
        @if($destination->cover_url)
            <img src="{{ $destination->cover_url }}" alt="{{ $destination->name }}" width="720" height="540" loading="lazy" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
        @else
            <span class="absolute inset-0 grid place-items-center text-slate-300">
                <x-lucide-map-pinned class="h-14 w-14" aria-hidden="true" />
            </span>
        @endif
        @if($destination->is_featured)
            <span class="absolute top-3 left-3 inline-flex items-center gap-1.5 rounded-full bg-amber-300 px-3 py-1 text-xs font-bold text-slate-950 shadow-sm">
                <x-lucide-star class="h-3.5 w-3.5" aria-hidden="true" />
                {{ __('destination.card.featured') }}
            </span>
        @endif
    </a>

    <div class="flex flex-1 flex-col p-5 sm:p-6">
        @if($destination->location)
            <p class="flex items-center gap-2 text-xs font-semibold text-slate-500">
                <x-lucide-map-pin class="h-4 w-4 shrink-0 text-blue-600" aria-hidden="true" />
                <span class="truncate">{{ $destination->location }}</span>
            </p>
        @endif
        <h2 class="mt-3 text-xl font-extrabold leading-7 text-slate-950">
            <a href="{{ $url }}" class="rounded-sm transition-colors hover:text-blue-600 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-blue-600">
                {{ $destination->name }}
            </a>
        </h2>
        @if(filled($destination->description))
            <p class="mt-3 line-clamp-3 text-sm leading-6 text-slate-600">
                {{ str(strip_tags((string) $destination->description))->limit(145) }}
            </p>
        @endif

        <div class="mt-5 flex flex-wrap gap-2" aria-label="{{ __('destination.card.available_content') }}">
            @if($hasTours)
                <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-2.5 py-1 text-xs font-bold text-blue-700">
                    <x-lucide-route class="h-3.5 w-3.5" aria-hidden="true" />
                    {{ __('destination.card.tours') }}
                </span>
            @endif
            @if($destination->has_umrah_packages)
                <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-800">
                    <x-lucide-moon-star class="h-3.5 w-3.5" aria-hidden="true" />
                    {{ __('destination.card.umrah') }}
                </span>
            @endif
            @if($destination->has_posts)
                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700">
                    <x-lucide-newspaper class="h-3.5 w-3.5" aria-hidden="true" />
                    {{ __('destination.card.articles') }}
                </span>
            @endif
        </div>

        <span class="mt-auto inline-flex items-center gap-2 pt-6 text-sm font-bold text-blue-600">
            {{ __('destination.card.view') }}
            <x-lucide-arrow-right class="h-4 w-4 transition-transform group-hover:translate-x-1" aria-hidden="true" />
        </span>
    </div>
</article>
