@php
    $locale = app()->getLocale();
    $slug = $vehicle->getTranslation('slug', $locale, false) ?: $vehicle->getTranslation('slug', 'id', false) ?: $vehicle->getKey();
    $galleryUrls = collect([$vehicle->thumbnail_url])->merge($vehicle->galleryUrls())->unique()->values();
@endphp

<x-layouts::app
    :title="__('transport.seo.show_title', ['vehicle' => $vehicle->name])"
    :meta-description="str($vehicle->description)->stripTags()->limit(155)"
    :$schemaJson
    breadcrumb-name="transport.show"
    :breadcrumb-parameters="[$locale, $vehicle]"
    :$canonicalUrl
    :$alternateUrls>
    <section class="bg-slate-950 text-white">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <x-ui.breadcrumbs name="transport.show" :parameters="[$locale, $vehicle]" variant="dark" class="mb-5" />

            <a href="{{ route('transport.index', ['locale' => $locale]) }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-300 hover:text-white">
                <x-lucide-arrow-left class="h-4 w-4" />{{ __('transport.show.back') }}
            </a>
        </div>

        <div x-data="{ open: false, selected: @js($galleryUrls->first()) }" @keydown.escape.window="open = false" class="mx-auto grid max-w-7xl gap-3 px-4 pb-10 sm:grid-cols-[minmax(0,1.55fr)_minmax(16rem,0.65fr)] sm:px-6 lg:px-8">
            <button type="button" @click="selected = @js($galleryUrls->first()); open = true" class="relative min-h-64 overflow-hidden rounded-lg bg-slate-800 text-left sm:min-h-105">
                <img src="{{ $vehicle->thumbnail_url }}" alt="{{ $vehicle->name }}" width="1100" height="700" class="absolute inset-0 h-full w-full object-cover">
                <span class="absolute inset-x-0 bottom-0 bg-linear-to-t from-slate-950/90 to-transparent p-6 pt-20">
                    <span class="text-xs font-bold uppercase text-blue-300">{{ $vehicle->brand }} · {{ $vehicle->year }}</span>
                    <span class="mt-2 block text-3xl font-extrabold sm:text-4xl">{{ $vehicle->name }}</span>
                </span>
            </button>

            <div class="grid grid-cols-2 gap-3 sm:grid-cols-1">
                @foreach($galleryUrls->skip(1)->take(2) as $imageUrl)
                    <button type="button" @click="selected = @js($imageUrl); open = true" class="relative min-h-28 overflow-hidden rounded-lg bg-slate-800 sm:min-h-0">
                        <img src="{{ $imageUrl }}" alt="{{ $vehicle->name }}" width="540" height="330" loading="lazy" class="absolute inset-0 h-full w-full object-cover transition hover:scale-105">
                        @if($loop->last && $galleryUrls->count() > 3)
                            <span class="absolute inset-0 grid place-items-center bg-slate-950/55 text-sm font-bold">+{{ $galleryUrls->count() - 3 }} {{ __('transport.show.gallery') }}</span>
                        @endif
                    </button>
                @endforeach
            </div>

            <div x-show="open" x-cloak x-transition.opacity class="fixed inset-0 z-70 grid place-items-center bg-slate-950/95 p-4" role="dialog" aria-modal="true" aria-label="{{ __('transport.show.gallery') }}">
                <button type="button" @click="open = false" class="absolute right-4 top-4 grid h-11 w-11 place-items-center rounded-full bg-white/10 text-white hover:bg-white/20" aria-label="Close"><x-lucide-x class="h-5 w-5" /></button>
                <img :src="selected" alt="{{ $vehicle->name }}" class="max-h-[85vh] max-w-full object-contain">
            </div>
        </div>
    </section>

    <section class="bg-white py-12 sm:py-16">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 sm:px-6 lg:grid-cols-[minmax(0,1fr)_22rem] lg:px-8">
            <div class="min-w-0">
                <div class="flex flex-wrap gap-2">
                    <span class="inline-flex items-center gap-2 rounded-md bg-emerald-50 px-3 py-2 text-xs font-bold text-emerald-700"><x-lucide-badge-check class="h-4 w-4" />{{ __('transport.show.driver_included') }}</span>
                    @foreach($vehicle->feature_badges as $badge)
                        <span class="rounded-md bg-slate-100 px-3 py-2 text-xs font-bold text-slate-700">{{ $badge }}</span>
                    @endforeach
                </div>

                <section class="mt-9 border-t border-slate-200 pt-8">
                    <h2 class="text-2xl font-extrabold text-slate-950">{{ __('transport.show.overview') }}</h2>
                    <div class="mt-4 max-w-3xl text-sm leading-7 text-slate-600">{{ $vehicle->description }}</div>
                </section>

                <section class="mt-9 border-t border-slate-200 pt-8">
                    <h2 class="text-2xl font-extrabold text-slate-950">{{ __('transport.show.specifications') }}</h2>
                    <dl class="mt-5 grid gap-px overflow-hidden rounded-lg border border-slate-200 bg-slate-200 sm:grid-cols-2">
                        <div class="bg-slate-50 p-4"><dt class="text-xs font-bold uppercase text-slate-500">{{ __('transport.capacity.pax', ['count' => $vehicle->capacity_pax]) }}</dt><dd class="mt-2 text-lg font-extrabold text-slate-950">{{ $vehicle->capacity_pax }}</dd></div>
                        <div class="bg-slate-50 p-4"><dt class="text-xs font-bold uppercase text-slate-500">{{ __('transport.capacity.luggage', ['count' => $vehicle->capacity_luggage]) }}</dt><dd class="mt-2 text-lg font-extrabold text-slate-950">{{ $vehicle->capacity_luggage }}</dd></div>
                        <div class="bg-slate-50 p-4"><dt class="text-xs font-bold uppercase text-slate-500">{{ __('transport.index.transmission') }}</dt><dd class="mt-2 font-extrabold text-slate-950">{{ __('transport.transmission.'.$vehicle->transmission) }}</dd></div>
                        <div class="bg-slate-50 p-4"><dt class="text-xs font-bold uppercase text-slate-500">{{ __('transport.show.facilities') }}</dt><dd class="mt-2 font-extrabold text-slate-950">{{ collect([$vehicle->has_ac ? 'AC' : null, $vehicle->has_wifi ? 'WiFi' : null])->filter()->join(' · ') ?: '-' }}</dd></div>
                    </dl>
                </section>

                <section class="mt-9 border-t border-slate-200 pt-8">
                    <h2 class="text-2xl font-extrabold text-slate-950">{{ __('transport.show.facilities') }}</h2>
                    @if(collect($vehicle->features)->isNotEmpty())
                        <ul class="mt-5 grid gap-3 sm:grid-cols-2">
                            @foreach($vehicle->features as $feature)
                                <li class="flex items-center gap-3 text-sm text-slate-700"><span class="grid h-7 w-7 shrink-0 place-items-center rounded-md bg-blue-50 text-blue-600"><x-lucide-check class="h-4 w-4" /></span>{{ $feature }}</li>
                            @endforeach
                        </ul>
                    @else
                        <p class="mt-4 text-sm text-slate-500">{{ __('transport.show.empty_features') }}</p>
                    @endif
                </section>
            </div>

            <aside class="lg:sticky lg:top-28 lg:self-start">
                <div class="rounded-lg border border-slate-200 bg-slate-50 p-6 shadow-sm">
                    <h2 class="text-xl font-extrabold text-slate-950">{{ __('transport.show.pricing') }}</h2>
                    <div class="mt-5 divide-y divide-slate-200 border-y border-slate-200">
                        @if($vehicle->formatted_price_per_day)
                            <div class="flex items-end justify-between gap-4 py-4"><span class="text-sm text-slate-500">{{ __('transport.show.daily') }}</span><strong class="text-lg text-slate-950">{{ $vehicle->formatted_price_per_day }}</strong></div>
                        @endif
                        @if($vehicle->formatted_price_per_trip)
                            <div class="flex items-end justify-between gap-4 py-4"><span class="text-sm text-slate-500">{{ __('transport.show.trip_from') }}</span><strong class="text-lg text-slate-950">{{ $vehicle->formatted_price_per_trip }}</strong></div>
                        @endif
                    </div>
                    <p class="mt-4 text-xs leading-5 text-slate-500">{{ __('transport.show.pricing_note') }}</p>
                    <x-ui::button tag="a" href="{{ route('transport.booking', ['locale' => $locale, 'vehicle' => $slug]) }}" size="lg" class="mt-6 w-full hover:bg-blue-600">{{ __('transport.show.book') }}<x-slot:trailingIcon><x-lucide-arrow-right /></x-slot:trailingIcon></x-ui::button>
                    <p class="mt-4 flex gap-2 text-xs leading-5 text-slate-500"><x-lucide-info class="mt-0.5 h-4 w-4 shrink-0 text-blue-600" />{{ __('transport.show.availability') }}</p>
                </div>
            </aside>
        </div>
    </section>

    @if($relatedVehicles->isNotEmpty())
        <section class="border-t border-slate-200 bg-slate-50 py-12 sm:py-16">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <h2 class="text-2xl font-extrabold text-slate-950">{{ __('transport.show.related') }}</h2>
                <div class="mt-7 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($relatedVehicles as $relatedVehicle)
                        <x-ui.vehicle-card :vehicle="$relatedVehicle" :$locale />
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</x-layouts::app>
