@php
    $locale = app()->getLocale();
    $slug = $vehicle->getTranslation('slug', $locale, false) ?: $vehicle->getTranslation('slug', 'id', false) ?: $vehicle->getKey();
@endphp

<x-layouts::app
    :title="__('transport.seo.booking_title', ['vehicle' => $vehicle->name])"
    :meta-description="__('transport.booking.subtitle')"
    robots="noindex, follow"
    breadcrumb-name="transport.booking"
    :breadcrumb-parameters="[$locale, $vehicle]"
    :$canonicalUrl
    :$alternateUrls
    :show-floating-whatsapp="false">
    <section class="border-b border-slate-200 bg-slate-50 py-10 sm:py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <x-ui.breadcrumbs name="transport.booking" :parameters="[$locale, $vehicle]" class="mb-6" />

            <a href="{{ route('transport.show', ['locale' => $locale, 'vehicle' => $slug, 'area' => $initialArea]) }}" class="inline-flex items-center gap-2 text-sm font-semibold text-blue-600 hover:text-blue-700"><x-lucide-arrow-left class="h-4 w-4" />{{ __('transport.booking.back') }}</a>
            <p class="mt-7 text-xs font-bold uppercase text-blue-600">{{ __('transport.booking.eyebrow') }}</p>
            <h1 class="mt-3 max-w-3xl text-balance text-3xl font-extrabold text-slate-950 sm:text-4xl">{{ __('transport.booking.title') }}</h1>
            <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-500">{{ __('transport.booking.subtitle') }}</p>
        </div>
    </section>

    <section class="bg-slate-50 py-10 sm:py-14">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @if(filled($whatsappNumber))
                <livewire:vehicle-booking-form :$vehicle :$initialArea :$initialPax />
            @else
                <div class="rounded-lg border border-amber-200 bg-amber-50 p-6 text-amber-900">{{ __('transport.booking.service_unavailable') }}</div>
            @endif
        </div>
    </section>
</x-layouts::app>
