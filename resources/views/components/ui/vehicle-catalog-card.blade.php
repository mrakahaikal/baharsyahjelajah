@props(['vehicle', 'locale' => app()->getLocale(), 'area' => null])

@php
    $slug = $vehicle->getTranslation('slug', $locale, false) ?: $vehicle->getTranslation('slug', 'id', false) ?: $vehicle->getKey();
    $rate = $vehicle->relationLoaded('rentalRates') ? $vehicle->rentalRates->first() : null;
    $areaSlug = $area?->slug;
    $showUrl = route('transport.show', array_filter(['locale' => $locale, 'vehicle' => $slug, 'area' => $areaSlug]));
    $bookingUrl = route('transport.booking', array_filter(['locale' => $locale, 'vehicle' => $slug, 'area' => $areaSlug]));
    $description = trim(strip_tags((string) $vehicle->description));
    $price = $area ? $rate?->formatted_price : $vehicle->formatted_starting_price;
    $facilities = collect(['AC' => $vehicle->has_ac, 'Wi-Fi' => $vehicle->has_wifi])
        ->filter()
        ->keys()
        ->implode(' · ');
@endphp

<article {{ $attributes->class('group grid overflow-hidden rounded-lg border border-slate-200/80 bg-white shadow-sm transition-[border-color,box-shadow] duration-200 hover:border-blue-200 hover:shadow-md lg:grid-cols-[15rem_minmax(0,1fr)_13rem]') }}>
    <a href="{{ $showUrl }}" class="relative block aspect-16/10 overflow-hidden bg-slate-100 lg:aspect-auto lg:min-h-64" aria-label="{{ __('transport.card.details') }}: {{ $vehicle->name }}">
        <img src="{{ $vehicle->thumbnail_url }}" alt="{{ $vehicle->name }}" width="640" height="520" loading="lazy" class="absolute inset-0 h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
        @if($vehicle->is_featured)
            <span class="absolute left-4 top-4 inline-flex items-center gap-1.5 rounded-md bg-white/95 px-2.5 py-1 text-[11px] font-bold text-slate-900 shadow-sm backdrop-blur">
                <x-lucide-sparkles class="h-3.5 w-3.5 text-amber-500" aria-hidden="true" />{{ __('transport.card.featured') }}
            </span>
        @endif
    </a>

    <div class="flex min-w-0 flex-col p-5 lg:p-6">
        <p class="text-xs font-bold uppercase text-blue-600">
            {{ $vehicle->category ? __('transport.category.'.$vehicle->category->value) : __('transport.index.all_categories') }}@if($area) · {{ $area->name }}@endif
        </p>
        <h2 class="mt-2 text-xl font-extrabold text-slate-950 transition-colors group-hover:text-blue-700 sm:text-2xl">
            <a href="{{ $showUrl }}" class="rounded-md focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-blue-600">{{ $vehicle->name }}</a>
        </h2>

        @if($description)
            <p class="mt-3 line-clamp-2 text-sm leading-6 text-slate-500 lg:line-clamp-3">{{ \Illuminate\Support\Str::limit($description, 190) }}</p>
        @endif

        <dl class="mt-5 grid grid-cols-2 gap-x-5 gap-y-4 border-t border-slate-100 pt-5 text-sm sm:grid-cols-4 lg:grid-cols-2 xl:grid-cols-4">
            <div>
                <dt class="flex items-center gap-1.5 text-xs font-semibold text-slate-400"><x-lucide-users class="h-3.5 w-3.5" aria-hidden="true" />{{ __('transport.show.capacity') }}</dt>
                <dd class="mt-1 font-bold text-slate-800">{{ $vehicle->capacity_display }}</dd>
            </div>
            <div>
                <dt class="flex items-center gap-1.5 text-xs font-semibold text-slate-400"><x-lucide-gauge class="h-3.5 w-3.5" aria-hidden="true" />{{ __('transport.index.transmission') }}</dt>
                <dd class="mt-1 font-bold text-slate-800">{{ __('transport.transmission.'.$vehicle->transmission) }}</dd>
            </div>
            <div>
                <dt class="flex items-center gap-1.5 text-xs font-semibold text-slate-400"><x-lucide-wind class="h-3.5 w-3.5" aria-hidden="true" />{{ __('transport.show.facilities') }}</dt>
                <dd class="mt-1 font-bold text-slate-800">{{ $facilities ?: '-' }}</dd>
            </div>
            @if($area)
                <div>
                    <dt class="flex items-center gap-1.5 text-xs font-semibold text-slate-400"><x-lucide-calendar-days class="h-3.5 w-3.5" aria-hidden="true" />{{ __('transport.show.minimum') }}</dt>
                    <dd class="mt-1 font-bold text-slate-800">{{ trans_choice('transport.index.minimum_days', $area->minimum_rental_days, ['count' => $area->minimum_rental_days]) }}</dd>
                </div>
            @endif
        </dl>
    </div>

    <div class="flex flex-col justify-between gap-5 border-t border-slate-100 bg-slate-50/70 p-5 lg:border-l lg:border-t-0 lg:p-6">
        <div>
            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ $area ? __('transport.card.daily') : __('transport.card.starting_price') }}</p>
            <p class="mt-2 text-xl font-extrabold text-blue-600">{{ $price ?? __('transport.booking.on_request') }}</p>
            @if(! $area && $vehicle->available_area_count)
                <p class="mt-2 text-xs leading-5 text-slate-500">{{ trans_choice('transport.card.available_areas', $vehicle->available_area_count, ['count' => $vehicle->available_area_count]) }}</p>
            @endif
        </div>

        <div class="grid gap-2">
            <x-ui::button tag="a" href="{{ $showUrl }}" variant="outline" class="w-full hover:border-blue-600 hover:text-blue-600">{{ __('transport.card.details') }}</x-ui::button>
            <x-ui::button tag="a" href="{{ $bookingUrl }}" class="w-full hover:bg-blue-600">{{ __('transport.card.book') }}<x-slot:trailingIcon><x-lucide-arrow-right /></x-slot:trailingIcon></x-ui::button>
        </div>
    </div>
</article>
