@php
    $locale = app()->getLocale();
    $heroDestination = $destinations->getCollection()->first(fn ($destination) => filled($destination->cover_url));
@endphp

<x-layouts::app
    :title="__('destination.seo.index_title')"
    :meta-description="__('destination.seo.index_description')"
    :$schemaJson
    breadcrumb-name="destination.index"
    :breadcrumb-parameters="[$locale]"
    :$canonicalUrl
    :$alternateUrls>
    <section class="relative isolate overflow-hidden bg-slate-950 py-20 text-white sm:py-24" aria-labelledby="destination-index-heading">
        @if($heroDestination)
            <img src="{{ $heroDestination->cover_url }}" alt="" width="1800" height="900" class="absolute inset-0 h-full w-full object-cover opacity-35">
            <div class="absolute inset-0 bg-linear-to-r from-slate-950 via-slate-950/90 to-slate-950/45"></div>
        @endif
        <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <x-ui.breadcrumbs name="destination.index" :parameters="[$locale]" variant="dark" class="mb-10" />

            <p class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-[0.18em] text-blue-300">
                <x-lucide-compass class="h-4 w-4" aria-hidden="true" />
                {{ __('destination.index.eyebrow') }}
            </p>
            <h1 id="destination-index-heading" class="mt-4 max-w-3xl text-balance text-4xl font-extrabold leading-tight sm:text-5xl">
                {{ __('destination.index.title') }}
            </h1>
            <p class="mt-5 max-w-2xl text-pretty text-base leading-8 text-slate-200 sm:text-lg">
                {{ __('destination.index.subtitle') }}
            </p>
        </div>
    </section>

    <section class="bg-slate-50 py-14 sm:py-20" aria-labelledby="destination-list-heading">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-600">{{ __('destination.index.list_eyebrow') }}</p>
                    <h2 id="destination-list-heading" class="mt-2 text-2xl font-extrabold text-slate-950 sm:text-3xl">{{ __('destination.index.list_title') }}</h2>
                </div>
                <p class="text-sm font-semibold text-slate-500">{{ trans_choice('destination.index.total', $destinations->total(), ['count' => $destinations->total()]) }}</p>
            </div>

            @if($destinations->isNotEmpty())
                <div class="mt-9 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($destinations as $destination)
                        <x-ui.destination-card :$destination :$locale />
                    @endforeach
                </div>
                <div class="mt-10">{{ $destinations->links() }}</div>
            @else
                <div class="mt-9 border-y border-slate-200 py-14 text-center">
                    <x-lucide-map-pinned class="mx-auto h-10 w-10 text-slate-300" aria-hidden="true" />
                    <h2 class="mt-4 text-lg font-bold text-slate-950">{{ __('destination.index.empty_title') }}</h2>
                    <p class="mx-auto mt-2 max-w-lg text-sm leading-6 text-slate-500">{{ __('destination.index.empty_text') }}</p>
                </div>
            @endif
        </div>
    </section>
</x-layouts::app>
