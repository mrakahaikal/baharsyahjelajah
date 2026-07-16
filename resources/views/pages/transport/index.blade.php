<x-layouts::app
    :title="__('transport.seo.index_title')"
    :meta-description="__('transport.seo.index_description')"
    breadcrumb-name="transport.index"
    :breadcrumb-parameters="[app()->getLocale()]"
    :$canonicalUrl
    :$alternateUrls>
    <section class="border-b border-slate-200 bg-slate-950 py-14 text-white sm:py-18">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <x-ui.breadcrumbs name="transport.index" :parameters="[app()->getLocale()]" variant="dark" class="mb-10" />

            <div class="max-w-3xl">
                <p class="inline-flex items-center gap-2 text-xs font-bold uppercase text-blue-300">
                    <x-lucide-car class="h-4 w-4" aria-hidden="true" />{{ __('transport.index.eyebrow') }}
                </p>
                <h1 class="mt-4 text-balance text-3xl font-extrabold sm:text-5xl">{{ __('transport.index.title') }}</h1>
                <p class="mt-5 max-w-2xl text-sm leading-7 text-slate-300 sm:text-base">{{ __('transport.index.subtitle') }}</p>
            </div>
        </div>
    </section>

    <section class="bg-white py-10 sm:py-14">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <livewire:vehicle-catalog />
        </div>
    </section>
</x-layouts::app>
