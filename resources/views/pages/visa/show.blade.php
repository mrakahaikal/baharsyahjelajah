@php
    $locale = app()->getLocale();
    $galleryImages = $service->gallery_urls->map(fn (string $url): array => [
        'src' => $url,
        'alt' => $service->name,
    ])->all();
    $processing = match (true) {
        $service->processing_days_min && $service->processing_days_max => __('visa.card.processing_range', ['min' => $service->processing_days_min, 'max' => $service->processing_days_max]),
        (bool) $service->processing_days_min => __('visa.card.processing_from', ['days' => $service->processing_days_min]),
        (bool) $service->processing_days_max => __('visa.card.processing_until', ['days' => $service->processing_days_max]),
        default => __('visa.not_available'),
    };
@endphp

<x-layouts::app
    :title="__('visa.seo.show_title', ['service' => $service->name])"
    :meta-description="$description"
    :$schemaJson
    :$canonicalUrl
    :$alternateUrls
    breadcrumb-name="visa.show"
    :breadcrumb-parameters="[$locale, $service]"
    og-type="website"
    :og-image="$service->cover_url"
    :show-floating-whatsapp="false">
    <section class="border-b border-emerald-900/20 bg-emerald-950 text-white">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <x-ui.breadcrumbs name="visa.show" :parameters="[$locale, $service]" variant="emerald" />
        </div>

        <div class="mx-auto grid max-w-7xl gap-8 px-4 pb-12 sm:px-6 lg:grid-cols-[minmax(0,1.1fr)_minmax(20rem,0.9fr)] lg:items-center lg:px-8 lg:pb-16">
            <div class="min-w-0 order-2 lg:order-1">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex min-h-8 items-center gap-2 rounded-md border border-white/15 bg-white/8 px-3 text-xs font-bold text-emerald-50">
                        @if($service->country->flag_url)
                            <img src="{{ $service->country->flag_url }}" alt="" width="24" height="16" class="h-4 w-6 rounded-sm object-cover" aria-hidden="true">
                        @else
                            <x-lucide-flag class="h-4 w-4 text-lime-300" aria-hidden="true" />
                        @endif
                        {{ $service->country->name }}
                    </span>
                    <span class="rounded-md bg-lime-300 px-3 py-2 text-xs font-extrabold text-emerald-950">{{ $service->visa_type }}</span>
                </div>
                <h1 class="mt-5 text-balance text-4xl font-extrabold leading-tight sm:text-5xl">{{ $service->name }}</h1>
                @if($service->summary)
                    <p class="mt-5 max-w-2xl text-base leading-8 text-emerald-50/80 sm:text-lg">{{ $service->summary }}</p>
                @endif
            </div>

            <div class="order-1 min-w-0 lg:order-2">
                <x-ui.lightbox-gallery :images="$galleryImages" :alt="$service->name" :label="__('visa.show.gallery')" />
            </div>
        </div>
    </section>

    <section class="bg-white py-12 sm:py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <dl class="grid overflow-hidden rounded-lg border border-slate-200 bg-slate-200 sm:grid-cols-2 lg:grid-cols-5">
                <div class="bg-slate-50 p-4">
                    <dt class="text-xs font-bold uppercase text-slate-400">{{ __('visa.show.processing') }}</dt>
                    <dd class="mt-2 font-extrabold text-slate-950">{{ $processing }}</dd>
                </div>
                <div class="bg-slate-50 p-4">
                    <dt class="text-xs font-bold uppercase text-slate-400">{{ __('visa.show.entry_type') }}</dt>
                    <dd class="mt-2 font-extrabold text-slate-950">{{ $service->entry_type ? __('visa.entry_types.'.$service->entry_type->value) : __('visa.not_available') }}</dd>
                </div>
                <div class="bg-slate-50 p-4">
                    <dt class="text-xs font-bold uppercase text-slate-400">{{ __('visa.show.validity') }}</dt>
                    <dd class="mt-2 font-extrabold text-slate-950">{{ $service->validity_days ? __('visa.days', ['count' => $service->validity_days]) : __('visa.not_available') }}</dd>
                </div>
                <div class="bg-slate-50 p-4">
                    <dt class="text-xs font-bold uppercase text-slate-400">{{ __('visa.show.maximum_stay') }}</dt>
                    <dd class="mt-2 font-extrabold text-slate-950">{{ $service->maximum_stay_days ? __('visa.days', ['count' => $service->maximum_stay_days]) : __('visa.not_available') }}</dd>
                </div>
                <div class="bg-emerald-900 p-4 text-white">
                    <dt class="text-xs font-bold uppercase text-emerald-100/65">{{ __('visa.show.price') }}</dt>
                    <dd class="mt-2 text-lg font-extrabold text-lime-300">{{ $service->formatted_price ?? __('visa.price_on_request') }}</dd>
                </div>
            </dl>

            <div class="mt-12 grid gap-10 lg:grid-cols-[minmax(0,1fr)_23rem] lg:items-start">
                <div class="min-w-0">
                    @if($service->description)
                        <section aria-labelledby="visa-overview-heading">
                            <p class="text-xs font-bold uppercase text-emerald-700">{{ __('visa.show.overview_eyebrow') }}</p>
                            <h2 id="visa-overview-heading" class="mt-2 text-2xl font-extrabold text-slate-950 sm:text-3xl">{{ __('visa.show.overview') }}</h2>
                            <div class="prose prose-slate mt-5 max-w-none text-sm leading-7">{!! $service->description !!}</div>
                        </section>
                    @endif

                    @foreach(\App\Enums\VisaItemType::cases() as $itemType)
                        @php($items = $groupedItems->get($itemType->value, collect()))
                        @if($items->isNotEmpty())
                            <section class="mt-10 border-t border-slate-200 pt-8" aria-labelledby="visa-items-{{ $itemType->value }}">
                                <h2 id="visa-items-{{ $itemType->value }}" class="text-2xl font-extrabold text-slate-950">{{ __('visa.item_types.'.$itemType->value) }}</h2>
                                <ul class="mt-5 grid gap-3 {{ in_array($itemType, [\App\Enums\VisaItemType::Requirement, \App\Enums\VisaItemType::Included, \App\Enums\VisaItemType::Excluded], true) ? 'sm:grid-cols-2' : '' }}">
                                    @foreach($items as $item)
                                        <li class="flex min-w-0 items-start gap-3 rounded-lg border border-slate-200 bg-slate-50 p-4">
                                            <span class="grid size-8 shrink-0 place-items-center rounded-md {{ $itemType === \App\Enums\VisaItemType::Excluded ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-800' }}">
                                                @if($itemType === \App\Enums\VisaItemType::Excluded)
                                                    <x-lucide-x class="h-4 w-4" aria-hidden="true" />
                                                @elseif($itemType === \App\Enums\VisaItemType::Note)
                                                    <x-lucide-info class="h-4 w-4" aria-hidden="true" />
                                                @else
                                                    <x-lucide-check class="h-4 w-4" aria-hidden="true" />
                                                @endif
                                            </span>
                                            <div class="min-w-0">
                                                <p class="text-sm font-bold leading-6 text-slate-900">{{ $item->content }}</p>
                                                @if($item->details)
                                                    <p class="mt-1 text-xs leading-5 text-slate-500">{{ $item->details }}</p>
                                                @endif
                                                @if($itemType === \App\Enums\VisaItemType::Requirement)
                                                    <span class="mt-2 inline-flex rounded-sm px-2 py-1 text-[11px] font-bold {{ $item->is_mandatory ? 'bg-rose-100 text-rose-700' : 'bg-slate-200 text-slate-600' }}">
                                                        {{ $item->is_mandatory ? __('visa.show.mandatory') : __('visa.show.optional') }}
                                                    </span>
                                                @endif
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </section>
                        @endif
                    @endforeach

                    <div class="mt-10 flex items-start gap-3 border-y border-amber-200 bg-amber-50 px-4 py-5 text-sm leading-6 text-amber-950">
                        <x-lucide-triangle-alert class="mt-0.5 h-5 w-5 shrink-0 text-amber-700" aria-hidden="true" />
                        <p>{{ __('visa.show.disclaimer') }}</p>
                    </div>
                </div>

                <livewire:visa-inquiry :service="$service" />
            </div>
        </div>
    </section>

    @if($relatedServices->isNotEmpty())
        <section class="border-t border-slate-200 bg-slate-50 py-12 sm:py-16" aria-labelledby="related-visa-heading">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <h2 id="related-visa-heading" class="text-2xl font-extrabold text-slate-950">{{ __('visa.show.related') }}</h2>
                <div class="mt-7 grid gap-6 lg:grid-cols-2">
                    @foreach($relatedServices as $relatedService)
                        <x-ui.visa-service-card :service="$relatedService" :$locale />
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</x-layouts::app>
