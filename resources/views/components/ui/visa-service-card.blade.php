@props(['service', 'locale' => app()->getLocale()])

@php
    $detailUrl = route('visa.show', ['locale' => $locale, 'visaService' => $service->slug]);
    $processing = match (true) {
        $service->processing_days_min && $service->processing_days_max => __('visa.card.processing_range', ['min' => $service->processing_days_min, 'max' => $service->processing_days_max]),
        (bool) $service->processing_days_min => __('visa.card.processing_from', ['days' => $service->processing_days_min]),
        (bool) $service->processing_days_max => __('visa.card.processing_until', ['days' => $service->processing_days_max]),
        default => __('visa.not_available'),
    };
@endphp

<article {{ $attributes->class('group grid min-w-0 overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm sm:grid-cols-[12rem_minmax(0,1fr)]') }}>
    <a href="{{ $detailUrl }}" class="relative block min-h-48 overflow-hidden bg-emerald-950 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700 sm:min-h-full" tabindex="-1" aria-hidden="true">
        <x-ui.resilient-image :src="$service->cover_url" :alt="$service->name" width="640" height="760" class="absolute inset-0 h-full w-full" image-class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105" />
        <span class="absolute inset-0 bg-linear-to-t from-emerald-950/80 via-transparent to-transparent"></span>
        <span class="absolute bottom-3 left-3 right-3 inline-flex items-center gap-2 text-xs font-bold text-white">
            @if($service->country->flag_url)
                <img src="{{ $service->country->flag_url }}" alt="" width="24" height="16" class="h-4 w-6 rounded-sm object-cover" aria-hidden="true">
            @else
                <x-lucide-flag class="h-4 w-4 text-lime-300" aria-hidden="true" />
            @endif
            {{ $service->country->name }}
        </span>
    </a>

    <div class="flex min-w-0 flex-col p-5">
        <p class="text-xs font-bold uppercase text-emerald-700">{{ $service->visa_type }}</p>
        <h3 class="mt-2 text-xl font-extrabold leading-snug text-slate-950">
            <a href="{{ $detailUrl }}" class="transition-colors hover:text-emerald-800 focus-visible:rounded-sm focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-emerald-700">{{ $service->name }}</a>
        </h3>
        @if($service->summary)
            <p class="mt-3 line-clamp-2 text-sm leading-6 text-slate-600">{{ $service->summary }}</p>
        @endif

        <dl class="mt-5 grid gap-3 border-y border-slate-100 py-4 text-sm sm:grid-cols-2">
            <div>
                <dt class="text-xs font-semibold text-slate-400">{{ __('visa.card.processing') }}</dt>
                <dd class="mt-1 font-bold text-slate-800">{{ $processing }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold text-slate-400">{{ __('visa.card.price') }}</dt>
                <dd class="mt-1 font-extrabold text-emerald-800">{{ $service->formatted_price ?? __('visa.price_on_request') }}</dd>
            </div>
        </dl>

        <x-ui::button tag="a" href="{{ $detailUrl }}" class="mt-5 w-full sm:w-fit hover:bg-emerald-800">
            {{ __('visa.card.details') }}
            <x-slot:trailingIcon><x-lucide-arrow-right /></x-slot:trailingIcon>
        </x-ui::button>
    </div>
</article>
