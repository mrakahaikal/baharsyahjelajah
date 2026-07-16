@php($locale = app()->getLocale())

<x-layouts::app
    :title="__('visa.seo.index_title')"
    :meta-description="__('visa.seo.index_description')"
    :$schemaJson
    :$canonicalUrl
    :$alternateUrls
    breadcrumb-name="visa.index"
    :breadcrumb-parameters="[$locale]"
    og-type="website"
    :og-image="asset(\App\Models\VisaService::DEFAULT_IMAGE)">
    <section class="relative isolate overflow-hidden bg-emerald-950 text-white" aria-labelledby="visa-index-heading">
        <img src="{{ asset(\App\Models\VisaService::DEFAULT_IMAGE) }}" alt="" width="1800" height="1000" fetchpriority="high" class="absolute inset-0 h-full w-full object-cover opacity-45">
        <div class="absolute inset-0 bg-linear-to-r from-emerald-950 via-emerald-950/92 to-emerald-950/35"></div>
        <div class="relative mx-auto max-w-7xl px-4 py-16 sm:px-6 sm:py-20 lg:px-8 lg:py-24">
            <x-ui.breadcrumbs name="visa.index" :parameters="[$locale]" variant="emerald" />

            <div class="mt-12 max-w-3xl">
                <p class="inline-flex items-center gap-2 text-xs font-bold uppercase text-lime-300">
                    <x-lucide-stamp class="h-4 w-4" aria-hidden="true" />
                    {{ __('visa.hero.eyebrow') }}
                </p>
                <h1 id="visa-index-heading" class="mt-5 text-balance text-4xl font-extrabold leading-tight sm:text-5xl lg:text-6xl">{{ __('visa.hero.title') }}</h1>
                <p class="mt-5 max-w-2xl text-pretty text-base leading-8 text-emerald-50/80 sm:text-lg">{{ __('visa.hero.subtitle') }}</p>
            </div>
        </div>
    </section>

    <section class="border-b border-emerald-100 bg-emerald-50" aria-label="{{ __('visa.process.label') }}">
        <div class="mx-auto grid max-w-7xl gap-px bg-emerald-100 sm:grid-cols-3">
            @foreach(['choose' => 'search-check', 'prepare' => 'files', 'consult' => 'message-circle'] as $step => $icon)
                <div class="flex items-start gap-4 bg-emerald-50 px-4 py-5 sm:px-6 lg:px-8">
                    <span class="grid size-9 shrink-0 place-items-center rounded-full bg-emerald-900 text-lime-300">
                        <x-dynamic-component :component="'lucide-'.$icon" class="h-4 w-4" aria-hidden="true" />
                    </span>
                    <div>
                        <p class="text-sm font-extrabold text-emerald-950">{{ __('visa.process.'.$step.'.title') }}</p>
                        <p class="mt-1 text-xs leading-5 text-emerald-800/70">{{ __('visa.process.'.$step.'.description') }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="bg-white py-14 sm:py-18" aria-labelledby="visa-catalog-heading">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl">
                <p class="text-xs font-bold uppercase text-emerald-700">{{ __('visa.catalog.eyebrow') }}</p>
                <h2 id="visa-catalog-heading" class="mt-3 text-balance text-3xl font-extrabold text-slate-950 sm:text-4xl">{{ __('visa.catalog.title') }}</h2>
                <p class="mt-4 text-sm leading-7 text-slate-600">{{ __('visa.catalog.subtitle') }}</p>
            </div>

            <livewire:visa-catalog />
        </div>
    </section>
</x-layouts::app>
