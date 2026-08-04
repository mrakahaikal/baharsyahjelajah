@php
    $locale = app()->getLocale();
    $heroCountry = $countries->getCollection()->first(fn ($c) => filled($c->cover_url));
@endphp

<x-layouts::app
    seo-page="country_index"
    :title="__('country.seo.index_title')"
    :meta-description="__('country.seo.index_description')"
    :$schemaJson
    breadcrumb-name="country.index"
    :breadcrumb-parameters="[$locale]"
    :$canonicalUrl
    :$alternateUrls>
    <section class="relative isolate overflow-hidden bg-slate-950 py-20 text-white sm:py-24" aria-labelledby="country-index-heading">
        @if($heroCountry)
            <img src="{{ $heroCountry->cover_url }}" alt="" width="1800" height="900" class="absolute inset-0 h-full w-full object-cover opacity-35">
            <div class="absolute inset-0 bg-linear-to-r from-slate-950 via-slate-950/90 to-slate-950/45"></div>
        @endif
        <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <x-ui.breadcrumbs name="country.index" :parameters="[$locale]" variant="dark" class="mb-10" />

            <p class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-[0.18em] text-blue-300">
                <x-lucide-globe class="h-4 w-4" aria-hidden="true" />
                {{ __('country.index.eyebrow') }}
            </p>
            <h1 id="country-index-heading" class="mt-4 max-w-3xl text-balance text-4xl font-extrabold leading-tight sm:text-5xl">
                {{ __('country.index.title') }}
            </h1>
            <p class="mt-5 max-w-2xl text-pretty text-base leading-8 text-slate-200 sm:text-lg">
                {{ __('country.index.subtitle') }}
            </p>
        </div>
    </section>

    <section class="bg-slate-50 py-14 sm:py-20" aria-labelledby="country-list-heading">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @if($countries->isNotEmpty())
                <div class="grid gap-6 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($countries as $country)
                        <x-ui.country-bento-card :$country :$locale />
                    @endforeach
                </div>
                <div class="mt-10">{{ $countries->links() }}</div>
            @else
                <div class="border-y border-slate-200 py-14 text-center">
                    <x-lucide-globe-2 class="mx-auto h-10 w-10 text-slate-300" aria-hidden="true" />
                    <h2 class="mt-4 text-lg font-bold text-slate-950">{{ __('country.index.empty.title') }}</h2>
                    <p class="mx-auto mt-2 max-w-lg text-sm leading-6 text-slate-500">{{ __('country.index.empty.subtitle') }}</p>
                </div>
            @endif
        </div>
    </section>
</x-layouts::app>
