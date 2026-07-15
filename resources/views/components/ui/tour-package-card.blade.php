@props(['package', 'tour', 'locale' => app()->getLocale()])

@php
    $startingPrice = $package->startingPriceTier();
    $includedItems = $package->includes->where('type', 'include')->values();
    $visibleIncludedItems = $includedItems->take(3);
    $remainingIncludedCount = $includedItems->count() - $visibleIncludedItems->count();
    $maximumHotelStars = $package->tiers->max('hotel_stars');
    $packageUrl = route('tour.package.show', [
        'locale' => $locale,
        'tour' => $tour->slug,
        'package' => $package->slug,
    ]);
@endphp

<article {{ $attributes->merge(['class' => 'overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm shadow-slate-900/5 transition-shadow hover:shadow-md']) }}>
    <div class="grid md:grid-cols-[17rem_minmax(0,1fr)]">
        <a href="{{ $packageUrl }}" class="group relative block aspect-[16/10] overflow-hidden bg-slate-100 focus-visible:outline-2 focus-visible:outline-offset-[-2px] focus-visible:outline-blue-600 md:aspect-auto md:min-h-80" aria-label="{{ __('frontend.tour.show.card.view_details') }}: {{ $package->name }}">
            <img src="{{ $package->cover_url }}" alt="{{ $package->name }}" width="680" height="680" loading="lazy" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-[1.03]">
            <span class="absolute left-4 top-4 inline-flex items-center gap-1.5 rounded-full bg-white/95 px-3 py-1.5 text-xs font-semibold text-slate-800 shadow-sm">
                <x-lucide-clock-3 class="h-3.5 w-3.5 text-blue-600" aria-hidden="true" />
                {{ $package->duration_label }}
            </span>
        </a>

        <div class="grid min-w-0 xl:grid-cols-[minmax(0,1fr)_14rem]">
            <div class="min-w-0 p-5 sm:p-6 lg:p-7">
                <p class="text-xs font-semibold uppercase text-blue-600">{{ __('frontend.tour.show.packages.eyebrow') }}</p>
                <h3 class="mt-2 text-xl font-extrabold text-slate-900 sm:text-2xl">
                    <a href="{{ $packageUrl }}" class="rounded-sm hover:text-blue-600 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-blue-600">
                        {{ $package->name }}
                    </a>
                </h3>

                <div class="mt-5 grid gap-3 text-sm text-slate-600 sm:grid-cols-2">
                    <div class="flex items-center gap-2.5">
                        <x-lucide-calendar-days class="h-4 w-4 shrink-0 text-blue-600" aria-hidden="true" />
                        <span>{{ trans_choice('frontend.tour.show.card.itinerary_count', $package->itineraries->count(), ['count' => $package->itineraries->count()]) }}</span>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <x-lucide-layers-3 class="h-4 w-4 shrink-0 text-blue-600" aria-hidden="true" />
                        <span>{{ trans_choice('frontend.tour.show.card.tier_count', $package->tiers->count(), ['count' => $package->tiers->count()]) }}</span>
                    </div>
                    @if($maximumHotelStars)
                        <div class="flex items-center gap-2.5 sm:col-span-2">
                            <x-lucide-bed-double class="h-4 w-4 shrink-0 text-blue-600" aria-hidden="true" />
                            <span>{{ __('frontend.tour.show.card.accommodation', ['stars' => $maximumHotelStars]) }}</span>
                        </div>
                    @endif
                </div>

                @if($visibleIncludedItems->isNotEmpty())
                    <div class="mt-6 border-t border-slate-100 pt-5">
                        <p class="text-xs font-semibold uppercase text-slate-400">{{ __('frontend.tour.show.card.included') }}</p>
                        <ul class="mt-3 grid gap-2 text-sm text-slate-700 sm:grid-cols-2">
                            @foreach($visibleIncludedItems as $item)
                                <li class="flex min-w-0 items-start gap-2">
                                    <x-lucide-circle-check class="mt-0.5 h-4 w-4 shrink-0 text-emerald-600" aria-hidden="true" />
                                    <span>{{ $item->item }}</span>
                                </li>
                            @endforeach
                            @if($remainingIncludedCount > 0)
                                <li class="text-xs font-semibold text-blue-600 sm:col-span-2">
                                    {{ __('frontend.tour.show.card.more_included', ['count' => $remainingIncludedCount]) }}
                                </li>
                            @endif
                        </ul>
                    </div>
                @endif
            </div>

            <div class="flex flex-col justify-between border-t border-slate-100 bg-slate-50/70 p-5 sm:p-6 xl:border-l xl:border-t-0">
                <div>
                    @if($startingPrice)
                        <p class="text-xs font-semibold uppercase text-slate-400">{{ __('frontend.tour.show.card.starting_price') }}</p>
                        <p class="mt-2 text-2xl font-extrabold text-blue-600">{{ $startingPrice->formatted_price }}</p>
                        <p class="mt-1 text-xs leading-5 text-slate-500">{{ __('frontend.tour.show.card.price_note') }}</p>
                    @else
                        <p class="text-sm font-semibold leading-6 text-slate-700">{{ __('frontend.tour.show.card.contact_price') }}</p>
                    @endif
                </div>

                <x-ui::button tag="a" href="{{ $packageUrl }}" class="mt-5 w-full hover:bg-blue-600">
                    {{ __('frontend.tour.show.card.view_details') }}
                    <x-slot:trailingIcon><x-lucide-arrow-right /></x-slot:trailingIcon>
                </x-ui::button>
            </div>
        </div>
    </div>
</article>
