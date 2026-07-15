@props(['services', 'locale'])

<section class="border-y border-emerald-900/10 bg-emerald-50 py-16 sm:py-20" aria-labelledby="featured-visa-heading">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-end">
            <div class="max-w-2xl">
                <p class="inline-flex items-center gap-2 text-xs font-bold uppercase text-emerald-800">
                    <x-lucide-stamp class="h-4 w-4" aria-hidden="true" />
                    {{ __('visa.home.eyebrow') }}
                </p>
                <h2 id="featured-visa-heading" class="mt-3 text-balance text-3xl font-extrabold text-slate-950 sm:text-4xl">{{ __('visa.home.title') }}</h2>
                <p class="mt-4 text-sm leading-7 text-slate-600">{{ __('visa.home.subtitle') }}</p>
            </div>

            <x-ui::button tag="a" href="{{ route('visa.index', ['locale' => $locale]) }}" variant="outline" class="w-full border-emerald-800 text-emerald-900 hover:bg-emerald-900 hover:text-white sm:w-fit">
                {{ __('visa.home.view_all') }}
                <x-slot:trailingIcon><x-lucide-arrow-right /></x-slot:trailingIcon>
            </x-ui::button>
        </div>

        @if($services->isNotEmpty())
            <div class="mt-10 grid gap-6 xl:grid-cols-2">
                @foreach($services as $service)
                    <x-ui.visa-service-card :$service :$locale />
                @endforeach
            </div>
        @else
            <div class="mt-10 flex flex-col gap-4 border-y border-emerald-900/15 py-8 sm:flex-row sm:items-center sm:justify-between">
                <p class="max-w-2xl text-sm leading-7 text-emerald-950/70">{{ __('visa.home.empty') }}</p>
                <a href="{{ route('visa.index', ['locale' => $locale]) }}" class="inline-flex items-center gap-2 text-sm font-bold text-emerald-900 hover:text-emerald-700 focus-visible:rounded-sm focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-emerald-800">
                    {{ __('visa.home.consult') }}
                    <x-lucide-arrow-right class="h-4 w-4" aria-hidden="true" />
                </a>
            </div>
        @endif
    </div>
</section>
