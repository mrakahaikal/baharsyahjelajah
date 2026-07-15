@props(['vehicle', 'locale' => app()->getLocale(), 'compact' => false])

@php
    $slug = $vehicle->getTranslation('slug', $locale, false) ?: $vehicle->getTranslation('slug', 'id', false) ?: $vehicle->getKey();
    $showUrl = route('transport.show', ['locale' => $locale, 'vehicle' => $slug]);
@endphp

<article {{ $attributes->merge(['class' => 'group flex h-full flex-col overflow-hidden rounded-lg border border-slate-200 bg-white transition duration-300 hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-xl hover:shadow-slate-900/8']) }}>
    <a href="{{ $showUrl }}" class="relative block overflow-hidden bg-slate-100" aria-label="{{ __('transport.card.details') }}: {{ $vehicle->name }}">
        <img src="{{ $vehicle->thumbnail_url }}" alt="{{ $vehicle->name }}" width="720" height="450" loading="lazy" class="aspect-[16/10] w-full object-cover transition duration-500 group-hover:scale-[1.03]">
        @if($vehicle->is_featured)
            <span class="absolute left-3 top-3 inline-flex items-center gap-1.5 rounded-md bg-white/95 px-2.5 py-1 text-[11px] font-bold text-slate-900 shadow-sm backdrop-blur">
                <x-lucide-sparkles class="h-3.5 w-3.5 text-amber-500" aria-hidden="true" />
                {{ __('transport.card.featured') }}
            </span>
        @endif
    </a>

    <div class="flex flex-1 flex-col p-5">
        <p class="text-xs font-bold uppercase text-blue-600">{{ $vehicle->brand }} · {{ $vehicle->year }}</p>
        <h3 class="mt-2 text-lg font-extrabold text-slate-950 transition-colors group-hover:text-blue-700"><a href="{{ $showUrl }}">{{ $vehicle->name }}</a></h3>

        <div class="mt-4 grid grid-cols-3 divide-x divide-slate-200 border-y border-slate-100 py-3 text-center text-xs text-slate-600">
            <span class="flex flex-col items-center gap-1"><x-lucide-users class="h-4 w-4 text-blue-600" />{{ $vehicle->capacity_pax }}</span>
            <span class="flex flex-col items-center gap-1"><x-lucide-briefcase class="h-4 w-4 text-blue-600" />{{ $vehicle->capacity_luggage }}</span>
            <span class="flex flex-col items-center gap-1"><x-lucide-gauge class="h-4 w-4 text-blue-600" />{{ __('transport.transmission.'.$vehicle->transmission) }}</span>
        </div>

        <div class="mt-4 grid gap-2 text-sm">
            @if($vehicle->formatted_price_per_day)
                <div class="flex items-baseline justify-between gap-3"><span class="text-slate-500">{{ __('transport.card.daily') }}</span><strong class="text-slate-950">{{ $vehicle->formatted_price_per_day }}</strong></div>
            @endif
            @if($vehicle->formatted_price_per_trip)
                <div class="flex items-baseline justify-between gap-3"><span class="text-slate-500">{{ __('transport.card.trip') }}</span><strong class="text-slate-950">{{ $vehicle->formatted_price_per_trip }}</strong></div>
            @endif
        </div>

        <div class="mt-auto flex gap-2 pt-5">
            <x-ui::button tag="a" href="{{ $showUrl }}" variant="outline" class="flex-1 hover:border-blue-600 hover:text-blue-600">{{ __('transport.card.details') }}</x-ui::button>
            @unless($compact)
                <x-ui::button tag="a" href="{{ route('transport.booking', ['locale' => $locale, 'vehicle' => $slug]) }}" class="flex-1 hover:bg-blue-600">{{ __('transport.card.book') }}</x-ui::button>
            @endunless
        </div>
    </div>
</article>
