@php
    $locale = app()->getLocale();
@endphp

<x-layouts::app
    :title="__('umrah.seo.title')"
    :meta-description="__('umrah.seo.description')"
    :show-floating-whatsapp="false"
    theme-class="theme-umrah"
    breadcrumb-name="umroh.index"
    :breadcrumb-parameters="[$locale]"
    :$canonicalUrl
    :$alternateUrls>
    <section class="relative isolate overflow-hidden bg-neutral-950 text-white" aria-labelledby="umrah-heading">
        <img src="https://images.unsplash.com/photo-1565552645632-d725f8bfc19a?auto=format&fit=crop&w=1800&q=85" alt="" width="1800" height="900" fetchpriority="high" class="absolute inset-0 h-full w-full object-cover opacity-45">
        <div class="absolute inset-0 bg-linear-to-r from-neutral-950 via-neutral-950/88 to-neutral-950/35"></div>
        <div class="absolute inset-x-0 bottom-0 h-px bg-linear-to-r from-transparent via-amber-300/80 to-transparent"></div>

        <div class="relative mx-auto max-w-7xl px-4 py-20 sm:px-6 sm:py-24 lg:px-8 lg:py-28">
            <x-ui.breadcrumbs name="umroh.index" :parameters="[$locale]" variant="dark" />

            <div class="mt-12 max-w-3xl">
                <p class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-[0.18em] text-amber-300">
                    <x-lucide-moon-star class="h-4 w-4" aria-hidden="true" />
                    {{ __('umrah.hero.eyebrow') }}
                </p>
                <h1 id="umrah-heading" class="mt-5 text-balance text-4xl font-extrabold leading-tight sm:text-5xl lg:text-6xl">{{ __('umrah.hero.title') }}</h1>
                <p class="mt-5 max-w-2xl text-pretty text-base leading-8 text-neutral-300 sm:text-lg">{{ __('umrah.hero.subtitle') }}</p>
            </div>
        </div>
    </section>

    <section class="border-b border-neutral-800 bg-neutral-950 py-6 text-white" aria-label="{{ __('umrah.filter.label') }}">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <form method="GET" action="{{ route('umroh.index', ['locale' => $locale]) }}" class="flex flex-col gap-3 sm:flex-row sm:items-end">
                <label class="min-w-0 flex-1 sm:max-w-sm">
                    <span class="mb-2 block text-xs font-bold uppercase tracking-wide text-neutral-400">{{ __('umrah.filter.label') }}</span>
                    <span class="relative block">
                        <select name="type" class="min-h-12 w-full appearance-none rounded-lg border border-white/15 bg-white/8 px-4 pr-10 text-sm font-semibold text-white outline-none transition-colors hover:border-amber-300/50 focus:border-amber-300">
                            <option value="" class="bg-neutral-900 text-white">{{ __('umrah.filter.all') }}</option>
                            @foreach($packageTypes as $packageType)
                                <option value="{{ $packageType }}" @selected($activeType === $packageType) class="bg-neutral-900 text-white">{{ __('umrah.types.'.$packageType) }}</option>
                            @endforeach
                        </select>
                        <x-lucide-chevron-down class="pointer-events-none absolute right-4 top-1/2 h-4 w-4 -translate-y-1/2 text-amber-300" aria-hidden="true" />
                    </span>
                </label>
                <button type="submit" class="inline-flex min-h-12 items-center justify-center gap-2 rounded-lg bg-amber-400 px-5 text-sm font-bold text-neutral-950 transition-colors hover:bg-amber-300 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amber-300">
                    <x-lucide-sliders-horizontal class="h-4 w-4" aria-hidden="true" />
                    {{ __('umrah.filter.apply') }}
                </button>
                @if($activeType !== '')
                    <a href="{{ route('umroh.index', ['locale' => $locale]) }}" class="inline-flex min-h-12 items-center justify-center px-3 text-sm font-bold text-neutral-300 transition-colors hover:text-white focus-visible:rounded-md focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amber-300">
                        {{ __('umrah.filter.clear') }}
                    </a>
                @endif
            </form>
        </div>
    </section>

    <section class="bg-stone-50 py-16 sm:py-20" aria-labelledby="umrah-catalog-heading">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl">
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-amber-700">{{ __('umrah.catalog.eyebrow') }}</p>
                <h2 id="umrah-catalog-heading" class="mt-3 text-balance text-3xl font-extrabold text-neutral-950 sm:text-4xl">{{ __('umrah.catalog.title') }}</h2>
                <p class="mt-4 text-sm leading-7 text-neutral-600">{{ __('umrah.catalog.subtitle') }}</p>
                <p class="mt-3 text-xs font-bold text-neutral-500">{{ __('umrah.catalog.count', ['count' => $packages->total()]) }}</p>
            </div>

            @if($packages->isNotEmpty())
                <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($packages as $package)
                        <x-ui.umrah-package-card :$package :$locale :$whatsappNumber />
                    @endforeach
                </div>

                @if($packages->hasPages())
                    <div class="mt-10">{{ $packages->links() }}</div>
                @endif
            @else
                <div class="mt-10 border-y border-stone-200 py-14 text-center">
                    <x-lucide-calendar-x-2 class="mx-auto h-8 w-8 text-amber-700" aria-hidden="true" />
                    <h3 class="mt-4 text-xl font-bold text-neutral-950">{{ __('umrah.empty.title') }}</h3>
                    <p class="mx-auto mt-2 max-w-xl text-sm leading-7 text-neutral-600">{{ __('umrah.empty.subtitle') }}</p>
                    <a href="{{ route('contact.index', ['locale' => $locale]) }}" class="mt-6 inline-flex min-h-11 items-center justify-center gap-2 rounded-lg bg-neutral-950 px-5 text-sm font-bold text-white transition-colors hover:bg-amber-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-neutral-950">
                        {{ __('umrah.empty.cta') }}
                        <x-lucide-arrow-right class="h-4 w-4" aria-hidden="true" />
                    </a>
                </div>
            @endif
        </div>
    </section>
</x-layouts::app>
