@props([
    'tour',
    'locale' => app()->getLocale(),
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
    $packageCount = $tour->packages_count ?? $tour->packages->count();
    $visiblePackages = $tour->packages->take(2);
    $remainingPackagesCount = max(0, $packageCount - $visiblePackages->count());
@endphp

<article {{ $attributes->class('group grid overflow-hidden rounded-lg border border-slate-200/80 bg-white shadow-sm transition-[border-color,box-shadow] duration-200 hover:border-blue-200 hover:shadow-md sm:grid-cols-[15rem_minmax(0,1fr)] xl:grid-cols-[17rem_minmax(0,1fr)_12rem]') }}>
    <div class="relative aspect-[16/9] overflow-hidden bg-slate-100 sm:row-span-2 sm:aspect-auto sm:min-h-64 xl:row-span-1">
        @if($previewPackage?->cover_url)
            <img src="{{ $previewPackage->cover_url }}" alt="{{ $tour->name }}" width="640" height="720" loading="lazy" class="absolute inset-0 h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
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

    <div class="flex min-w-0 flex-col p-4 sm:p-6">
        <div class="flex flex-wrap items-center gap-2 text-[11px] font-bold uppercase tracking-wider text-slate-500">
            @if($tour->category)
                <span class="text-blue-600">{{ $tour->category->name }}</span>
                <span class="text-slate-300" aria-hidden="true">/</span>
            @endif
            <span>{{ __('frontend.tour.catalog_card.types.'.$tour->tour_type->value) }}</span>
        </div>

        <h3 class="mt-3 text-xl font-extrabold text-slate-900 transition-colors group-hover:text-blue-600 sm:text-2xl">
            <a href="{{ route('tour.show', ['locale' => $locale, 'tour' => $tour->slug]) }}" class="rounded-md focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-blue-600">
                {{ $tour->name }}
            </a>
        </h3>

        @if($description)
            <p class="mt-3 line-clamp-2 text-sm leading-6 text-slate-500 sm:line-clamp-3">
                {{ \Illuminate\Support\Str::limit($description, 190) }}
            </p>
        @endif

        <dl class="mt-5 grid grid-cols-2 gap-3 border-t border-slate-100 pt-5">
            <div>
                <dt class="flex items-center gap-1.5 text-xs font-semibold text-slate-400">
                    <x-lucide-layers-3 class="h-3.5 w-3.5" aria-hidden="true" />
                    {{ __('frontend.tour.catalog_card.package_options') }}
                </dt>
                <dd class="mt-1 text-sm font-bold text-slate-800">
                    {{ trans_choice('frontend.tour.catalog_card.package_count', $packageCount, ['count' => $packageCount]) }}
                </dd>
            </div>

            <div>
                <dt class="flex items-center gap-1.5 text-xs font-semibold text-slate-400">
                    <x-lucide-clock-3 class="h-3.5 w-3.5" aria-hidden="true" />
                    {{ __('frontend.tour.catalog_card.duration_options') }}
                </dt>
                <dd class="mt-1 text-sm font-bold text-slate-800">
                    {{ $durations->isNotEmpty() ? $durations->take(2)->implode(' / ') : __('frontend.tour.catalog_card.on_request') }}
                </dd>
            </div>
        </dl>

        @if($visiblePackages->isNotEmpty())
            <div class="mt-5 hidden flex-wrap items-center gap-2 sm:flex" aria-label="{{ __('frontend.tour.catalog_card.available_packages') }}">
                @foreach($visiblePackages as $package)
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ $package->name }}</span>
                @endforeach
                @if($remainingPackagesCount > 0)
                    <span class="text-xs font-bold text-blue-600">{{ __('frontend.tour.catalog_card.more_packages', ['count' => $remainingPackagesCount]) }}</span>
                @endif
            </div>
        @endif
    </div>

    <div class="flex flex-col justify-between gap-4 border-t border-slate-100 bg-slate-50/70 p-4 sm:col-start-2 sm:p-6 xl:col-start-auto xl:border-l xl:border-t-0">
        <div>
            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">
                {{ __('frontend.tour.catalog_card.starting_price') }}
            </p>
            @if($startingPrice)
                <p class="mt-2 text-xl font-extrabold text-blue-600">{{ $startingPrice->formatted_price }}</p>
                <p class="mt-1 text-xs leading-5 text-slate-500">{{ __('frontend.tour.catalog_card.price_note') }}</p>
            @else
                <p class="mt-2 text-sm font-bold text-slate-800">{{ __('frontend.tour.catalog_card.contact_for_price') }}</p>
            @endif
        </div>

        <a href="{{ route('tour.show', ['locale' => $locale, 'tour' => $tour->slug]) }}" class="inline-flex w-full items-center justify-center gap-2 rounded-full bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-blue-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
            {{ __('frontend.tour.catalog_card.view_tour') }}
            <x-lucide-arrow-right class="h-4 w-4" aria-hidden="true" />
        </a>
    </div>
</article>
