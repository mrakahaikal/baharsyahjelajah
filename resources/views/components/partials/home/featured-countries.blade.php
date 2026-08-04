@props(['countries', 'locale' => app()->getLocale()])

@if($countries->isNotEmpty())
    <section class="bg-white py-16 sm:py-20" aria-labelledby="featured-countries-heading">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                <div class="max-w-2xl">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-600">
                        <x-lucide-globe-2 class="inline-block h-4 w-4 mr-1.5 align-text-bottom" aria-hidden="true" />
                        {{ __('country.home.eyebrow') }}
                    </p>
                    <h2 id="featured-countries-heading" class="mt-3 text-balance text-3xl font-extrabold text-slate-950 sm:text-4xl">
                        {{ __('country.home.title') }}
                    </h2>
                    <p class="mt-4 text-sm leading-7 text-slate-500">
                        {{ __('country.home.subtitle') }}
                    </p>
                </div>
                <x-ui::button tag="a" href="{{ route('country.index', ['locale' => $locale]) }}" variant="outline" class="w-full hover:border-blue-600 hover:text-blue-600 sm:w-auto">
                    {{ __('country.home.view_all') }}
                    <x-slot:trailingIcon><x-lucide-arrow-right /></x-slot:trailingIcon>
                </x-ui::button>
            </div>

            <div class="mt-10 grid gap-6 grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 lg:grid-rows-2">
                @foreach($countries as $country)
                    <x-ui.country-bento-card :$country :$locale :is-large="$loop->first" />
                @endforeach
            </div>
        </div>
    </section>
@endif
