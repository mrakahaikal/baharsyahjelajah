@props([
    'tour',
    'locale' => app()->getLocale(),
    'imageHeight' => 'h-56',
])

@php
    $previewPackage = $tour->packages->first();
    $startingPrice = $tour->packages
        ->map(fn ($package) => $package->startingPriceTier())
        ->filter()
        ->sortBy(fn ($priceTier) => (float) $priceTier->price)
        ->first();
    $durations = $tour->packages
        ->map(fn ($package) => $package->duration_label)
        ->unique()
        ->values();
    $description = trim(strip_tags((string) ($tour->short_description ?: $tour->description)));
    $tourSlug = $tour->localizedSlug($locale);
@endphp

<article class="group flex h-full flex-col overflow-hidden rounded-lg border border-slate-200/80 bg-white shadow-sm transition-[border-color,box-shadow,transform] duration-200 hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-md">
    <div class="relative {{ $imageHeight }} overflow-hidden bg-slate-100">
        @if($previewPackage?->cover_url)
            <img src="{{ $previewPackage->cover_url }}" alt="{{ $tour->name }}" width="640" height="448" loading="lazy" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
        @else
            <div class="grid h-full place-items-center text-slate-300">
                <x-lucide-map class="h-10 w-10" aria-hidden="true" />
            </div>
        @endif

        @if($tour->is_featured)
            <span class="absolute left-4 top-4 rounded-full bg-slate-900/90 px-3 py-1 text-xs font-bold text-white">
                {{ __('frontend.tour.featured') }}
            </span>
        @endif
    </div>

    <div class="flex flex-1 flex-col p-5">
        <div class="flex flex-wrap items-center gap-2 text-[11px] font-semibold uppercase text-slate-500">
            @if($tour->category)
                <span>{{ $tour->category->name }}</span>
                <span aria-hidden="true">/</span>
            @endif
            <span>{{ $tour->tour_type->getLabel() }}</span>
        </div>

        <h3 class="mt-3 line-clamp-2 text-lg font-bold text-slate-900 transition-colors group-hover:text-blue-600">
            {{ $tour->name }}
        </h3>

        @if($description)
            <p class="mt-2 line-clamp-2 text-sm leading-6 text-slate-500">
                {{ \Illuminate\Support\Str::limit($description, 130) }}
            </p>
        @endif

        <div class="mt-4 flex flex-wrap gap-2 text-xs font-semibold text-slate-600">
            <span class="rounded-full bg-slate-100 px-3 py-1">
                {{ $tour->packages_count ?? $tour->packages->count() }} {{ $locale === 'en' ? 'package options' : ($locale === 'ms' ? 'pilihan pakej' : 'pilihan paket') }}
            </span>
            @if($durations->isNotEmpty())
                <span class="rounded-full bg-slate-100 px-3 py-1">{{ $durations->take(2)->implode(' / ') }}</span>
            @endif
        </div>

        @if($startingPrice)
            <div class="mt-5">
                <span class="text-[10px] font-bold uppercase text-slate-400">{{ __('frontend.featured_tour.labels.start_from') }}</span>
                <p class="mt-1 text-xl font-extrabold text-blue-600">{{ $startingPrice->formatted_price }}</p>
            </div>
        @endif

        <x-ui::button tag="a" href="{{ route('tour.show', ['locale' => $locale, 'tour' => $tourSlug]) }}" variant="outline" class="mt-auto w-full {{ $startingPrice ? 'mt-5' : 'mt-6' }}">
            {{ $locale === 'en' ? 'View package options' : ($locale === 'ms' ? 'Lihat pilihan pakej' : 'Lihat pilihan paket') }}
            <x-slot:trailingIcon><x-lucide-arrow-right /></x-slot:trailingIcon>
        </x-ui::button>
    </div>
</article>
